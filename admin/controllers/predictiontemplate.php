<?php
/** Legacy compatibility bridge for the native administrator Predictiontemplate controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\PredictiontemplateController;

if (!class_exists(PredictiontemplateController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/PredictiontemplateController.php';
}

if (!class_exists('sportsmanagementControllerpredictiontemplate', false)) {
    class_alias(PredictiontemplateController::class, 'sportsmanagementControllerpredictiontemplate');
}
