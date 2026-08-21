<?php
/** Legacy compatibility bridge for the native PredictiongroupTable. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictiongroupTable;

if (!class_exists(PredictiongroupTable::class)) {
    $tableFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PredictiongroupTable.php';

    if (is_file($tableFile)) {
        require_once $tableFile;
    }
}

if (class_exists(PredictiongroupTable::class) && !class_exists('sportsmanagementTablePredictionGroup', false)) {
    class_alias(PredictiongroupTable::class, 'sportsmanagementTablePredictionGroup');
}
