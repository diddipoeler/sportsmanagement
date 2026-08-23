<?php
/** Legacy compatibility bridge for the native Joomla 5/6 season team table. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\SeasonteamTable;

if (!class_exists(SeasonteamTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SeasonteamTable.php';
}

if (!class_exists('sportsmanagementTableseasonteam', false)) {
    class_alias(SeasonteamTable::class, 'sportsmanagementTableseasonteam');
}
