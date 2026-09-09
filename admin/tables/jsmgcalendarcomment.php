<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 GCalendar comment table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\JsmgcalendarcommentTable;

if (!class_exists(JsmgcalendarcommentTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/JsmgcalendarcommentTable.php';
}

if (class_exists(JsmgcalendarcommentTable::class) && !class_exists('sportsmanagementTablejsmgcalendarComment', false)) {
    class_alias(JsmgcalendarcommentTable::class, 'sportsmanagementTablejsmgcalendarComment');
}
