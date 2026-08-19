<?php
/** Legacy compatibility bridge for the native administrator Predictiontemplates list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiontemplatesModel;

if (!class_exists(PredictiontemplatesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictiontemplatesModel.php';
}

if (!class_exists('sportsmanagementModelPredictionTemplates', false)) {
    class_alias(PredictiontemplatesModel::class, 'sportsmanagementModelPredictionTemplates');
}
