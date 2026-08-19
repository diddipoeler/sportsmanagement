<?php
/** Legacy compatibility bridge for the native administrator Databasetool controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\DatabasetoolController;

if (!class_exists(DatabasetoolController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/DatabasetoolController.php';
}

if (!class_exists('sportsmanagementControllerDatabaseTool', false)) {
    class_alias(DatabasetoolController::class, 'sportsmanagementControllerDatabaseTool');
}
