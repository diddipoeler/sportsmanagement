<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Matchplayer table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchplayerTable;

if (!class_exists(MatchplayerTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/MatchplayerTable.php';
}

if (!class_exists(MatchplayerTable::class)) {
    throw new \RuntimeException('SportsManagement native Matchplayer table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableMatchplayer', false)) {
    class_alias(MatchplayerTable::class, 'sportsmanagementTableMatchplayer');
}
