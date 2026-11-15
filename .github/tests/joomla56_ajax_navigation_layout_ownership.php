<?php
/**
 * Verify Joomla 5/6 AJAX navigation preserves existing template variables.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

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
         * Return a fake helper factory that collides with Joomla layout data.
         *
         * @return object Test helper factory.
         */
        protected function getHelperFactory(): object
        {
            return new class {
                /**
                 * Supply a fake AJAX navigation data helper.
                 *
                 * @param string $name Requested module helper.
                 * @return object Fake helper returning template data.
                 */
                public function getHelper(string $name): object
                {
                    return new class {
                        /**
                         * Produce a helper payload that intentionally collides with Joomla.
                         *
                         * @param mixed ...$arguments Helper dependencies.
                         * @return array<string,mixed> Collision and normal module fields.
                         */
                        public function getData(mixed ...$arguments): array
                        {
                            return [
                                'module' => 'ATTACK',
                                'params' => 'ATTACK',
                                'layout' => 'ATTACK',
                                'helper' => 'ATTACK',
                                'navigationItems' => ['club', 'team'],
                                'clientConfig' => ['moduleId' => 8],
                            ];
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
         * Construct a dispatcher with a test Joomla site application.
         *
         * @param object $app Fake site application.
         */
        public function __construct(object $app)
        {
            $this->application = $app;
        }

        /**
         * Prepare Joomla-owned layout values and a preexisting helper slot.
         *
         * @return array<string,mixed> Framework layout context.
         */
        protected function getLayoutData(): array|false
        {
            $params = new class {
                public string $layout = 'default';

                /**
                 * Return a requested module option.
                 *
                 * @param string $key Module configuration name.
                 * @param mixed $default Fallback value.
                 * @return mixed Configured value or fallback.
                 */
                public function get(string $key, mixed $default = null): mixed
                {
                    return $key === 'layout' ? $this->layout : $default;
                }

                /**
                 * Update the chosen template layout.
                 *
                 * @param string $key Module configuration name.
                 * @param mixed $value New option value.
                 * @return void
                 */
                public function set(string $key, mixed $value): void
                {
                    if ($key === 'layout') {
                        $this->layout = (string) $value;
                    }
                }
            };

            return [
                'module' => (object) ['id' => 8],
                'params' => $params,
                'layout' => 'default',
                'helper' => 'FRAMEWORK',
            ];
        }

        /**
         * Expose the fake Joomla site application.
         *
         * @return object Fake site application.
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

    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_ajax_top_navigation_menu/src/Dispatcher/Dispatcher.php';

    $app = new class {
        /**
         * Indicate a Joomla site context for the navigation dispatcher.
         *
         * @param string $client Requested Joomla client.
         * @return bool True only for site rendering.
         */
        public function isClient(string $client): bool
        {
            return $client === 'site';
        }

        /**
         * Return the module language loader.
         *
         * @return object Fake Joomla language service.
         */
        public function getLanguage(): object
        {
            return new class {
                /**
                 * Return the frontend language tag.
                 *
                 * @return string Test language.
                 */
                public function getTag(): string
                {
                    return 'de-DE';
                }

                /**
                 * Simulate successful module and component language loading.
                 *
                 * @param mixed ...$values Joomla language loader arguments.
                 * @return bool True for this isolated test.
                 */
                public function load(mixed ...$values): bool
                {
                    return true;
                }
            };
        }

        /**
         * Return a non-HTML document to isolate layout assembly from assets.
         *
         * @return object Fake document.
         */
        public function getDocument(): object
        {
            return new \stdClass();
        }
    };

    $dispatcher = new \Diddipoeler\Module\SportsManagementAjaxTopNavigationMenu\Site\Dispatcher\Dispatcher($app);
    $render = new \ReflectionMethod($dispatcher, 'getLayoutData');
    $data = $render->invoke($dispatcher);

    if (!is_array($data)
        || !is_object($data['module'] ?? null)
        || !is_object($data['params'] ?? null)
        || $data['layout'] !== 'default'
        || $data['helper'] !== 'FRAMEWORK'
        || $data['navigationItems'] !== ['club', 'team']
        || $data['legacyLayout'] !== 'default'
        || $data['params']->layout !== 'native') {
        throw new \RuntimeException('Navigation helper replaced existing Joomla layout context.');
    }

    echo "Joomla 5/6 AJAX navigation layout ownership: OK\n";
}
