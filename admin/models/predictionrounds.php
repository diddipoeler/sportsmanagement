<?php
/** Legacy compatibility bridge for the native administrator Predictionrounds list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictionroundsModel;

if (!class_exists(PredictionroundsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictionroundsModel.php';
}

if (!class_exists('sportsmanagementModelPredictionRounds', false)) {
    class_alias(PredictionroundsModel::class, 'sportsmanagementModelPredictionRounds');
}
