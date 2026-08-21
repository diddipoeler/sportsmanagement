<?php
/** Legacy compatibility bridge for the native PredictiongameTable. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictiongameTable;

if (!class_exists(PredictiongameTable::class)) {
    $tableFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PredictiongameTable.php';

    if (is_file($tableFile)) {
        require_once $tableFile;
    }
}

if (class_exists(PredictiongameTable::class) && !class_exists('sportsmanagementTablePredictionGame', false)) {
    class_alias(PredictiongameTable::class, 'sportsmanagementTablePredictionGame');
}
