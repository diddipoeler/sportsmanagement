<?php
/** Legacy compatibility bridge for the native legacy SportsManagement table. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\LegacySportsmanagementTable;

if (!class_exists(LegacySportsmanagementTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/LegacySportsmanagementTable.php';
}

if (class_exists(LegacySportsmanagementTable::class) && !class_exists('sportsmanagementTablesportsmanagement', false)) {
    class_alias(LegacySportsmanagementTable::class, 'sportsmanagementTablesportsmanagement');
}
