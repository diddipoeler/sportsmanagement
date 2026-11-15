<?php
/**
 * Test Joomla 5/6 AJAX navigation service and selector failures.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface
    {
    }
}

namespace Joomla\Database {
    interface DatabaseInterface
    {
    }
}

namespace Joomla\Registry {
    class Registry
    {
        /**
         * Supply a stored module option to the navigation helper.
         *
         * @param string $key Joomla module property.
         * @param mixed $default Configuration fallback.
         * @return mixed Fallback option.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $default;
        }
    }
}

namespace Joomla\CMS\Component {
    class ComponentHelper
    {
        /**
         * Supply a minimal component registry for database selection.
         *
         * @param string $name Joomla component name.
         * @return object Component settings.
         */
        public static function getParams(string $name): object
        {
            return new \Joomla\Registry\Registry();
        }
    }
}

namespace Joomla\CMS {
    class Factory
    {
        /**
         * Simulate an incorrectly configured Joomla database service.
         *
         * @return object Dependency-injection container.
         */
        public static function getContainer(): object
        {
            return new class {
                /**
                 * Return a service not implementing DatabaseInterface.
                 *
                 * @param string $service Requested interface.
                 * @return object Incompatible service.
                 */
                public function get(string $service): object
                {
                    return new \stdClass();
                }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_ajax_top_navigation_menu/src/Helper/AjaxTopNavigationHelper.php';

    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        public mixed $selector = 0;

        /**
         * Confirm the fake Joomla request uses the site client.
         *
         * @param string $client Joomla application client.
         * @return bool Whether it is a site request.
         */
        public function isClient(string $client): bool
        {
            return $client === 'site';
        }

        /**
         * Return a compatible input object with a configurable selector.
         *
         * @return object Input handling fake.
         */
        public function getInput(): object
        {
            return new class($this) {
                private object $app;

                /**
                 * Keep the parent request selector available for this test.
                 *
                 * @param object $app Fake Joomla application.
                 */
                public function __construct(object $app)
                {
                    $this->app = $app;
                }

                /**
                 * Return the requested database selector without filtering.
                 *
                 * @param string $key Joomla input field.
                 * @param mixed $default Fallback.
                 * @param string $filter Joomla input filtering style.
                 * @return mixed Configured selector.
                 */
                public function get(string $key, mixed $default = null, string $filter = 'RAW'): mixed
                {
                    return $this->app->selector;
                }

                /**
                 * Accept normalised database selectors from the module.
                 *
                 * @param string $key Input parameter to store.
                 * @param mixed $value Validated selector value.
                 * @return void
                 */
                public function set(string $key, mixed $value): void
                {
                }
            };
        }
    };

    $helper = new \Diddipoeler\Module\SportsManagementAjaxTopNavigationMenu\Site\Helper\AjaxTopNavigationHelper();
    $params = new \Joomla\Registry\Registry();

    $app->selector = ['invalid'];
    $result = $helper->getData($params, (object) ['id' => 3], $app);

    if ($result !== []) {
        throw new \RuntimeException('Malformed database selector must not select a database.');
    }

    $app->selector = '0001';

    try {
        $helper->getData($params, (object) ['id' => 3], $app);
        throw new \LogicException('An incompatible Joomla database service was accepted.');
    } catch (\RuntimeException $exception) {
        if ($exception->getCode() !== 500
            || !str_contains($exception->getMessage(), 'database service is unavailable')) {
            throw $exception;
        }
    }

    echo "Joomla 5/6 AJAX navigation database dependency guards: OK\n";
}
