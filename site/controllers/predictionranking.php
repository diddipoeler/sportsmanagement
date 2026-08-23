<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction ranking controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\PredictionrankingController;

if (!class_exists(PredictionrankingController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/PredictionrankingController.php';
}

if (!class_exists('sportsmanagementControllerPredictionRanking', false)) {
    class_alias(PredictionrankingController::class, 'sportsmanagementControllerPredictionRanking');
}
