<?php
/**
 * Regression test for authenticated administrator GitHub issue creation.
 *
 * Ensures absent identities and guests never reach the outbound issue model
 * even when a legacy ACL implementation supplies an unexpected grant.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Controller {
    class BaseController
    {
        protected object $app;
        public bool $tokenValid = true;
        public int $modelLookups = 0;
        public object|false $testModel = false;
        public array $redirect = [];

        /**
         * Construct a minimal Joomla controller with an injected application.
         *
         * @param array $config Unused controller configuration.
         * @param mixed $factory Unused MVC factory dependency.
         * @param object|null $app Fake administrator application.
         * @param mixed $input Optional input dependency.
         */
        public function __construct($config = [], $factory = null, $app = null, $input = null)
        {
            $this->app = $app;
        }

        /**
         * Simulate Joomla's mandatory form-token validation.
         *
         * @throws \RuntimeException When the submitted token is invalid.
         * @return void
         */
        public function checkToken(): void
        {
            if (!$this->tokenValid) {
                throw new \RuntimeException('Invalid form token.', 403);
            }
        }

        /**
         * Track any attempted model access during a GitHub issue submission.
         *
         * @param string $name Requested administrator model.
         * @param string $prefix Joomla MVC namespace prefix.
         * @param array $config Controller model options.
         * @return object|false The configured test issue model.
         */
        public function getModel($name = '', $prefix = '', $config = []): object|false
        {
            ++$this->modelLookups;

            return $this->testModel;
        }

        /**
         * Store the redirect selected after a successful or failed submission.
         *
         * @param string $url Administrator destination.
         * @param string $message Translated result description.
         * @param string $type Joomla message severity.
         * @return void
         */
        public function setRedirect(string $url, string $message = '', string $type = ''): void
        {
            $this->redirect = compact('url', 'message', 'type');
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return a language key unchanged for isolated controller testing.
         *
         * @param string $key Joomla language identifier.
         * @return string Same key used for assertion context.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace {
    \define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/admin/src/Controller/GithubController.php';

    $app = new class {
        public ?object $identity = null;

        /**
         * Return the simulated Joomla user or no current identity.
         *
         * @return object|null Current administrator identity.
         */
        public function getIdentity(): ?object
        {
            return $this->identity;
        }
    };

    $issueModel = new class {
        public int $calls = 0;

        /**
         * Count outbound issue creation attempts without network access.
         *
         * @return bool Successful model operation.
         */
        public function addissue(): bool
        {
            ++$this->calls;

            return true;
        }
    };

    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\GithubController(
        [], null, $app
    );
    $controller->testModel = $issueModel;

    // Reject missing Joomla identity, guest and a denied authenticated user.
    foreach ([[null, 0, false], [0, 0, true], [14, 14, false]] as [$id, $expectedId, $grant]) {
        $app->identity = $id === null ? null : new class($expectedId, $grant) {
            /**
             * Create a user with a specified ID and authorization outcome.
             *
             * @param int $id Joomla user ID (zero means guest).
             * @param bool $grant Whether ACL grants component management.
             */
            public function __construct(public int $id, private bool $grant)
            {
            }

            /**
             * Simulate a component ACL decision for the user.
             *
             * @param string $action Joomla permission name.
             * @param string $asset Component permission asset.
             * @return bool Test's requested ACL outcome.
             */
            public function authorise(string $action, string $asset): bool
            {
                return $this->grant && $action === 'core.manage'
                    && $asset === 'com_sportsmanagement';
            }
        };

        $denied = false;
        try {
            $controller->addissue();
        } catch (\RuntimeException $exception) {
            $denied = $exception->getCode() === 403;
        }

        if (!$denied || $controller->modelLookups !== 0 || $issueModel->calls !== 0) {
            throw new \RuntimeException('Missing/guest/denied identity was allowed to create a GitHub issue.');
        }
    }

    $app->identity = new class {
        public int $id = 42;

        /**
         * Grant the permitted administrator component manage access.
         *
         * @param string $action Permission name.
         * @param string $asset Permission asset.
         * @return bool True only for SportsManagement management.
         */
        public function authorise(string $action, string $asset): bool
        {
            return $action === 'core.manage' && $asset === 'com_sportsmanagement';
        }
    };

    $controller->tokenValid = false;
    $deniedToken = false;

    try {
        $controller->addissue();
    } catch (\RuntimeException $exception) {
        $deniedToken = $exception->getCode() === 403;
    }

    if (!$deniedToken || $controller->modelLookups !== 0 || $issueModel->calls !== 0) {
        throw new \RuntimeException('Invalid Joomla form token reached the GitHub issue model.');
    }

    $controller->tokenValid = true;
    $controller->addissue();

    if ($controller->modelLookups !== 1
        || $issueModel->calls !== 1
        || ($controller->redirect['type'] ?? '') !== 'message'
        || !str_contains($controller->redirect['url'] ?? '', 'view=github')) {
        throw new \RuntimeException('Authenticated GitHub issue submission failed.');
    }

    echo "Joomla 5/6 GitHub issue identity and CSRF validation OK\n";
}
