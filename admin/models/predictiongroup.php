<?php
/** Legacy compatibility bridge for the native administrator Predictiongroup form model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiongroupModel;

if (!class_exists(PredictiongroupModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictiongroupModel.php';
}

if (!class_exists('sportsmanagementModelpredictiongroup', false)) {
    class_alias(PredictiongroupModel::class, 'sportsmanagementModelpredictiongroup');
}
