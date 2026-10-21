<?php
/**
 * Joomla 5/6 administrator divisions list action guards.
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
        protected object $app;
        protected object $input;
        public array $lastConfig = [];
        public ?object $model = null;

        public function __construct()
        {
            $post = new class {
                public function get(string $name, mixed $default = null, string $filter = 'raw'): mixed
                {
                    return $default;
                }
            };
            $this->input = new class($post) {
                public function __construct(public object $post) {}
            };
            $this->app = new class($this->input) {
                public function __construct(private object $input) {}
                public function getInput(): object { return $this->input; }
            };
        }

        public function getModel($name = '', $prefix = '', $config = [])
        {
            $this->lastConfig = $config;
            return $this->model;
        }

        protected function assertWritePermission(string $action = 'core.edit'): void {}
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Controller/DivisionsController.php';

    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\DivisionsController();
    $controller->getModel('Division', 'Administrator', ['ignore_request' => false, 'keep_option' => true]);

    if ($controller->lastConfig !== ['ignore_request' => true, 'keep_option' => true]) {
        throw new \RuntimeException('Divisions MVC options were lost.');
    }

    foreach (['massadd', 'divisiontoproject', 'saveOrder', 'saveshort'] as $task) {
        try {
            $controller->{$task}();
        } catch (\RuntimeException $error) {
            if ($error->getCode() === 500 && str_contains($error->getMessage(), 'division model')) {
                continue;
            }
            throw $error;
        }

        throw new \RuntimeException('Missing Division model was accepted in ' . $task);
    }

    echo "Administrator Divisions model guards OK\n";
}
