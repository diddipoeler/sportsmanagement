<?php
/** Legacy compatibility bridge for the native administrator Predictiontemplate model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiontemplateModel;

if (!class_exists(PredictiontemplateModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictiontemplateModel.php';
}

if (!class_exists('sportsmanagementModelPredictionTemplate', false)) {
    class_alias(PredictiontemplateModel::class, 'sportsmanagementModelPredictionTemplate');
}
