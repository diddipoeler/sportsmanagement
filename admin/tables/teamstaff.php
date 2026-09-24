<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Teamstaff table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\TeamstaffTable;

if (!class_exists(TeamstaffTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/TeamstaffTable.php';
}

if (!class_exists(TeamstaffTable::class)) {
    throw new \RuntimeException('SportsManagement native Teamstaff table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableTeamStaff', false)) {
    class_alias(TeamstaffTable::class, 'sportsmanagementTableTeamStaff');
}
