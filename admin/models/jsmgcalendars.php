<?php
/** Legacy compatibility bridge for the native administrator Google calendars model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\JsmgcalendarsModel;

if (!class_exists(JsmgcalendarsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JsmgcalendarsModel.php';
}

if (!class_exists('sportsmanagementModeljsmGCalendars', false)) {
    class_alias(JsmgcalendarsModel::class, 'sportsmanagementModeljsmGCalendars');
}
