<?php
/**
 * Joomla 5/6 administrator Seasons controller model-loading regressions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementAdminController
    {
        protected object $input;
        public array $lastConfig = [];
        public int $tokenChecks = 0;
        public int $permissionChecks = 0;

        public function __construct()
        {
            $this->input = new class {
                public object $post;

                public function __construct()
                {
                    $this->post = new class {
                        public function getArray(): array { return []; }
                    };
                }
            };
        }

        /**
         * Simulate Joomla's inherited form-token check in this isolated MVC test.
         *
         * The production SeasonsController invokes checkToken() before its
         * component ACL guard. The lightweight parent stub must provide that
         * framework method so the test reaches the missing-model assertion.
         *
         * @return void
         */
        protected function checkToken(): void
        {
            ++$this->tokenChecks;
        }

        /**
         * Record each required write-authorisation check in the controller.
         *
         * @param string $permission Component ACL action being enforced.
         * @return void
         */
        protected function assertWritePermission(string $permission = 'core.edit'): void
        {
            ++$this->permissionChecks;
        }

        public function getModel($name = '', $prefix = '', $config = [])
        {
            $this->lastConfig = $config;
            return null;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Controller/SeasonsController.php';

    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\SeasonsController();
    $controller->getModel('Season', 'Administrator', ['ignore_request' => false, 'test_flag' => 'kept']);

    if ($controller->lastConfig !== ['ignore_request' => true, 'test_flag' => 'kept']) {
        throw new \RuntimeException('Seasons controller discarded custom Joomla model options.');
    }

    foreach (['applypersons', 'applyteams'] as $task) {
        try {
            $controller->{$task}();
        } catch (\RuntimeException $error) {
            if ($error->getCode() === 500
                && str_contains($error->getMessage(), 'season model could not be loaded')) {
                continue;
            }
            throw $error;
        }

        throw new \RuntimeException('Missing season model was not rejected for ' . $task);
    }

    if ($controller->tokenChecks !== 2 || $controller->permissionChecks !== 2) {
        throw new \RuntimeException('Seasons assignment tasks must verify both CSRF tokens and write permissions.');
    }

    echo "Administrator Seasons controller model guards OK\n";
}
