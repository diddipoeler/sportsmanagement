<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Projectteam table.
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

if (!class_exists(ProjectteamTable::class)) {
    throw new \RuntimeException('SportsManagement native Projectteam table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableProjectteam', false)) {
    class_alias(ProjectteamTable::class, 'sportsmanagementTableProjectteam');
}
