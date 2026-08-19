<?php
/** Legacy compatibility bridge for the native administrator Predictiongames controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\PredictiongamesController;

if (!class_exists(PredictiongamesController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/PredictiongamesController.php';
}

if (!class_exists('sportsmanagementControllerpredictiongames', false)) {
    class_alias(PredictiongamesController::class, 'sportsmanagementControllerpredictiongames');
}
