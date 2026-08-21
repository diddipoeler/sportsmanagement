<?php
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\TeamstaffTable;

if (!class_exists(TeamstaffTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/TeamstaffTable.php';
}

if (!class_exists('sportsmanagementTableTeamStaff', false)) {
    class_alias(TeamstaffTable::class, 'sportsmanagementTableTeamStaff');
}
