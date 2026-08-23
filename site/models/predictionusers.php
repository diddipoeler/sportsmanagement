<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction-users model. */
\defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionusersModel;

if (!class_exists(PredictionusersModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/PredictionusersModel.php';
}

if (!class_exists('sportsmanagementModelPredictionUsers', false)) {
    class_alias(PredictionusersModel::class, 'sportsmanagementModelPredictionUsers');
}
