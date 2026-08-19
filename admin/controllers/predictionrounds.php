<?php
/** Legacy compatibility bridge for the native administrator Predictionrounds controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\PredictionroundsController;

if (!class_exists(PredictionroundsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/PredictionroundsController.php';
}

if (!class_exists('sportsmanagementControllerPredictionRounds', false)) {
    class_alias(PredictionroundsController::class, 'sportsmanagementControllerPredictionRounds');
}
