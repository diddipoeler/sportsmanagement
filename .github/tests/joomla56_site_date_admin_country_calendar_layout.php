<?php
/**
 * Regression tests for site dates, admin country logging, and calendar layouts.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\CMS\Log {
    /** Capture administrator errors without the Joomla runtime. */
    final class Log {
        public const ERROR = 1;
        public static array $entries = [];

        /**
         * Record a safe error-log message for assertion.
         * @param string $message Error message.
         * @param int $priority Joomla log severity.
         * @param string $category Joomla log category.
         * @return void
         */
        public static function add(string $message, int $priority, string $category): void {
            self::$entries[] = $message;
        }
    }
}
namespace Joomla\CMS\Dispatcher {
    /** Minimal module dispatcher base with a caller-provided layout context. */
    class AbstractModuleDispatcher {
        public static array|false $fixture = [];

        /**
         * Return the test module context, mirroring Joomla's parent dispatcher.
         * @return array|false Module data or disabled rendering indicator.
         */
        protected function getLayoutData(): array|false {
            return self::$fixture;
        }

        /**
         * Supply the current test application instance.
         * @return object Joomla site application fixture.
         */
        protected function getApplication(): object {
            return \TestCalendarRuntime::$app;
        }
    }
}
namespace Joomla\CMS\Helper {
    /** Dummy Joomla helper factory marker. */
    interface HelperFactoryAwareInterface {}

    /** Supply a Joomla-style helper factory for dispatcher tests. */
    trait HelperFactoryAwareTrait {
        /**
         * Return the configured test helper factory.
         * @return object Calendar helper factory fixture.
         */
        protected function getHelperFactory(): object {
            return \TestCalendarRuntime::$factory;
        }
    }
}
namespace Joomla\Database {
    /** Minimal Joomla database interface for administrator country queries. */
    interface DatabaseInterface {
        /**
         * Create a query for the country selector test.
         * @return object Database query builder fixture.
         */
        public function createQuery(): object;

        /**
         * Quote a database identifier for the test query.
         * @param string $name Table or field identifier.
         * @return string Escaped identifier.
         */
        public function quoteName(string $name): string;

        /**
         * Set the prepared query without running SQL.
         * @param mixed $query Prepared SQL query.
         * @return void
         */
        public function setQuery(mixed $query): void;

        /**
         * Simulate a database error from the country query.
         * @return array Country options.
         */
        public function loadAssocList(): array;
    }
}
namespace {
    define('_JEXEC', 1);
    define('JPATH_SITE', '/fixture/site');
    define('JPATH_ADMINISTRATOR', '/fixture/admin');

    /** Shared Joomla calendar fixture state. */
    final class TestCalendarRuntime {
        public static object $app;
        public static object $factory;
        public static array $seenLayouts = [];
    }

    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Helper/SportsManagementDateHelper.php';
    require_once $root . '/admin/src/Helper/CountryOptionsHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_calendar/src/Dispatcher/Dispatcher.php';

    $dates = \Diddipoeler\Component\SportsManagement\Site\Helper\SportsManagementDateHelper::class;
    foreach ([
        ['2024-02-29', 1, '29-02-2024'],
        ['2026-11-12 23:59:59', 1, '12-11-2026'],
        ['12-11-2026', 0, '2026-11-12'],
    ] as [$value, $direction, $expected]) {
        if ($dates::convertDate($value, $direction) !== $expected) {
            throw new \RuntimeException('Valid SportsManagement site date conversion regressed.');
        }
    }

    foreach ([' 2024-02-29', "2024-02-29\n", '2026-02-30', '2024-02-29 25:00:00'] as $invalid) {
        if ($dates::convertDate($invalid) !== '') {
            throw new \RuntimeException('Malformed site date was silently accepted.');
        }
    }

    $db = new class implements \Joomla\Database\DatabaseInterface {
        /**
         * Return a database builder fixture.
         * @return object Fluent query builder.
         */
        public function createQuery(): object {
            return new class {
                /**
                 * Accept selected country columns.
                 * @param mixed $columns SQL selected columns.
                 * @return self Fluent query builder.
                 */
                public function select(mixed $columns): self { return $this; }
                /**
                 * Accept selected country table.
                 * @param string $table SQL table name.
                 * @return self Fluent query builder.
                 */
                public function from(string $table): self { return $this; }
            };
        }

        /**
         * Quote a SQL identifier in the test query.
         * @param string $name SQL identifier.
         * @return string Quoted identifier.
         */
        public function quoteName(string $name): string { return '[' . $name . ']'; }

        /**
         * Record the query without executing anything.
         * @param mixed $query Database query fixture.
         * @return void
         */
        public function setQuery(mixed $query): void {}

        /**
         * Simulate a SQL exception carrying potentially sensitive details.
         * @return array Country row list.
         */
        public function loadAssocList(): array {
            throw new \RuntimeException('password=secret SELECT user_credentials');
        }
    };

