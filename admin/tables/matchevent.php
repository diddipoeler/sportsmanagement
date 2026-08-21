<?php
/** Legacy compatibility bridge for the native Matchevent table. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatcheventTable;
use Diddipoeler\Component\SportsManagement\Administrator\Table\SportsManagementTable;

if (!class_exists(SportsManagementTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
}

if (!class_exists(MatcheventTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/MatcheventTable.php';
}

if (!class_exists('sportsmanagementTableMatchEvent', false)) {
    class_alias(MatcheventTable::class, 'sportsmanagementTableMatchEvent');
}
