<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Table/TeamTable.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\TeamTable;

if (!class_exists(TeamTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/TeamTable.php';
}

if (!class_exists('sportsmanagementTableTeam', false)) {
    class_alias(TeamTable::class, 'sportsmanagementTableTeam');
}
