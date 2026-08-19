<?php
/** Legacy compatibility bridge for the native administrator Databasetools controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\DatabasetoolsController;

if (!class_exists(DatabasetoolsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/DatabasetoolsController.php';
}

if (!class_exists('sportsmanagementControllerDatabaseTools', false)) {
    class_alias(DatabasetoolsController::class, 'sportsmanagementControllerDatabaseTools');
}
