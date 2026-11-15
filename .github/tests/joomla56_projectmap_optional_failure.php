<?php
/**
 * Verify Joomla 5/6 project map outages do not enqueue public warnings.
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
         * Provide the Joomla database service to the module dispatcher.
         *
         * @return object Fake service container.
         */
        public static function getContainer(): object
        {
            return new class {
                /**
                 * Return a database service implementing the required interface.
                 *
                 * @param string $name Requested interface.
                 * @return object Joomla database test double.
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

namespace Joomla\CMS\Component {
    class ComponentHelper
    {
        /**
         * Return a current-season configuration for map selection.
         *
         * @param string $name Requested Joomla component.
         * @return object Component configuration.
         */
        public static function getParams(string $name): object
        {
            return new class {
                /**
                 * Return configured test seasons without a Joomla installation.
                 *
                 * @param string $key Component option.
                 * @param mixed $default Default configuration.
                 * @return mixed Test configuration.
                 */
                public function get(string $key, mixed $default = null): mixed
                {
                    return $default;
                }
            };
        }
    }
}

namespace Joomla\CMS\Log {
    class Log
    {
        public const ERROR = 3;
        public static array $entries = [];

        /**
         * Capture a safe server-side diagnostic about the failed map.
         *
         * @param string $message Diagnostic text.
         * @param int $severity Log priority.
         * @param string $category Log category.
         * @return void
         */
        public static function add(string $message, int $severity, string $category): void
        {
            self::$entries[] = $message;
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
         * Return a factory with a deliberately failing map helper.
         *
         * @return object Test helper factory.
         */
        protected function getHelperFactory(): object
        {
            return new class {
                /**
                 * Return the failing project-map helper.
                 *
                 * @param string $name Helper alias.
                 * @return object Helper stub.
                 */
                public function getHelper(string $name): object
                {
                    return new class {
                        /**
                         * Simulate a database or external map-service outage.
                         *
                         * @param mixed ...$args Helper dependencies.
                         * @return array Never returns.
                         * @throws \RuntimeException Always throws with a private detail.
                         */
                        public function getMapData(mixed ...$args): array
                        {
                            throw new \RuntimeException('private-database-details');
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
        protected object $application;

        /**
         * Construct the test dispatcher for the frontend.
         *
         * @param object $app Mock Joomla application.
         */
        public function __construct(object $app)
        {
            $this->application = $app;
        }

        /**
         * Supply a standard Joomla module layout.
         *
         * @return array<string,mixed> Parent layout state.
         */
        protected function getLayoutData(): array|false
        {
            return ['module' => (object) ['id' => 3], 'params' => (object) []];
        }

        /**
         * Return the fake site application.
         *
         * @return object Test application.
         */
        protected function getApplication(): object
        {
            return $this->application;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    define('JPATH_SITE', '/site');
    define('JPATH_ADMINISTRATOR', '/administrator');

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_projectmap/src/Dispatcher/Dispatcher.php';

    $app = new class {
        public array $messages = [];

        /**
         * Identify the application as a Joomla site request.
         *
         * @param string $name Requested client.
         * @return bool True for frontend requests.
         */
        public function isClient(string $name): bool
        {
            return $name === 'site';
        }

        /**
         * Return a language service for component translation loading.
         *
         * @return object Language loader.
         */
        public function getLanguage(): object
        {
            return new class {
                /**
                 * Return the current language.
                 *
                 * @return string Language tag.
                 */
                public function getTag(): string
                {
                    return 'de-DE';
                }

                /**
                 * Accept module and component translation requests.
                 *
                 * @param mixed ...$args Translation parameters.
                 * @return bool Success.
                 */
                public function load(mixed ...$args): bool
                {
                    return true;
                }
            };
        }

        /**
         * Provide a non-HTML response so optional assets are not registered.
         *
         * @return object Generic non-HTML Joomla document.
         */
        public function getDocument(): object
        {
            return new \stdClass();
        }

        /**
         * Record user-visible warnings to catch unexpected error disclosure.
         *
         * @param string $message Visitor-visible text.
         * @param string $type Message severity.
         * @return void
         */
        public function enqueueMessage(string $message, string $type = 'message'): void
        {
            $this->messages[] = [$message, $type];
        }
    };

    $dispatcher = new \Diddipoeler\Module\SportsManagementProjectMap\Site\Dispatcher\Dispatcher($app);
    $method = new \ReflectionMethod($dispatcher, 'getLayoutData');
    $data = $method->invoke($dispatcher);

    if (!is_array($data) || ($data['projects'] ?? null) !== [] || $app->messages !== []) {
        throw new \RuntimeException('Map outage exposed warnings or invalid layout state.');
    }

    $logged = implode(' ', \Joomla\CMS\Log\Log::$entries);

    if (!str_contains($logged, 'RuntimeException') || str_contains($logged, 'private-database-details')) {
        throw new \RuntimeException('Map diagnostic leaked private failure details.');
    }

    echo "Joomla 5/6 optional project map failure: OK\n";
}
