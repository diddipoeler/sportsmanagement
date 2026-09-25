<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Playground table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PlaygroundTable;

if (!class_exists(PlaygroundTable::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PlaygroundTable.php',
    ] as $nativeTable) {
        if (is_file($nativeTable)) {
            require_once $nativeTable;
        }
    }
}

if (!class_exists(PlaygroundTable::class)) {
    throw new \RuntimeException('SportsManagement native Playground table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTablePlayground', false)) {
    class_alias(PlaygroundTable::class, 'sportsmanagementTablePlayground');
}
