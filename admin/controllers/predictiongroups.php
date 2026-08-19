<?php
/** Legacy compatibility bridge for the native administrator Predictiongroups controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\PredictiongroupsController;

if (!class_exists(PredictiongroupsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/PredictiongroupsController.php';
}

if (!class_exists('sportsmanagementControllerpredictiongroups', false)) {
    class_alias(PredictiongroupsController::class, 'sportsmanagementControllerpredictiongroups');
}
