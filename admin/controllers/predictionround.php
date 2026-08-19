<?php
/** Legacy compatibility bridge for the native administrator Predictionround controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\PredictionroundController;

if (!class_exists(PredictionroundController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/PredictionroundController.php';
}

if (!class_exists('sportsmanagementControllerPredictionRound', false)) {
    class_alias(PredictionroundController::class, 'sportsmanagementControllerPredictionRound');
}
