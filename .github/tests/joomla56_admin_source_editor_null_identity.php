<?php
/**
 * Test Joomla 5/6 extended PHP/XML editor authorization with absent identities.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    class Text
    {
        /**
         * Return a language key without starting Joomla.
         *
         * @param string $key Requested translation constant.
         * @return string Original key.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\Router {
    class Route
    {
        /**
         * Return a Joomla route without rewriting it.
         *
         * @param string $route Raw component URL.
         * @param bool $xhtml Whether HTML escaping is requested.
         * @return string Original route.
         */
        public static function _(string $route, bool $xhtml = true): string
        {
            return $route;
        }
    }
}

namespace Joomla\CMS\MVC\Controller {
    class FormController
    {
        protected object $app;
        public int $modelCalls = 0;

        /**
         * Inject a mock administrator application.
         *
         * @param object $app Fake identity provider.
         */
        public function __construct(object $app)
        {
            $this->app = $app;
        }

        /**
         * Simulate a valid form token.
         *
         * @return void
         */
        protected function checkToken(): void
        {
        }

        /**
         * Record model resolution calls that must not occur for guests.
         *
         * @param string $name Joomla model alias.
         * @param string $prefix MVC namespace.
         * @param array $config Model options.
         * @return bool Mock model absence.
         */
        public function getModel($name = '', $prefix = '', $config = [])
        {
            ++$this->modelCalls;

            return false;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Controller/SmextxmleditorController.php';

    $app = new class {
        public ?object $identity = null;

        /**
         * Return the current administrator identity.
         *
         * @return object|null Missing or authorised test user.
         */
        public function getIdentity(): ?object
        {
            return $this->identity;
        }
    };

    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\SmextxmleditorController($app);

    foreach ([null, new class {
        /**
         * Deny all administrator permissions in the test.
         *
         * @param string $permission Joomla ACL action.
         * @param string $asset Component identifier.
         * @return bool Always denied.
         */
        public function authorise(string $permission, string $asset): bool
        {
            return false;
        }
    }] as $identity) {
        $app->identity = $identity;

        try {
            $controller->save();
            throw new \LogicException('An unauthorised PHP editor save was accepted.');
        } catch (\RuntimeException $exception) {
            if ($exception->getCode() !== 403 || $controller->modelCalls !== 0) {
                throw $exception;
            }
        }
    }

    echo "Joomla 5/6 PHP/XML editor absent identity guards: OK\n";
}
