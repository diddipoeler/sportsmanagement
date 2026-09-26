<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Projectposition table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\ProjectpositionTable;

if (!class_exists(ProjectpositionTable::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/ProjectpositionTable.php',
    ] as $nativeTable) {
        if (is_file($nativeTable)) {
            require_once $nativeTable;
        }
    }
}

if (!class_exists(ProjectpositionTable::class)) {
    throw new \RuntimeException('SportsManagement native Projectposition table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableProjectposition', false)) {
    class_alias(ProjectpositionTable::class, 'sportsmanagementTableProjectposition');
}
