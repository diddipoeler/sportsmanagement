<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Rosterposition table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\RosterpositionTable;

if (!class_exists(RosterpositionTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/RosterpositionTable.php';
}

if (!class_exists(RosterpositionTable::class)) {
    throw new \RuntimeException('SportsManagement native Rosterposition table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTablerosterposition', false)) {
    class_alias(RosterpositionTable::class, 'sportsmanagementTablerosterposition');
}
