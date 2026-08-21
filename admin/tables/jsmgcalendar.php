<?php
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\JsmgcalendarTable;

if (!class_exists(JsmgcalendarTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/JsmgcalendarTable.php';
}

if (!class_exists('sportsmanagementTablejsmGCalendar', false)) {
    class_alias(JsmgcalendarTable::class, 'sportsmanagementTablejsmGCalendar');
}
