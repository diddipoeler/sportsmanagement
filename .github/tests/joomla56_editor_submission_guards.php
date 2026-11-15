<?php
/**
 * Regression coverage for Joomla 5/6 frontend editor submission and identity guards.
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
         * Return untranslated Joomla language keys in the isolated test.
         *
         * @param string $key Language key requested by the controller.
         * @return string Untranslated language key.
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
        public array $tokens = [];

        /**
         * Supply a fake Joomla application for controller authorisation tests.
         *
         * @param object $app Application exposing getIdentity().
         */
        public function __construct(object $app)
        {
            $this->app = $app;
        }

        /**
         * Record whether legacy submit tasks explicitly check the POST token.
         *
         * @param string $method Token transport expected by the controller.
         * @return void
         */
        protected function checkToken(string $method = 'post'): void
        {
            $this->tokens[] = $method;
        }
    }
}

namespace {
    define('_JEXEC', 1);

    foreach (['Editperson', 'Editteam', 'Editprojectteam'] as $editor) {
        require_once dirname(__DIR__, 2) . '/site/src/Controller/' . $editor . 'Controller.php';
    }

    /**
     * Fail the regression with a useful explanation when a guard is missing.
     *
     * @param bool $condition Required controller security property.
     * @param string $message Human-readable reason for failure.
     * @return void
     */
    function editorGuardAssert(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    $application = new class {
        public ?object $identity = null;

        /**
         * Return the fake current Joomla identity, including absent sessions.
         *
         * @return object|null Current fake identity, or null.
         */
        public function getIdentity(): ?object
        {
            return $this->identity;
        }
    };

    $deniedIdentity = new class {
        /**
         * Reject component edits for an unauthorised or guest identity.
         *
         * @param string $action Joomla permission being checked.
         * @param string $asset Component asset being protected.
         * @return bool Always false in this negative case.
         */
        public function authorise(string $action, string $asset): bool
        {
            return false;
        }
    };

    $allowedIdentity = new class {
        /**
         * Permit only the expected component-level edit operation.
         *
         * @param string $action Joomla permission being checked.
         * @param string $asset Component asset being protected.
         * @return bool True only for SportsManagement edit permission.
         */
        public function authorise(string $action, string $asset): bool
        {
            return $action === 'core.edit' && $asset === 'com_sportsmanagement';
        }
    };

    foreach (['Editperson', 'Editteam', 'Editprojectteam'] as $editor) {
        $class = 'Diddipoeler\\Component\\SportsManagement\\Site\\Controller\\' . $editor . 'Controller';
        $controller = new $class($application);
        $permission = new \ReflectionMethod($controller, 'assertEditorPermission');

        foreach ([null, $deniedIdentity] as $identity) {
            $application->identity = $identity;

            try {
                $permission->invoke($controller);
                throw new \LogicException($editor . ' accepted an unauthorised identity.');
            } catch (\RuntimeException $exception) {
                editorGuardAssert(
                    $exception->getCode() === 403 && $exception->getMessage() === 'JERROR_ALERTNOAUTHOR',
                    $editor . ' returned an incorrect authorisation error.'
                );
            }
        }

        $application->identity = $allowedIdentity;
        $permission->invoke($controller);
        editorGuardAssert($controller->submit() === true, $editor . ' submission was not accepted.');
        editorGuardAssert($controller->tokens === ['post'], $editor . ' did not check its POST token.');
    }

    echo "Joomla 5/6 frontend editor submission guards: OK\n";
}
