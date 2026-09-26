<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Seasonteam table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\SeasonteamTable;

if (!class_exists(SeasonteamTable::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SeasonteamTable.php',
    ] as $nativeTable) {
        if (is_file($nativeTable)) {
            require_once $nativeTable;
        }
    }
}

if (!class_exists(SeasonteamTable::class)) {
    throw new \RuntimeException('SportsManagement native Seasonteam table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableseasonteam', false)) {
    class_alias(SeasonteamTable::class, 'sportsmanagementTableseasonteam');
}
