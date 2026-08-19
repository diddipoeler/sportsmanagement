<?php
/** Legacy compatibility bridge for the native administrator Trainingdata model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\TrainingdataModel;

if (!class_exists(TrainingdataModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TrainingdataModel.php';
}

if (!class_exists('sportsmanagementModeltrainingdata', false)) {
    class_alias(TrainingdataModel::class, 'sportsmanagementModeltrainingdata');
}
