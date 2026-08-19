<?php
/** Legacy compatibility bridge for the native administrator Google calendar model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\JsmgcalendarModel;

if (!class_exists(JsmgcalendarModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JsmgcalendarModel.php';
}

if (!class_exists('sportsmanagementModeljsmGCalendar', false)) {
    class_alias(JsmgcalendarModel::class, 'sportsmanagementModeljsmGCalendar');
}
