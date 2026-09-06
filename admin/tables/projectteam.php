<?php
/**
 * SportsManagement legacy compatibility bridge for the native Projectteam table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\ProjectteamTable;

if (!class_exists(ProjectteamTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/ProjectteamTable.php';
}

if (!class_exists('sportsmanagementTableProjectteam', false)) {
    class_alias(ProjectteamTable::class, 'sportsmanagementTableProjectteam');
}
