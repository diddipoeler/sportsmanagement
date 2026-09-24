<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 LegacySportsmanagement table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\LegacySportsmanagementTable;

if (!class_exists(LegacySportsmanagementTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/LegacySportsmanagementTable.php';
}

if (!class_exists(LegacySportsmanagementTable::class)) {
    throw new \RuntimeException('SportsManagement native LegacySportsmanagement table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTablesportsmanagement', false)) {
    class_alias(LegacySportsmanagementTable::class, 'sportsmanagementTablesportsmanagement');
}
