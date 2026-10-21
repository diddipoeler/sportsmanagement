<?php
/**
 * Administrator Playgrounds MVC model configuration regression.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementAdminController {
        public array $lastConfig = [];

        public function getModel($name = '', $prefix = '', $config = [])
        {
            $this->lastConfig = $config;

            return (object) ['name' => $name, 'prefix' => $prefix];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Controller/PlaygroundsController.php';

    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\PlaygroundsController();

    $result = $controller->getModel('Playground', 'Administrator', [
        'ignore_request' => false,
        'custom_option' => ['keep' => true],
    ]);

    if ($result->name !== 'Playground' || $result->prefix !== 'Administrator'
        || $controller->lastConfig !== [
            'ignore_request' => true,
            'custom_option' => ['keep' => true],
        ]) {
        throw new \RuntimeException('Playgrounds controller lost caller MVC configuration.');
    }

    $controller->getModel();
    if ($controller->lastConfig !== ['ignore_request' => true]) {
        throw new \RuntimeException('Playgrounds default model must ignore request state.');
    }

    echo "Administrator Playgrounds MVC options OK\n";
}
