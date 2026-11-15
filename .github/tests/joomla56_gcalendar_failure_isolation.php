<?php
/**
 * Ensure Joomla 5/6 GCalendar helper failures do not break site rendering.
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
         * Supply a test container containing Joomla's database service.
         *
         * @return object Fake dependency-injection container.
         */
        public static function getContainer(): object
        {
            return new class {
                /**
                 * Resolve the module database dependency.
                 *
                 * @param string $id Requested service.
                 * @return object Fake database implementation.
                 */
                public function get(string $id): object
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
         * Capture log messages without printing integration secrets.
         *
         * @param string $message Diagnostic text.
         * @param int $priority Joomla log severity.
         * @param string $category Component log category.
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
        public array $options = [];

        /**
         * Return a minimal Joomla WebAssetManager test double.
         *
         * @return object Asset manager accepting reuse of registered assets.
         */
        public function getWebAssetManager(): object
        {
            return new class {
                private array $assets = [];

                /**
                 * Check whether a requested asset has already been registered.
                 *
                 * @param string $type Asset type.
                 * @param string $name Asset name.
                 * @return bool Whether the asset exists.
                 */
                public function assetExists(string $type, string $name): bool
                {
                    return isset($this->assets[$type . ':' . $name]);
                }

                /**
                 * Register the calendar module CSS asset.
                 *
                 * @param string $name Asset key.
                 * @param string $url Relative asset path.
                 * @param array $options Cache settings.
                 * @return void
                 */
                public function registerStyle(string $name, string $url, array $options = []): void
                {
                    $this->assets['style:' . $name] = true;
                }

                /**
                 * Register the calendar JavaScript asset.
                 *
                 * @param string $name Asset key.
                 * @param string $url Relative asset path.
                 * @param array $options Cache settings.
                 * @param array $attributes Script HTML attributes.
                 * @return void
                 */
                public function registerScript(string $name, string $url, array $options = [], array $attributes = []): void
                {
                    $this->assets['script:' . $name] = true;
                }

                /**
                 * Mark a registered stylesheet as used.
                 *
                 * @param string $name Asset name.
                 * @return void
                 */
                public function useStyle(string $name): void
                {
                }

                /**
                 * Mark a registered script as used.
                 *
                 * @param string $name Asset name.
                 * @return void
                 */
                public function useScript(string $name): void
                {
                }
            };
        }

        /**
         * Record calendar client options for inspection.
         *
         * @param string $key Per-module options key.
         * @param array $values Client configuration.
         * @return void
         */
        public function addScriptOptions(string $key, array $values): void
        {
            $this->options[$key] = $values;
        }
    }
}

namespace Joomla\CMS\Helper {
    interface HelperFactoryAwareInterface
    {
    }

    trait HelperFactoryAwareTrait
    {
        /**
         * Supply a module helper factory that simulates failed API loading.
         *
         * @return object Module helper factory.
         */
        protected function getHelperFactory(): object
        {
            return new class {
                /**
                 * Return a failing calendar data helper.
                 *
                 * @param string $name Helper class alias.
                 * @return object Helper that raises a private API error.
                 */
                public function getHelper(string $name): object
                {
                    return new class {
                        /**
                         * Simulate a failed optional calendar integration.
                         *
                         * @param mixed ...$args Helper dependencies.
                         * @return array Never returns normally.
                         * @throws \RuntimeException Always, to exercise the fallback.
                         */
                        public function getData(mixed ...$args): array
                        {
                            throw new \RuntimeException('private-token-example');
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
         * Construct a module dispatcher with a mock Joomla site.
         *
         * @param object $app Fake Joomla site application.
         */
        public function __construct(object $app)
        {
            $this->application = $app;
        }

        /**
         * Return framework-owned module layout data.
         *
         * @return array<string,mixed> Module context.
         */
        protected function getLayoutData(): array|false
        {
            return ['module' => (object) ['id' => 5], 'params' => (object) [], 'layout' => 'default'];
        }

        /**
         * Return the Joomla site application used by the dispatcher.
         *
         * @return object Fake application.
         */
        protected function getApplication(): object
        {
            return $this->application;
        }
    }
}

namespace {
    define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_gcalendar/src/Dispatcher/Dispatcher.php';

    $document = new \Joomla\CMS\Document\HtmlDocument();
    $application = new class($document) {
        private object $document;

        /**
         * Retain the test HTML document for the frontend dispatcher.
         *
         * @param object $document HTML document.
         */
        public function __construct(object $document)
        {
            $this->document = $document;
        }

        /**
         * Indicate that the dispatcher is running on the site client.
         *
         * @param string $client Joomla client name.
         * @return bool True only for the site.
         */
        public function isClient(string $client): bool
        {
            return $client === 'site';
        }

        /**
         * Provide the mock HTML response document.
         *
         * @return object Shared HTML document.
         */
        public function getDocument(): object
        {
            return $this->document;
        }
    };

    $dispatcher = new \Diddipoeler\Module\SportsManagementGcalendar\Site\Dispatcher\Dispatcher($application);
    $render = new \ReflectionMethod($dispatcher, 'getLayoutData');
    $data = $render->invoke($dispatcher);

    if (!is_array($data) || $data['events'] !== [] || $data['calendarOptionsKey'] !== 'mod_sportsmanagement_gcalendar.5'
        || $data['layout'] !== 'default' || ($document->options['mod_sportsmanagement_gcalendar.5'] ?? null) !== []) {
        throw new \RuntimeException('GCalendar helper failure did not preserve safe Joomla layout data.');
    }

    $log = implode(' ', \Joomla\CMS\Log\Log::$messages);

    if (!str_contains($log, 'RuntimeException') || str_contains($log, 'private-token-example')) {
        throw new \RuntimeException('GCalendar failure logging exposed API details or missed the error.');
    }

    echo "Joomla 5/6 GCalendar failure isolation: OK\n";
}
