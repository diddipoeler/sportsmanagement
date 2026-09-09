<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Databasetool controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\DatabasetoolController;

if (!class_exists(DatabasetoolController::class)) {
    $nativeController = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/DatabasetoolController.php';

    if (is_file($nativeController)) {
        require_once $nativeController;
    }
}

if (!class_exists(DatabasetoolController::class)) {
    throw new \RuntimeException('SportsManagement native administrator Databasetool controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerDatabaseTool', false)) {
    class_alias(DatabasetoolController::class, 'sportsmanagementControllerDatabaseTool');
}
