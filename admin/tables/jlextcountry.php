<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Jlextcountry table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\JlextcountryTable;

if (!class_exists(JlextcountryTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/JlextcountryTable.php';
}

if (!class_exists(JlextcountryTable::class)) {
    throw new \RuntimeException('SportsManagement native Jlextcountry table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTablejlextcountry', false)) {
    class_alias(JlextcountryTable::class, 'sportsmanagementTablejlextcountry');
}
