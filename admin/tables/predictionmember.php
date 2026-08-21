<?php
/** Legacy compatibility bridge for the native PredictionmemberTable. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictionmemberTable;

if (!class_exists(PredictionmemberTable::class)) {
    $tableFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PredictionmemberTable.php';

    if (is_file($tableFile)) {
        require_once $tableFile;
    }
}

if (class_exists(PredictionmemberTable::class) && !class_exists('sportsmanagementTablePredictionMember', false)) {
    class_alias(PredictionmemberTable::class, 'sportsmanagementTablePredictionMember');
}
