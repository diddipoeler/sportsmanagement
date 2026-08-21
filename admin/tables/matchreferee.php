<?php
/** Legacy compatibility bridge for the native Matchreferee table. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchrefereeTable;
use Diddipoeler\Component\SportsManagement\Administrator\Table\SportsManagementTable;

if (!class_exists(SportsManagementTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
}

if (!class_exists(MatchrefereeTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/MatchrefereeTable.php';
}

if (!class_exists('sportsmanagementTableMatchreferee', false)) {
    class_alias(MatchrefereeTable::class, 'sportsmanagementTableMatchreferee');
}
