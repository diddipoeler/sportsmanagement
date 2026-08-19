<?php
/** Legacy compatibility bridge for the native administrator Predictionproject model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictionprojectModel;

if (!class_exists(PredictionprojectModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictionprojectModel.php';
}

if (!class_exists('sportsmanagementModelpredictionproject', false)) {
    class_alias(PredictionprojectModel::class, 'sportsmanagementModelpredictionproject');
}
