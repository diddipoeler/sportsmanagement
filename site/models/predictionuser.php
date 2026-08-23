<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction-user editor model. */
\defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionuserModel;

if (!class_exists(PredictionuserModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/PredictionuserModel.php';
}

if (!class_exists('sportsmanagementModelPredictionUser', false)) {
    class_alias(PredictionuserModel::class, 'sportsmanagementModelPredictionUser');
}
