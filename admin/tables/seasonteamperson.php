<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Seasonteamperson table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\SeasonteampersonTable;

if (!class_exists(SeasonteampersonTable::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SeasonteampersonTable.php',
    ] as $nativeTable) {
        if (is_file($nativeTable)) {
            require_once $nativeTable;
        }
    }
}

if (!class_exists(SeasonteampersonTable::class)) {
    throw new \RuntimeException('SportsManagement native Seasonteamperson table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableseasonteamperson', false)) {
    class_alias(SeasonteampersonTable::class, 'sportsmanagementTableseasonteamperson');
}
