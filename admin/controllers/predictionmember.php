<?php
/** Legacy compatibility bridge for the native prediction member controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\PredictionmemberController;

if (!class_exists(PredictionmemberController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/PredictionmemberController.php';
}

if (!class_exists('sportsmanagementControllerpredictionmember', false)) {
    class_alias(PredictionmemberController::class, 'sportsmanagementControllerpredictionmember');
}
