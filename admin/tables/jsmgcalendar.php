<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 JSM Google Calendar table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\JsmgcalendarTable;

if (!class_exists(JsmgcalendarTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/JsmgcalendarTable.php';
}

if (!class_exists('sportsmanagementTablejsmGCalendar', false)) {
    class_alias(JsmgcalendarTable::class, 'sportsmanagementTablejsmGCalendar');
}
