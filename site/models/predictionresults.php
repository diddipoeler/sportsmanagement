<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction results model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionresultsModel;

if (!class_exists(PredictionresultsModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/PredictionresultsModel.php';
}

if (!class_exists('sportsmanagementModelPredictionResults', false)) {
    class_alias(PredictionresultsModel::class, 'sportsmanagementModelPredictionResults');
}
