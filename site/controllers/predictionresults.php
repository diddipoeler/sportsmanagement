<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction results controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\PredictionresultsController;

if (!class_exists(PredictionresultsController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/PredictionresultsController.php';
}

if (!class_exists('sportsmanagementControllerPredictionResults', false)) {
    class_alias(PredictionresultsController::class, 'sportsmanagementControllerPredictionResults');
}
