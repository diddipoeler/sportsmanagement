<?php
/** Legacy compatibility bridge for the native administrator Predictiontemplates controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\PredictiontemplatesController;

if (!class_exists(PredictiontemplatesController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/PredictiontemplatesController.php';
}

if (!class_exists('sportsmanagementControllerpredictiontemplates', false)) {
    class_alias(PredictiontemplatesController::class, 'sportsmanagementControllerpredictiontemplates');
}
