<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/** Legacy compatibility bridge for the native PredictionroundTable. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictionroundTable;

if (!class_exists(PredictionroundTable::class)) {
    $tableFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PredictionroundTable.php';

    if (is_file($tableFile)) {
        require_once $tableFile;
    }
}

if (class_exists(PredictionroundTable::class) && !class_exists('sportsmanagementTablePredictionRound', false)) {
    class_alias(PredictionroundTable::class, 'sportsmanagementTablePredictionRound');
}
