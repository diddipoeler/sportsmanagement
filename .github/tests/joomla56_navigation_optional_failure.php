<?php
/**
 * Verify Joomla 5/6 native navigation skips optional database failures.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface
    {
    }
}

namespace Joomla\CMS {
    class Factory
    {
        /**
         * Provide a valid database service for a failing helper scenario.
         *
         * @return object Minimal Joomla container.
         */
        public static function getContainer(): object
        {
            return new class {
                /**
                 * Return an interface-compatible database stub.
                 *
                 * @param string $name Service identifier.
                 * @return object Typed database.
                 */
                public function get(string $name): object
                {
                    return new class implements \Joomla\Database\DatabaseInterface {
                    };
                }
            };
        }
    }
}

namespace Joomla\CMS\Log {
    class Log
    {
        public const ERROR = 3;
        public static array $messages = [];

        /**
         * Capture server diagnostic messages for information leakage checks.
         *
         * @param string $message Diagnostic log entry.
         * @param int $severity Severity.
         * @param string $category Joomla log category.
         * @return void
         */
        public static function add(string $message, int $severity, string $category): void
        {
            self::$messages[] = $message;
        }
    }
}

namespace Joomla\CMS\Document {
    class HtmlDocument
    {
    }
}

namespace Joomla\CMS\Helper {
    interface HelperFactoryAwareInterface
    {
    }

    trait HelperFactoryAwareTrait
    {
        /**
         * Return the failing native navigation helper.
         *
         * @return object Fake helper factory.
         */
        protected function getHelperFactory(): object
        {
            return new class {
                /**
                 * Provide the named navigation helper.
                 *
                 * @param string $name Native helper alias.
                 * @return object Failing helper.
                 */
                public function getHelper(string $name): object
                {
                    return new class {
                        /**
                         * Simulate a private database connection error.
                         *
                         * @param mixed ...$args Joomla helper arguments.
                         * @return array Never reached.
                         * @throws \RuntimeException For failure-isolation checks.
                         */
                        public function getData(mixed ...$args): array
                        {
                            throw new \RuntimeException('PRIVATE_NAV_DATABASE_PASSWORD');
                        }
                    };
                }
            };
        }
    }
}

namespace Joomla\CMS\Dispatcher {
    class AbstractModuleDispatcher
    {
        protected object $app;

        /**
         * Attach a fake frontend application.
         *
         * @param object $app Site application test double.
         */
        public function __construct(object $app)
        {
            $this->app = $app;
        }

        /**
         * Return the normal Joomla module template context.
         *
         * @return array<string,mixed> Parent rendering values.
         */
        protected function getLayoutData(): array|false
        {
            return ['module' => (object) ['id' => 4], 'params' => (object) []];
        }

        /**
         * Return the configured site application.
         *
         * @return object Test runtime.
         */
        protected function getApplication(): object
        {
            return $this->app;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    define('JPATH_SITE', '/site');
    define('JPATH_ADMINISTRATOR', '/administrator');

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_navigation_menu/src/Dispatcher/Dispatcher.php';

    $app = new class {
        public array $publicMessages = [];

        /**
         * Identify a frontend Joomla request.
         *
         * @param string $client Joomla client name.
         * @return bool Whether the request is frontend.
         */
        public function isClient(string $client): bool
        {
            return $client === 'site';
        }

        /**
         * Provide Joomla's translation loader.
         *
         * @return object Language service.
         */
        public function getLanguage(): object
        {
            return new class {
                /**
                 * Supply the test locale.
                 *
                 * @return string Language tag.
                 */
                public function getTag(): string
                {
                    return 'de-DE';
                }

                /**
                 * Accept module and component language loads.
                 *
                 * @param mixed ...$args Translation path and flags.
                 * @return bool Simulated success.
                 */
                public function load(mixed ...$args): bool
                {
                    return true;
                }
            };
        }

        /**
         * Detect accidental warnings shown to site visitors.
         *
         * @param string $message Frontend notification.
         * @param string $level Severity.
         * @return void
         */
        public function enqueueMessage(string $message, string $level = 'warning'): void
        {
            $this->publicMessages[] = $message;
        }
    };

    $dispatcher = new \Diddipoeler\Module\SportsManagementNavigationMenu\Site\Dispatcher\Dispatcher($app);
    $render = new \ReflectionMethod($dispatcher, 'getLayoutData');
    $result = $render->invoke($dispatcher);
    $logs = implode(' ', \Joomla\CMS\Log\Log::$messages);

    if ($result !== false || $app->publicMessages !== []
        || !str_contains($logs, 'RuntimeException')
        || str_contains($logs, 'PRIVATE_NAV_DATABASE_PASSWORD')) {
        throw new \RuntimeException('Navigation helper failure leaked details or broke module fallback.');
    }

    echo "Joomla 5/6 native navigation optional failure: OK\n";
}
