<?php
/**
 * Regression test for Joomla 5/6 module WebAssetManager reuse.
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
         * Supply a lightweight service container with a database interface.
         *
         * @return object Service container stub.
         */
        public static function getContainer(): object
        {
            return new class {
                /**
                 * Resolve the mock Joomla database dependency.
                 *
                 * @param string $id Requested container service interface.
                 * @return object A compatible database service.
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

namespace Joomla\CMS\Component {
    class ComponentHelper
    {
        /**
         * Provide minimal component options for the quotation dispatcher.
         *
         * @param string $component Joomla component option.
         * @return object Component configuration stub.
         */
        public static function getParams(string $component): object
        {
            return (object) [];
        }
    }
}

namespace Joomla\CMS\Document {
    class HtmlDocument
    {
        public object $assets;

        /**
         * Supply a shared page-level WebAssetManager test double.
         *
         * @param object $assets Asset registration and activation recorder.
         */
        public function __construct(object $assets)
        {
            $this->assets = $assets;
        }

        /**
         * Return the same WebAssetManager for all module instances.
         *
         * @return object Asset registration and activation recorder.
         */
        public function getWebAssetManager(): object
        {
            return $this->assets;
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
         * Return a minimal module helper factory with deterministic data.
         *
         * @return object Mock factory that resolves a module data helper.
         */
        protected function getHelperFactory(): object
        {
            return new class {
                /**
                 * Return a helper for the module being rendered.
                 *
                 * @param string $name Joomla helper alias.
                 * @return object Fake helper service.
                 */
                public function getHelper(string $name): object
                {
                    return new class {
                        /**
                         * Return empty but valid module records for all modules.
                         *
                         * @param mixed ...$arguments Module helper dependencies.
                         * @return array<string,mixed> Empty layout data.
                         */
                        public function getData(mixed ...$arguments): array
                        {
                            return [];
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
         * Create a test dispatcher bound to the current fake application.
         *
         * @param object $application Test site application.
         */
        public function __construct(object $application)
        {
            $this->application = $application;
        }

        /**
         * Provide shared Joomla module data in this isolated test.
         *
         * @return array<string,mixed> Params and module identity.
         */
        protected function getLayoutData(): array|false
        {
            return ['params' => (object) [], 'module' => (object) ['id' => 1]];
        }

        /**
         * Return the configured site application.
         *
         * @return object Site application stub.
         */
        protected function getApplication(): object
        {
            return $this->application;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    define('JPATH_ADMINISTRATOR', '/administrator');
    define('JPATH_SITE', '/site');

    foreach (['trainingsdata', 'uefawertung', 'rquotes'] as $module) {
        require_once dirname(__DIR__, 2)
            . '/modules/mod_sportsmanagement_' . $module . '/src/Dispatcher/Dispatcher.php';
    }

    $assets = new class {
        public array $registered = [];
        public array $activated = [];

        /**
         * Check whether Joomla has already registered a named asset.
         *
         * @param string $type Asset kind such as style.
         * @param string $name Unique asset identifier.
         * @return bool Whether registration was already performed.
         */
        public function assetExists(string $type, string $name): bool
        {
            return isset($this->registered[$type . ':' . $name]);
        }

        /**
         * Record stylesheet registration and reject duplicate registrations.
         *
         * @param string $name Joomla asset name.
         * @param string $url Relative asset file path.
         * @param array $options Joomla asset registration options.
         * @return void
         */
        public function registerStyle(string $name, string $url, array $options = []): void
        {
            $key = 'style:' . $name;

            if (isset($this->registered[$key])) {
                throw new \RuntimeException('Duplicate stylesheet registration: ' . $name);
            }

            $this->registered[$key] = ['url' => $url, 'options' => $options];
        }

        /**
         * Track requests to activate an existing registered style.
         *
         * @param string $name Unique stylesheet name.
         * @return void
         */
        public function useStyle(string $name): void
        {
            if (!$this->assetExists('style', $name)) {
                throw new \RuntimeException('Using an unregistered style: ' . $name);
            }

            $this->activated[$name] = ($this->activated[$name] ?? 0) + 1;
        }
    };

    $document = new \Joomla\CMS\Document\HtmlDocument($assets);
    $application = new class($document) {
        private object $document;

        /**
         * Keep the HTML document shared between module instances.
         *
         * @param object $document Stub HTML response document.
         */
        public function __construct(object $document)
        {
            $this->document = $document;
        }

        /**
         * Identify this application as Joomla's frontend site.
         *
         * @param string $client Requested Joomla application client.
         * @return bool True for frontend site requests.
         */
        public function isClient(string $client): bool
        {
            return $client === 'site';
        }

        /**
         * Return a language service that accepts translation loading.
         *
         * @return object Language loader test double.
         */
        public function getLanguage(): object
        {
            return new class {
                /**
                 * Accept translations requested by the module dispatcher.
                 *
                 * @param mixed ...$arguments Language loading arguments.
                 * @return bool True for this isolated test.
                 */
                public function load(mixed ...$arguments): bool
                {
                    return true;
                }
            };
        }

        /**
         * Return the shared HTML document.
         *
         * @return object Shared HTML document for all render calls.
         */
        public function getDocument(): object
        {
            return $this->document;
        }
    };

    $classes = [
        'trainingsdata' => \Diddipoeler\Module\SportsManagementTrainingsData\Site\Dispatcher\Dispatcher::class,
        'uefawertung' => \Diddipoeler\Module\SportsManagementUefaWertung\Site\Dispatcher\Dispatcher::class,
        'rquotes' => \Diddipoeler\Module\SportsManagementRquotes\Site\Dispatcher\Dispatcher::class,
    ];

    foreach ($classes as $module => $class) {
        $dispatcher = new $class($application);
        $render = new \ReflectionMethod($dispatcher, 'getLayoutData');

        // Simulate the same module displayed twice in one Joomla HTML page.
        $first = $render->invoke($dispatcher);
        $second = $render->invoke($dispatcher);

        $name = 'mod_sportsmanagement_' . $module;

        if (!is_array($first) || !is_array($second)
            || ($assets->activated[$name] ?? 0) !== 2
            || !isset($assets->registered['style:' . $name])) {
            throw new \RuntimeException('Module stylesheet was not reused: ' . $module);
        }
    }

    if (count($assets->registered) !== 3) {
        throw new \RuntimeException('Expected exactly three registered module styles.');
    }

    echo "Joomla 5/6 repeated module styles: OK\n";
}
