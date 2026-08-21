<?php
/** Legacy compatibility bridge for the native Matchplayer table. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchplayerTable;
use Diddipoeler\Component\SportsManagement\Administrator\Table\SportsManagementTable;

if (!class_exists(SportsManagementTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
}

if (!class_exists(MatchplayerTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/MatchplayerTable.php';
}

if (!class_exists('sportsmanagementTableMatchplayer', false)) {
    class_alias(MatchplayerTable::class, 'sportsmanagementTableMatchplayer');
}
