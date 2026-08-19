<?php
/** Legacy compatibility bridge for the native administrator Predictiongroup controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\PredictiongroupController;

if (!class_exists(PredictiongroupController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/PredictiongroupController.php';
}

if (!class_exists('sportsmanagementControllerpredictiongroup', false)) {
    class_alias(PredictiongroupController::class, 'sportsmanagementControllerpredictiongroup');
}
