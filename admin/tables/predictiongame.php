<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 PredictiongameTable.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
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
