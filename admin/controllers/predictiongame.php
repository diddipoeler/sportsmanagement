<?php
/** Legacy compatibility bridge for the native administrator Predictiongame controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\PredictiongameController;

if (!class_exists(PredictiongameController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/PredictiongameController.php';
}

if (!class_exists('sportsmanagementControllerpredictiongame', false)) {
    class_alias(PredictiongameController::class, 'sportsmanagementControllerpredictiongame');
}
