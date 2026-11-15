<?php
/**
 * Regression test for Joomla 5/6 installer wizard MVC model failures.
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
         * Return translation keys in this isolated controller unit test.
         *
         * @param string $key Joomla language constant.
         * @return string Original translation key.
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
        public mixed $model = false;
        public array $redirect = [];

        /**
         * Store the lightweight Joomla administrator application.
         *
         * @param object $app Fake application exposing input and identity.
         */
        public function __construct(object $app)
        {
            $this->app = $app;
        }

        /**
         * Simulate successful form token validation.
         *
         * @return void
         */
        protected function checkToken(): void
        {
        }

        /**
         * Return the test MVC model, including false or incompatible objects.
         *
         * @param string $name Requested model name.
         * @param string $prefix Joomla MVC namespace.
         * @param array $config MVC factory options.
         * @return mixed Test model.
         */
        protected function getModel(string $name, string $prefix, array $config = []): mixed
        {
            return $this->model;
        }

        /**
         * Record the administrator redirect chosen by the controller.
         *
         * @param string $url Destination URL.
         * @param string $message Optional user-facing status.
         * @param string $type Optional message type.
         * @return void
         */
        protected function setRedirect(string $url, string $message = '', string $type = ''): void
        {
            $this->redirect = [$url, $message, $type];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Controller/InstallhelperController.php';

    $application = new class {
        /**
         * Provide an authorised installer user.
         *
         * @return object Fake administrator identity.
         */
        public function getIdentity(): object
        {
            return new class {
                /**
                 * Permit the administrator's install-helper action.
                 *
                 * @param string $permission Joomla permission.
                 * @param string $asset Extension asset.
                 * @return bool True only for component administration.
                 */
                public function authorise(string $permission, string $asset): bool
                {
                    return $permission === 'core.admin' && $asset === 'com_sportsmanagement';
                }
            };
        }

        /**
         * Provide the submitted installer form data.
         *
         * @return object Form input and POST data.
         */
        public function getInput(): object
        {
            return new class {
                public object $post;

                /**
                 * Build a minimal POST request container.
                 */
                public function __construct()
                {
                    $this->post = new class {
                        /**
                         * Return the sport type chosen in the test form.
                         *
                         * @return array<string,string> Installer POST fields.
                         */
                        public function getArray(): array
                        {
                            return ['filter_sports_type' => 'football'];
                        }
                    };
                }
            };
        }
    };

    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\InstallhelperController($application);

    foreach ([false, null, new \stdClass()] as $model) {
        $controller->model = $model;
        $controller->savesportstype();

        if (($controller->redirect[0] ?? '') !== 'index.php?option=com_sportsmanagement&view=installhelper&step=1'
            || ($controller->redirect[2] ?? '') !== 'error') {
            throw new \RuntimeException('Missing installer model did not redirect to step one.');
        }
    }

    $controller->model = new class {
        /**
         * Simulate a successful sports-type model save.
         *
         * @param array $post Submitted form fields.
         * @return array No warnings after successful insertion.
         */
        public function saveSportstype(array $post): array
        {
            return [];
        }
    };
    $controller->savesportstype();

    if (($controller->redirect[0] ?? '') !== 'index.php?option=com_sportsmanagement&view=installhelper&step=2') {
        throw new \RuntimeException('Valid sports-type model did not advance the installer.');
    }

    echo "Joomla 5/6 installer model guards: OK\n";
}
