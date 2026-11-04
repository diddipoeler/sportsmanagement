<?php
/**
 * Test native event-types controller configuration preservation.
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

        /**
         * Record the Joomla factory arguments without requiring Joomla boot.
         *
         * @param string $name Requested model.
         * @param string $prefix MVC namespace prefix.
         * @param array $config Options to forward to the model.
         * @return object Simulated factory result.
         */
        public function getModel($name = '', $prefix = '', $config = [])
        {
            $this->lastConfig = $config;

            return (object) ['name' => $name, 'prefix' => $prefix];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Controller/EventtypesController.php';

    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\EventtypesController();
    $model = $controller->getModel('Eventtype', 'Administrator', [
        'ignore_request' => false,
        'custom_option' => ['keep' => true],
    ]);

    if ($model->name !== 'Eventtype' || $model->prefix !== 'Administrator'
        || $controller->lastConfig !== [
            'ignore_request' => true,
            'custom_option' => ['keep' => true],
        ]) {
        throw new \RuntimeException('Eventtypes controller discarded MVC model options.');
    }

    $controller->getModel();

    if ($controller->lastConfig !== ['ignore_request' => true]) {
        throw new \RuntimeException('Eventtypes default model must ignore request state.');
    }

    echo "Eventtypes MVC model options OK\n";
}
