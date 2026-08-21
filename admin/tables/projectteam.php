<?php
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\ProjectteamTable;

if (!class_exists(ProjectteamTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/ProjectteamTable.php';
}

if (!class_exists('sportsmanagementTableProjectteam', false)) {
    class_alias(ProjectteamTable::class, 'sportsmanagementTableProjectteam');
}
