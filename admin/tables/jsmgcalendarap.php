<?php
/** Legacy compatibility bridge for the native JsmgcalendarapTable. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\JsmgcalendarapTable;

if (!class_exists(JsmgcalendarapTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/JsmgcalendarapTable.php';
}

if (class_exists(JsmgcalendarapTable::class) && !class_exists('sportsmanagementTablejsmGCalendarAP', false)) {
    class_alias(JsmgcalendarapTable::class, 'sportsmanagementTablejsmGCalendarAP');
}
