<?php
/** Legacy compatibility bridge for the native PredictionprojectTable. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictionprojectTable;

if (!class_exists(PredictionprojectTable::class)) {
    $tableFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PredictionprojectTable.php';

    if (is_file($tableFile)) {
        require_once $tableFile;
    }
}

if (class_exists(PredictionprojectTable::class) && !class_exists('sportsmanagementTablePredictionProject', false)) {
    class_alias(PredictionprojectTable::class, 'sportsmanagementTablePredictionProject');
}
