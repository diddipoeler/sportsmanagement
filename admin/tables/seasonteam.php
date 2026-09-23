<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/** Legacy compatibility bridge for the native Joomla 5/6 season team table. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\SeasonteamTable;

if (!class_exists(SeasonteamTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SeasonteamTable.php';
}

if (!class_exists('sportsmanagementTableseasonteam', false)) {
    class_alias(SeasonteamTable::class, 'sportsmanagementTableseasonteam');
}
