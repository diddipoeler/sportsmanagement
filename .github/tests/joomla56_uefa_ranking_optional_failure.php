<?php
/**
 * Ensure the Joomla 5/6 UEFA ranking module survives unavailable databases.
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
         * Return a test container with the expected database interface.
         *
         * @return object Mock dependency-injection container.
         */
        public static function getContainer(): object
        {
            return new class {
                /**
                 * Supply the valid placeholder Joomla database service.
                 *
                 * @param string $name Interface being resolved.
                 * @return object A typed database service.
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
         * Store diagnostic entries for safe logging assertions.
         *
         * @param string $message Internal log entry.
         * @param int $priority Severity level.
         * @param string $category Joomla log channel.
         * @return void
         */
        public static function add(string $message, int $priority, string $category): void
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
         * Return a deliberately failing coefficient data helper.
         *
         * @return object Mock Joomla helper factory.
         */
        protected function getHelperFactory(): object
        {
            return new class {
                /**
                 * Resolve a dummy native UEFA helper.
                 *
                 * @param string $name Requested helper name.
                 * @return object Failing helper implementation.
                 */
                public function getHelper(string $name): object
                {
                    return new class {
                        /**
                         * Simulate an optional remote SQL outage.
                         *
                         * @param mixed ...$args Module configuration dependencies.
                         * @return array Never reached.
                         * @throws \RuntimeException Always throws.
                         */
                        public function getData(mixed ...$args): array
                        {
                            throw new \RuntimeException('PRIVATE_UEFA_DATABASE_PASSWORD');
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
         * Construct a module dispatcher for the test application.
         *
         * @param object $app Joomla site test double.
         */
        public function __construct(object $app)
        {
            $this->app = $app;
        }

        /**
         * Supply the standard Joomla layout context.
         *
         * @return array<string,mixed> Shared module template data.
         */
        protected function getLayoutData(): array|false
        {
            return ['params' => (object) [], 'module' => (object) ['id' => 10], 'layout' => 'default'];
        }

        /**
         * Return the site application used by the module.
         *
         * @return object Site runtime.
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
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_uefawertung/src/Dispatcher/Dispatcher.php';

    $app = new class {
        public array $messages = [];

        /**
         * Identify this as a Joomla site application.
         *
         * @param string $client Requested client alias.
         * @return bool Whether frontend access is allowed.
         */
        public function isClient(string $client): bool
        {
            return $client === 'site';
        }

        /**
         * Supply a translation loader for module initialization.
         *
         * @return object Fake Joomla language.
         */
        public function getLanguage(): object
        {
            return new class {
                /**
                 * Accept module language loading.
                 *
                 * @param mixed ...$args Loading arguments.
                 * @return bool Whether the request was accepted.
                 */
                public function load(mixed ...$args): bool
                {
                    return true;
                }
            };
        }

        /**
         * Return a non-HTML document so no asset manager is required.
         *
         * @return object Fake Joomla document.
         */
        public function getDocument(): object
        {
            return new \stdClass();
        }

        /**
         * Capture unexpected visitor-visible error notifications.
         *
         * @param string $message Public notice.
         * @param string $severity Error severity.
         * @return void
         */
        public function enqueueMessage(string $message, string $severity = 'warning'): void
        {
            $this->messages[] = $message;
        }
    };

    $dispatcher = new \Diddipoeler\Module\SportsManagementUefaWertung\Site\Dispatcher\Dispatcher($app);
    $render = new \ReflectionMethod($dispatcher, 'getLayoutData');
    $result = $render->invoke($dispatcher);
    $log = implode(' ', \Joomla\CMS\Log\Log::$messages);

    if (!is_array($result)
        || ($result['seasons'] ?? null) !== []
        || ($result['rankings'] ?? null) !== []
        || ($result['layout'] ?? null) !== 'default'
        || $app->messages !== []
        || !str_contains($log, 'RuntimeException')
        || str_contains($log, 'PRIVATE_UEFA_DATABASE_PASSWORD')) {
        throw new \RuntimeException('UEFA ranking outage broke layout or leaked private diagnostics.');
    }

    echo "Joomla 5/6 UEFA ranking helper failure isolation: OK\n";
}
