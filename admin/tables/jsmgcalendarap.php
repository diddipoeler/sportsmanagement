<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

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
