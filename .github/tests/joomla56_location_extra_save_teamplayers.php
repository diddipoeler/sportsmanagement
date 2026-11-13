<?php
/**
 * Regression for location values, extra-field delete failures and TeamPlayers context.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\CMS\Language {
    /** Standalone Joomla language fixture. */
    final class Text {
        /**
         * Return untranslated Joomla language key.
         * @param string $key Translation key.
         * @return string Same key for deterministic assertions.
         */
        public static function _(string $key): string { return $key; }
    }
}
namespace Joomla\CMS\Application {
    /** Minimal Joomla app contract for administrative saves. */
    interface CMSApplicationInterface {}
}
namespace Joomla\CMS\Log {
    /** Isolated Joomla logging fixture. */
    final class Log {
        public const ERROR = 1;
        public static array $messages = [];
        /**
         * Capture the message without writing a server log.
         * @param string $message Log message.
         * @param int $severity Log priority.
         * @param string $category Component log category.
         * @return void
         */
        public static function add(string $message, int $severity, string $category): void {
            self::$messages[] = $message;
        }
    }
}
namespace Joomla\Database {
    /** Minimal database interface for helper dependency typing. */
    interface DatabaseInterface {}
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper {
    /** Fake administrator resolver independent of Joomla's container. */
    final class SportsManagementAdministratorApplicationResolver {
        /**
         * Provide a safe administrator application fixture.
         * @return \Joomla\CMS\Application\CMSApplicationInterface Administrator app.
         */
        public static function resolve(): \Joomla\CMS\Application\CMSApplicationInterface {
            return \TestSafetyRuntime::$app;
        }
    }
    /** Inject the test database into extra-field persistence. */
    final class SportsManagementDatabaseResolver {
        /**
         * Return the supplied test database.
         * @param mixed $unused Unused legacy connection.
         * @param \Joomla\Database\DatabaseInterface|null $db Test database.
         * @return \Joomla\Database\DatabaseInterface Simulated database.
         */
        public function resolve(mixed $unused, ?\Joomla\Database\DatabaseInterface $db): \Joomla\Database\DatabaseInterface {
            return $db;
        }
    }
}
namespace Joomla\CMS\Dispatcher {
    /** Minimal Joomla module base for dispatcher tests. */
    abstract class AbstractModuleDispatcher {
        public static array $data = [];

        /**
         * Return Joomla-owned module variables.
         * @return array Module rendering data.
         */
        protected function getLayoutData(): array {
            return self::$data;
        }

        /**
         * Supply the test site or administrator client.
         * @return object Application fixture.
         */
        protected function getApplication(): object {
            return \TestSafetyRuntime::$moduleApp;
        }
    }
}
namespace Joomla\CMS\Helper {
    /** Joomla module helper-factory marker. */
    interface HelperFactoryAwareInterface {}
    /** Helper factory fixture exposing TeamPlayers data. */
    trait HelperFactoryAwareTrait {
        /**
         * Return a TeamPlayers helper factory fixture.
         * @return object Factory with test module helper.
         */
        public function getHelperFactory(): object {
            return new class {
                /**
                 * Return the expected module helper fixture.
                 * @param string $name Requested helper name.
                 * @return object Helper fixture.
                 */
                public function getHelper(string $name): object {
                    return new class {
                        /**
                         * Return safe empty player records.
                         * @param mixed $params Module parameter registry.
                         * @param mixed $db Database service.
                         * @return array<string,mixed> Module data.
                         */
                        public function getData(mixed $params, mixed $db): array {
                            return ['players' => [], 'roster' => [], 'project' => null];
                        }
                    };
                }
            };
        }
    }
}
namespace Joomla\CMS\Document {
    /** HTML document stub to test optional assets. */
    class HtmlDocument {}
}
namespace Joomla\CMS {
    /** Joomla container fixture for TeamPlayers database resolution. */
    final class Factory {
        /**
         * Provide an isolated service container.
         * @return object Test service container.
         */
        public static function getContainer(): object {
            return new class {
                /**
                 * Resolve the configured database fixture.
                 * @param string $name Requested service.
                 * @return mixed Configured service.
                 */
                public function get(string $name): mixed {
                    return \TestSafetyRuntime::$database;
                }
            };
        }
    }
}
namespace {
    define('_JEXEC', 1);

    /** Shared fixtures for administrator and module tests. */
    final class TestSafetyRuntime {
        public static \Joomla\CMS\Application\CMSApplicationInterface $app;
        public static object $moduleApp;
        public static \Joomla\Database\DatabaseInterface $database;
    }

    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Helper/LocationAddressHelper.php';
    require_once $root . '/admin/src/Helper/ExtraFieldsSaveHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_teamplayers/src/Dispatcher/Dispatcher.php';

