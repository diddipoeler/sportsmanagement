<?php
/** SportsManagement legacy compatibility bridge for the match table. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchTable;

if (!class_exists(MatchTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/MatchTable.php';
}

if (!class_exists('sportsmanagementTableMatch', false)) {
    class_alias(MatchTable::class, 'sportsmanagementTableMatch');
}
