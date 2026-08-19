<?php
/** Legacy compatibility bridge for the native administrator Predictiongroups list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiongroupsModel;

if (!class_exists(PredictiongroupsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictiongroupsModel.php';
}

if (!class_exists('sportsmanagementModelpredictiongroups', false)) {
    class_alias(PredictiongroupsModel::class, 'sportsmanagementModelpredictiongroups');
}
