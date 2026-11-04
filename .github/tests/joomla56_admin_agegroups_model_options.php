<?php
/**
 * Verify that the Joomla 5/6 Agegroups controller preserves MVC model options.
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
        public array $lastConfig = [];
        public bool $modelUnavailable = false;

        /**
         * Simulate the parent controller's authorization guard for test calls.
         *
         * The isolated test focuses on model resolution, so no full Joomla
         * user/session container is required to reach that code path.
         *
         * @param string $permission Requested Joomla component action.
         * @return void
         */
        protected function assertWritePermission(string $permission = 'core.edit'): void
        {
        }

        /**
         * Capture the options passed to the Joomla MVC model factory.
         *
         * This stand-in returns the requested name and prefix without
         * bootstrapping Joomla, allowing controller delegation to be tested.
         *
         * @param string $name Requested model name.
         * @param string $prefix MVC client namespace prefix.
         * @param array $config Options provided to the model factory.
         * @return object Lightweight representation of the requested model.
         */
        public function getModel($name = '', $prefix = '', $config = [])
        {
            $this->lastConfig = $config;

            return $this->modelUnavailable ? null : (object) ['name' => $name, 'prefix' => $prefix];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Controller/AgegroupsController.php';

    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\AgegroupsController();

    $model = $controller->getModel('Agegroup', 'Administrator', [
        'ignore_request' => false,
        'custom_option' => ['keep' => true],
    ]);

    if ($model->name !== 'Agegroup'
        || $model->prefix !== 'Administrator'
        || $controller->lastConfig !== [
            'ignore_request' => true,
            'custom_option' => ['keep' => true],
        ]) {
        throw new \RuntimeException('Agegroups controller discarded caller-supplied MVC options.');
    }

    $controller->getModel();

    if ($controller->lastConfig !== ['ignore_request' => true]) {
        throw new \RuntimeException('Agegroups default model must ignore request state.');
    }

    $controller->modelUnavailable = true;

    foreach (['saveshort', 'import'] as $operation) {
        try {
            $controller->{$operation}();
        } catch (\RuntimeException $error) {
            if ($error->getCode() === 500
                && str_contains($error->getMessage(), 'age-group')
                && str_contains($error->getMessage(), 'model could not be loaded')) {
                continue;
            }

            throw $error;
        }

        throw new \RuntimeException('Missing age-group model was not rejected for ' . $operation);
    }

    echo "Administrator Agegroups MVC options and missing-model guards OK\n";
}
