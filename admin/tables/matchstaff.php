<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Matchstaff table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchstaffTable;

if (!class_exists(MatchstaffTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/MatchstaffTable.php';
}

if (!class_exists(MatchstaffTable::class)) {
    throw new \RuntimeException('SportsManagement native Matchstaff table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableMatchStaff', false)) {
    class_alias(MatchstaffTable::class, 'sportsmanagementTableMatchStaff');
}
