<?php
/** Legacy compatibility bridge for the native administrator Google calendar import model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\JsmgcalendarimportModel;

if (!class_exists(JsmgcalendarimportModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JsmgcalendarimportModel.php';
}

if (!class_exists('sportsmanagementModeljsmgcalendarImport', false)) {
    class_alias(JsmgcalendarimportModel::class, 'sportsmanagementModeljsmgcalendarImport');
}
