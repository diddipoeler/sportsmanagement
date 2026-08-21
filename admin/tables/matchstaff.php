<?php
/** Legacy compatibility bridge for the native Matchstaff table. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchstaffTable;
use Diddipoeler\Component\SportsManagement\Administrator\Table\SportsManagementTable;

if (!class_exists(SportsManagementTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
}

if (!class_exists(MatchstaffTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/MatchstaffTable.php';
}

if (!class_exists('sportsmanagementTableMatchStaff', false)) {
    class_alias(MatchstaffTable::class, 'sportsmanagementTableMatchStaff');
}
