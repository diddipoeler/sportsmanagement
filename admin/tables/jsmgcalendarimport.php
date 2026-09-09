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

if (!class_exists(JsmgcalendarTable::class)) {
    throw new \RuntimeException('SportsManagement native JSM Google Calendar table could not be loaded.', 500);
}

if (!class_exists('GCalendarTableImport', false)) {
    class_alias(JsmgcalendarTable::class, 'GCalendarTableImport');
}
