<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Predictionproject table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictionprojectTable;

if (!class_exists(PredictionprojectTable::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PredictionprojectTable.php',
    ] as $nativeTable) {
        if (is_file($nativeTable)) {
            require_once $nativeTable;
        }
    }
}

if (!class_exists(PredictionprojectTable::class)) {
    throw new \RuntimeException('SportsManagement native Predictionproject table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTablePredictionProject', false)) {
    class_alias(PredictionprojectTable::class, 'sportsmanagementTablePredictionProject');
}
