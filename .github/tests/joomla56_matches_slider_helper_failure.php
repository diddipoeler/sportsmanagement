<?php
/**
 * Test Joomla 5/6 matches-slider failures without leaking helper exceptions.
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
         * Return a valid database service for the failing helper test.
         *
         * @return object Joomla service container.
         */
        public static function getContainer(): object
        {
            return new class {
                /**
                 * Resolve the mock Joomla database.
                 *
                 * @param string $service Joomla DI service name.
                 * @return object Valid database implementation.
                 */
                public function get(string $service): object
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
         * Capture logged exception types for safe-logging assertions.
         *
         * @param string $message Server-side diagnostic.
         * @param int $priority Log severity.
         * @param string $category Component log channel.
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
         * Return a helper factory that fails while loading matches.
         *
         * @return object Mock Joomla module helper factory.
         */
        protected function getHelperFactory(): object
        {
            return new class {
                /**
                 * Supply a deliberately broken optional matches helper.
                 *
                 * @param string $name Requested native helper name.
                 * @return object Broken helper stub.
                 */
                public function getHelper(string $name): object
                {
                    return new class {
                        /**
                         * Simulate a database exception containing private details.
                         *
                         * @param mixed ...$args Joomla helper dependencies.
                         * @return array Never returns normally.
                         * @throws \RuntimeException Always throws.
                         */
                        public function getData(mixed ...$args): array
                        {
                            throw new \RuntimeException('PRIVATE_SLIDER_DATABASE_PASSWORD');
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
         * Bind the test Joomla frontend application.
         *
         * @param object $app Fake Joomla site.
         */
        public function __construct(object $app)
        {
            $this->app = $app;
        }

        /**
         * Provide Joomla-owned default module layout values.
         *
         * @return array<string,mixed> Module and param context.
         */
        protected function getLayoutData(): array|false
        {
            return ['module' => (object) ['id' => 3], 'params' => (object) [], 'layout' => 'default'];
        }

        /**
         * Return the Joomla site application to the native dispatcher.
         *
         * @return object Mock site runtime.
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
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_matchesslider/src/Dispatcher/Dispatcher.php';

    $app = new class {
        public array $messages = [];

        /**
         * Report a standard Joomla frontend client.
         *
         * @param string $client Joomla client alias.
         * @return bool True only for the site.
         */
        public function isClient(string $client): bool
        {
            return $client === 'site';
        }

        /**
         * Provide a module translation loader.
         *
         * @return object Fake language service.
         */
        public function getLanguage(): object
        {
            return new class {
                /**
                 * Accept requested component language files.
                 *
                 * @param mixed ...$args Joomla language loading arguments.
                 * @return bool Whether the fake load succeeded.
                 */
                public function load(mixed ...$args): bool
                {
                    return true;
                }
            };
        }

        /**
         * Avoid HTML-only web asset setup in the isolated test.
         *
         * @return object Generic document without WebAssetManager.
         */
        public function getDocument(): object
        {
            return new \stdClass();
        }

        /**
         * Record visitor-visible errors, which should remain empty.
         *
         * @param string $message Public notification.
         * @param string $type Joomla notification type.
         * @return void
         */
        public function enqueueMessage(string $message, string $type = 'warning'): void
        {
            $this->messages[] = $message;
        }
    };

    $dispatcher = new \Diddipoeler\Module\SportsManagementMatchesSlider\Site\Dispatcher\Dispatcher($app);
    $render = new \ReflectionMethod($dispatcher, 'getLayoutData');
    $result = $render->invoke($dispatcher);
    $logs = implode(' ', \Joomla\CMS\Log\Log::$messages);

    if (!is_array($result)
        || ($result['slidermatches'] ?? null) !== []
        || ($result['layout'] ?? '') !== 'default'
        || $app->messages !== []
        || !str_contains($logs, 'RuntimeException')
        || str_contains($logs, 'PRIVATE_SLIDER_DATABASE_PASSWORD')) {
        throw new \RuntimeException('Optional matches slider failure was not isolated safely.');
    }

    echo "Joomla 5/6 matches-slider helper failure isolation: OK\n";
}
