<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Table/LeagueTable.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\LeagueTable;

if (!class_exists(LeagueTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/LeagueTable.php';
}

if (!class_exists('sportsmanagementTableLeague', false)) {
    class_alias(LeagueTable::class, 'sportsmanagementTableLeague');
}
