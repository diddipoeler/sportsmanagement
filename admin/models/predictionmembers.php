<?php
/** Legacy compatibility bridge for the native prediction members list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictionmembersModel;

if (!class_exists(PredictionmembersModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictionmembersModel.php';
}

if (!class_exists('sportsmanagementModelPredictionMembers', false)) {
    class_alias(PredictionmembersModel::class, 'sportsmanagementModelPredictionMembers');
}
