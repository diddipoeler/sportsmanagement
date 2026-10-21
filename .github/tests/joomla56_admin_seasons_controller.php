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

        protected function assertWritePermission(string $permission = 'core.edit'): void {}

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

    echo "Administrator Seasons controller model guards OK\n";
}
