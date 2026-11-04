<?php
/**
 * Ensure the Joomla 5/6 person editor rejects missing and guest identities.
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
         * Return the requested translation key in this isolated unit test.
         *
         * @param string $key Joomla language constant.
         * @return string Untranslated key for assertion messages.
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
         * Inject a simple application with a controllable identity.
         *
         * @param object $app Fake site application for this regression test.
         */
        public function __construct(object $app)
        {
            $this->app = $app;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Controller/EditpersonController.php';

    $application = new class {
        public ?object $identity = null;

        /**
         * Return the identity offered to the person-editing controller.
         *
         * @return object|null Current fake Joomla user or null.
         */
        public function getIdentity(): ?object
        {
            return $this->identity;
        }
    };

    $controller = new \Diddipoeler\Component\SportsManagement\Site\Controller\EditpersonController($application);
    $permission = new \ReflectionMethod($controller, 'assertEditorPermission');

    $guest = new class {
        /**
         * Simulate Joomla's component ACL lookup.
         *
         * @param string $action Requested Joomla permission.
         * @param string $asset Component asset identifier.
         * @return bool Always denied for guests.
         */
        public function authorise(string $action, string $asset): bool
        {
            return false;
        }
    };

    foreach ([null, $guest] as $identity) {
        $application->identity = $identity;

        try {
            $permission->invoke($controller);
        } catch (\RuntimeException $error) {
            if ($error->getCode() === 403
                && $error->getMessage() === 'JERROR_ALERTNOAUTHOR') {
                continue;
            }

            throw $error;
        }

        throw new \RuntimeException('Unauthorised person editor was not rejected.');
    }

    $application->identity = new class {
        /**
         * Authorise a component editor for the positive test case.
         *
         * @param string $action Joomla editing permission.
         * @param string $asset SportsManagement component identifier.
         * @return bool True only for component-level editing.
         */
        public function authorise(string $action, string $asset): bool
        {
            return $action === 'core.edit' && $asset === 'com_sportsmanagement';
        }
    };

    $permission->invoke($controller);
    echo "Frontend person editor identity guards OK\n";
}
