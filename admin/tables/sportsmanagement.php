<?php
/** Legacy compatibility bridge for the native SportsmanagementTable. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\SportsmanagementTable;

if (!class_exists(SportsmanagementTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsmanagementTable.php';
}

if (class_exists(SportsmanagementTable::class) && !class_exists('sportsmanagementTablesportsmanagement', false)) {
    class_alias(SportsmanagementTable::class, 'sportsmanagementTablesportsmanagement');
}