    $countries = \Diddipoeler\Component\SportsManagement\Administrator\Helper\CountryOptionsHelper::class;
    if ($countries::getOptions($db) !== []
        || count(\Joomla\CMS\Log\Log::$entries) !== 1
        || !str_contains(\Joomla\CMS\Log\Log::$entries[0], 'RuntimeException')
        || str_contains(\Joomla\CMS\Log\Log::$entries[0], 'secret')
        || str_contains(\Joomla\CMS\Log\Log::$entries[0], 'SELECT')) {
        throw new \RuntimeException('Country option error handling exposed database details.');
    }

    TestCalendarRuntime::$app = new class {
        /**
         * Confirm Joomla site-client mode.
         * @param string $client Requested Joomla client.
         * @return bool Whether this is the site client.
         */
        public function isClient(string $client): bool { return $client === 'site'; }
        /**
         * Supply a fake language loader.
         * @return object Joomla language fixture.
         */
        public function getLanguage(): object {
            return new class {
                /**
                 * Return a stable Joomla language tag.
                 * @return string Current language tag.
                 */
                public function getTag(): string { return 'de-DE'; }
                /**
                 * Accept component and module language loads.
                 * @param mixed ...$args Joomla language loading arguments.
                 * @return bool Language loaded indicator.
                 */
                public function load(mixed ...$args): bool { return true; }
            };
        }
    };

    TestCalendarRuntime::$factory = new class {
        /**
         * Return a calendar data fixture for the requested helper.
         * @param string $name Joomla helper name.
         * @return object Calendar module helper.
         */
        public function getHelper(string $name): object {
            return new class {
                /**
                 * Preserve calendar selection and expose a safe template marker.
                 * @param object $params Module parameter registry.
                 * @param object $module Joomla module record.
                 * @param object $app Joomla application.
                 * @return array<string,mixed> Module template data.
                 */
                public function getData(object $params, object $module, object $app): array {
                    \TestCalendarRuntime::$seenLayouts[] = $params->get('which_layout');
                    return ['calendarReady' => true, 'layout' => 'injected', 'module' => 'injected'];
                }
            };
        }
    };

    $dispatcher = new \Diddipoeler\Module\SportsManagementCalendar\Site\Dispatcher\Dispatcher();
    $method = new \ReflectionMethod($dispatcher, 'getLayoutData');
    foreach ([
        'default_jsm' => 'default_jsm',
        'default_arrobefr' => 'default_jsm',
        'default_tuicalendar' => 'default_jsm',
        'default_bootstrap' => 'default_bootstrap',
        'default_zabuto' => 'default_zabuto',
        '../../evil' => 'default_jsm',
        'does_not_exist' => 'default_jsm',
    ] as $requested => $expected) {
        $params = new class($requested) {
            private array $values;

            /**
             * Create a Joomla Registry-like module settings object.
             * @param string $layout Requested raw module layout.
             */
            public function __construct(string $layout) {
                $this->values = ['which_layout' => $layout];
            }

            /**
             * Return the configured module setting.
             * @param string $key Setting name.
             * @param mixed $default Default value.
             * @return mixed Stored value or default.
             */
            public function get(string $key, mixed $default = null): mixed {
                return $this->values[$key] ?? $default;
            }

            /**
             * Save the resolved native layout.
             * @param string $key Setting name.
             * @param mixed $value New setting value.
             * @return void
             */
            public function set(string $key, mixed $value): void {
                $this->values[$key] = $value;
            }
        };
        \Joomla\CMS\Dispatcher\AbstractModuleDispatcher::$fixture = [
            'params' => $params, 'module' => (object) ['id' => 1], 'layout' => 'framework',
        ];
        $data = $method->invoke($dispatcher);
        if ($params->get('which_layout') !== $expected
            || $data['layout'] !== 'framework'
            || !is_object($data['module'])
            || ($data['calendarReady'] ?? false) !== true) {
            throw new \RuntimeException('Invalid calendar layout or altered framework module context.');
        }
    }

    echo "Joomla 5/6 site date, admin country logs and calendar layout guards: OK\n";
}