    $db = new class implements \Joomla\Database\DatabaseInterface {
        public array $executions = [];
        public string $operation = '';
        /**
         * Return a fluent simulated SQL builder.
         * @return self Test SQL builder.
         */
        public function createQuery(): self { return $this; }
        /**
         * Accept a SQL delete request.
         * @param string $table SQL table identifier.
         * @return self Fluent query builder.
         */
        public function delete(string $table): self { $this->operation = 'delete'; return $this; }
        /**
         * Accept a SQL where condition.
         * @param array $values SQL conditions.
         * @return self Fluent query builder.
         */
        public function where(array $values): self { return $this; }
        /**
         * Accept a SQL insert request.
         * @param string $table SQL table identifier.
         * @return self Fluent query builder.
         */
        public function insert(string $table): self { $this->operation = 'insert'; return $this; }
        /**
         * Accept the inserted SQL columns.
         * @param mixed $values SQL column names.
         * @return self Fluent query builder.
         */
        public function columns(mixed $values): self { return $this; }
        /**
         * Accept SQL insert values.
         * @param string $values Stored field values.
         * @return self Fluent query builder.
         */
        public function values(string $values): self { return $this; }
        /**
         * Quote table or column identifiers in the test.
         * @param mixed $name Table or column name.
         * @return mixed Quoted identifier.
         */
        public function quoteName(mixed $name): mixed { return $name; }
        /**
         * Quote a scalar SQL value.
         * @param string $value Field value.
         * @return string Quoted field value.
         */
        public function quote(string $value): string { return "'" . $value . "'"; }
        /**
         * Accept a query object for simulated execution.
         * @param self $query Database builder.
         * @return self Current test database.
         */
        public function setQuery(self $query): self { return $this; }
        /**
         * Simulate a silent failure when deleting old extra-field records.
         * @return bool Whether execution succeeded.
         */
        public function execute(): bool {
            $this->executions[] = $this->operation;
            return $this->operation !== 'delete';
        }
        /**
         * Return a test country record when requested.
         * @return mixed Stored country name.
         */
        public function loadResult(): mixed { return 'Germany'; }
    };
    TestSafetyRuntime::$database = $db;

    $location = \Diddipoeler\Component\SportsManagement\Site\Helper\LocationAddressHelper::class;
    $value = $location::build($db, (object) [
        'address' => str_repeat('a', 4097), 'state' => 'NRW',
        'location' => 'Gelsenkirchen', 'zipcode' => '45879', 'country' => '',
    ]);
    if ($value !== 'NRW, 45879 Gelsenkirchen') {
        throw new \RuntimeException('Oversized legacy location value was rendered.');
    }

    TestSafetyRuntime::$app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        /**
         * Return the active admin input task fixture.
         * @return object Joomla administrator input object.
         */
        public function getInput(): object {
            return new class {
                /**
                 * Return the form submit task name.
                 * @param string $name Form command name.
                 * @return string Task value.
                 */
                public function getCmd(string $name): string { return 'save'; }
            };
        }
        /**
         * Collect generic safe administrator error messages.
         * @param string $message Joomla translated error message.
         * @param string $type Message type.
         * @return void
         */
        public function enqueueMessage(string $message, string $type): void {}
    };

    $save = new \Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraFieldsSaveHelper();
    $save->save(['extraf' => ['new'], 'extra_id' => ['12']], 100, $db);
    if ($db->executions !== ['delete']
        || count(\Joomla\CMS\Log\Log::$messages) !== 1
        || !str_contains(\Joomla\CMS\Log\Log::$messages[0], 'RuntimeException')) {
        throw new \RuntimeException('Failed extra-field delete resulted in insertion.');
    }

    $params = new class {
        public array $values = ['template' => ['invalid']];
        /**
         * Return module parameter value or default.
         * @param string $key Module setting name.
         * @param mixed $default Default value.
         * @return mixed Configured value.
         */
        public function get(string $key, mixed $default = null): mixed {
            return $this->values[$key] ?? $default;
        }
        /**
         * Store a changed module setting.
         * @param string $key Setting name.
         * @param mixed $value Changed value.
         * @return void
         */
        public function set(string $key, mixed $value): void {
            $this->values[$key] = $value;
        }
    };

    \Joomla\CMS\Dispatcher\AbstractModuleDispatcher::$data = ['params' => $params];
    TestSafetyRuntime::$moduleApp = new class {
        public bool $isSite = false;
        /**
         * Check the active site client before mutating the module layout.
         * @param string $client Joomla client name.
         * @return bool Whether the active client matches.
         */
        public function isClient(string $client): bool { return $this->isSite; }
        /**
         * Return the translation loader fixture.
         * @return object Joomla translation fixture.
         */
        public function getLanguage(): object {
            return new class {
                /**
                 * Simulate loading a Joomla language bundle.
                 * @param mixed ...$args Language load arguments.
                 * @return bool Whether loading succeeded.
                 */
                public function load(mixed ...$args): bool { return true; }
            };
        }
        /**
         * Supply an HTML document fixture.
         * @return object Test document.
         */
        public function getDocument(): object { return new \stdClass(); }
    };

    $dispatcher = new \Diddipoeler\Module\SportsManagementTeamPlayers\Site\Dispatcher\Dispatcher();
    $method = new \ReflectionMethod($dispatcher, 'getLayoutData');
    try {
        $method->invoke($dispatcher);
        throw new \RuntimeException('Admin client was accepted by TeamPlayers.');
    } catch (\RuntimeException $e) {
        if (!str_contains($e->getMessage(), 'requires the Joomla site application')) {
            throw $e;
        }
    }
    if (array_key_exists('layout', $params->values)) {
        throw new \RuntimeException('TeamPlayers changed layout before checking site client.');
    }

    TestSafetyRuntime::$moduleApp->isSite = true;
    $result = $method->invoke($dispatcher);
    if ($params->get('layout') !== 'native'
        || $result['players'] !== []
        || $result['roster'] !== []) {
        throw new \RuntimeException('Valid site TeamPlayers module layout regressed.');
    }

    echo "Joomla 5/6 location bounds, extra-field delete checks, TeamPlayers client guard: OK\n";
}
