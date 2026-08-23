<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction-entry model. */
\defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionentryModel;

if (!class_exists(PredictionentryModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/PredictionentryModel.php';
}

if (!class_exists('sportsmanagementModelPredictionEntry', false)) {
    class_alias(PredictionentryModel::class, 'sportsmanagementModelPredictionEntry');
}
