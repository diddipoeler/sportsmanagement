<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction-entry controller. */
\defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\PredictionentryController;

if (!class_exists(PredictionentryController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/PredictionentryController.php';
}

if (!class_exists('sportsmanagementControllerPredictionEntry', false)) {
    class_alias(PredictionentryController::class, 'sportsmanagementControllerPredictionEntry');
}
