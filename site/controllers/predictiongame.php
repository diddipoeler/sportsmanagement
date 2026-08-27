<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction game controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\PredictiongameController;

if (!class_exists(PredictiongameController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/PredictiongameController.php';
}

if (!class_exists('sportsmanagementControllerPredictiongame', false)) {
    class_alias(PredictiongameController::class, 'sportsmanagementControllerPredictiongame');
}
