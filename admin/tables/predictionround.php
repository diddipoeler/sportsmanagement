<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Predictionround table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictionroundTable;

if (!class_exists(PredictionroundTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PredictionroundTable.php';
}

if (!class_exists(PredictionroundTable::class)) {
    throw new \RuntimeException('SportsManagement native Predictionround table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTablePredictionRound', false)) {
    class_alias(PredictionroundTable::class, 'sportsmanagementTablePredictionRound');
}
