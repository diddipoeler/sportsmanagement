<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction users controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\PredictionusersController;

if (!class_exists(PredictionusersController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/PredictionusersController.php';
}

if (!class_exists('sportsmanagementControllerPredictionUsers', false)) {
    class_alias(PredictionusersController::class, 'sportsmanagementControllerPredictionUsers');
}
