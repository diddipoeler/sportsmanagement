<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Matchevent table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatcheventTable;

if (!class_exists(MatcheventTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/MatcheventTable.php';
}

if (!class_exists(MatcheventTable::class)) {
    throw new \RuntimeException('SportsManagement native Matchevent table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableMatchEvent', false)) {
    class_alias(MatcheventTable::class, 'sportsmanagementTableMatchEvent');
}
