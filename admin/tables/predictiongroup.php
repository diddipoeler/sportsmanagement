<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Predictiongroup table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictiongroupTable;

if (!class_exists(PredictiongroupTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PredictiongroupTable.php';
}

if (!class_exists(PredictiongroupTable::class)) {
    throw new \RuntimeException('SportsManagement native Predictiongroup table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTablePredictionGroup', false)) {
    class_alias(PredictiongroupTable::class, 'sportsmanagementTablePredictionGroup');
}
