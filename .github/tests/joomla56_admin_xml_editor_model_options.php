<?php
/**
 * Test Joomla 5/6 administrator XML editor MVC factory options.
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
        /**
         * Return the arguments received by the parent Joomla MVC factory.
         *
         * @param string $name Requested model.
         * @param string $prefix Model namespace prefix.
         * @param array $config Caller-supplied MVC options.
         * @return array<string,mixed> Factory arguments for test assertions.
         */
        public function getModel($name = '', $prefix = '', $config = [])
        {
            return ['name' => $name, 'prefix' => $prefix, 'config' => $config];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Controller/SmextxmleditorsController.php';

    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\SmextxmleditorsController();

    $defaults = $controller->getModel();
    $configured = $controller->getModel('CustomEditor', 'Administrator', [
        'ignore_request' => false,
        'ignore_cache' => true,
        'database' => 'injected',
    ]);

    if ($defaults['name'] !== 'Smextxmleditor'
        || $defaults['config'] !== ['ignore_request' => true]
        || $configured['name'] !== 'CustomEditor'
        || $configured['config']['ignore_request'] !== false
        || $configured['config']['ignore_cache'] !== true
        || $configured['config']['database'] !== 'injected') {
        throw new \RuntimeException('Joomla XML editor model options were lost.');
    }

    echo "Joomla 5/6 admin XML editor MVC options: OK\n";
}
