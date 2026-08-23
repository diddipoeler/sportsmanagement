<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction ranking model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionrankingModel;

if (!class_exists(PredictionrankingModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/PredictionrankingModel.php';
}

if (!class_exists('sportsmanagementModelPredictionRanking', false)) {
    class_alias(PredictionrankingModel::class, 'sportsmanagementModelPredictionRanking');
}
