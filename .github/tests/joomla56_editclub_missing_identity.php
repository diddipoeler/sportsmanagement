<?php
/**
 * Regression coverage for Joomla 5/6 frontend club editor identity guards.
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
         * Return the language key for identity error assertions.
         *
         * @param string $key Requested translation key.
         * @return string Original language key.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\MVC\Controller {
    class FormController
    {
        protected object $app;

        /**
         * Inject a minimal site application for permission tests.
         *
         * @param object $app Application exposing the current identity.
         */
        public function __construct(object $app)
        {
            $this->app = $app;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Controller/EditclubController.php';

    $app = new class {
        public ?object $identity = null;

        /**
         * Return the identity configured for this regression case.
         *
         * @return object|null User record or missing session.
         */
        public function getIdentity(): ?object
        {
            return $this->identity;
        }
    };

    $controller = new \Diddipoeler\Component\SportsManagement\Site\Controller\EditclubController($app);
    $guard = new \ReflectionMethod($controller, 'assertEditorPermission');

    foreach ([null, new class {
        /**
         * Represent a user with neither club nor component edit rights.
         *
         * @param string $action Requested ACL action.
         * @param string $asset Component asset.
         * @return bool Denied permission.
         */
        public function authorise(string $action, string $asset): bool
        {
            return false;
        }
    }] as $identity) {
        $app->identity = $identity;

        try {
            $guard->invoke($controller);
            throw new \LogicException('Unauthorised frontend club editor was accepted.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 403 || $e->getMessage() !== 'JERROR_ALERTNOAUTHOR') {
                throw $e;
            }
        }
    }

    foreach (['core.edit', 'club.edit'] as $permission) {
        $app->identity = new class($permission) {
            /**
             * Store the single permission assigned to the fake Joomla user.
             *
             * @param string $permission Allowed permission for this identity.
             */
            public function __construct(private string $permission)
            {
            }

            /**
             * Permit only the configured SportsManagement edit permission.
             *
             * @param string $action ACL action.
             * @param string $asset Component asset.
             * @return bool Whether this identity may edit the club.
             */
            public function authorise(string $action, string $asset): bool
            {
                return $action === $this->permission && $asset === 'com_sportsmanagement';
            }
        };

        $guard->invoke($controller);
    }

    echo "Joomla 5/6 frontend club editor identity guards: OK\n";
}
