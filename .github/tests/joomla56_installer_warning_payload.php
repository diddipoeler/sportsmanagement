<?php
/**
 * Verify Joomla 5/6 installer wizard handles malformed warning results.
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
         * Use language keys in the isolated controller test.
         *
         * @param string $key Joomla translation identifier.
         * @return string Untranslated message identifier.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementFormController
    {
        protected object $app;
        public mixed $model = null;
        public array $redirect = [];

        /**
         * Set the mock Joomla administrator application.
         *
         * @param object $app Authorised application test double.
         */
        public function __construct(object $app)
        {
            $this->app = $app;
        }

        /**
         * Validate the mock Joomla CSRF token.
         *
         * @return void
         */
        protected function checkToken(): void
        {
        }

        /**
         * Provide a test model to the installer controller.
         *
         * @param string $name Model name.
         * @param string $prefix Administrator namespace.
         * @param array $config Model options.
         * @return mixed Mock model.
         */
        protected function getModel(string $name, string $prefix, array $config): mixed
        {
            return $this->model;
        }

        /**
         * Capture the installer redirect without starting Joomla.
         *
         * @param string $route Target wizard page.
         * @param string $message Status text.
         * @param string $type Joomla severity.
         * @return void
         */
        public function setRedirect(string $route, string $message = '', string $type = ''): void
        {
            $this->redirect = [$route, $message, $type];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Controller/InstallhelperController.php';

    $app = new class {
        public array $messages = [];

        /**
         * Return a user with component administrator privileges.
         *
         * @return object Administrator identity.
         */
        public function getIdentity(): object
        {
            return new class {
                /**
                 * Authorise component administration.
                 *
                 * @param string $action Joomla ACL action.
                 * @param string $asset Component name.
                 * @return bool Allowed for the required permission only.
                 */
                public function authorise(string $action, string $asset): bool
                {
                    return $action === 'core.admin' && $asset === 'com_sportsmanagement';
                }
            };
        }

        /**
         * Return a minimal input object supplying the posted wizard data.
         *
         * @return object Joomla Input test double.
         */
        public function getInput(): object
        {
            return new class {
                public object $post;

                /**
                 * Initialise the post input collection.
                 */
                public function __construct()
                {
                    $this->post = new class {
                        /**
                         * Produce an installer payload.
                         *
                         * @return array<string,mixed> Submitted form data.
                         */
                        public function getArray(): array
                        {
                            return ['filter_sports_type' => 'football'];
                        }
                    };
                }
            };
        }

        /**
         * Capture safe Joomla installer warnings.
         *
         * @param string $message Public notice.
         * @param string $type Joomla severity.
         * @return void
         */
        public function enqueueMessage(string $message, string $type = 'message'): void
        {
            $this->messages[] = [$message, $type];
        }
    };

    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\InstallhelperController($app);
    $result = new class {
        public mixed $warnings = [];

        /**
         * Return a configurable legacy installer warning payload.
         *
         * @param array $post Submitted wizard values.
         * @return mixed Model return value to validate.
         */
        public function saveSportstype(array $post): mixed
        {
            return $this->warnings;
        }
    };
    $controller->model = $result;

    foreach ([true, 'success', new \stdClass(), null] as $invalid) {
        $result->warnings = $invalid;
        $controller->savesportstype();

        if (($controller->redirect[2] ?? null) !== 'error'
            || !str_contains($controller->redirect[0] ?? '', 'step=1')) {
            throw new \RuntimeException('Malformed installer result bypassed safe failure redirect.');
        }
    }

    $result->warnings = ['Approved warning', ['nested'], new \stdClass(), ''];
    $app->messages = [];
    $controller->savesportstype();

    if ($app->messages !== [['Approved warning', 'warning']]
        || !str_contains($controller->redirect[0] ?? '', 'step=1')) {
        throw new \RuntimeException('Installer warning payload was not safely filtered.');
    }

    $result->warnings = [];
    $controller->savesportstype();

    if (!str_contains($controller->redirect[0] ?? '', 'step=2')) {
        throw new \RuntimeException('Valid empty installer warnings did not advance wizard.');
    }

    echo "Joomla 5/6 installer warning payload: OK\n";
}
