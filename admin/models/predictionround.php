<?php
/** Legacy compatibility bridge for the native administrator Predictionround model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictionroundModel;

if (!class_exists(PredictionroundModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictionroundModel.php';
}

if (!class_exists('sportsmanagementModelPredictionRound', false)) {
    class_alias(PredictionroundModel::class, 'sportsmanagementModelPredictionRound');
}
