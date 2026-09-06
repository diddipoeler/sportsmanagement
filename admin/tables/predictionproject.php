<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 PredictionprojectTable.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
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
