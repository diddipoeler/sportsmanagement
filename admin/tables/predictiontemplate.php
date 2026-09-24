<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Predictiontemplate table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictiontemplateTable;

if (!class_exists(PredictiontemplateTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PredictiontemplateTable.php';
}

if (!class_exists(PredictiontemplateTable::class)) {
    throw new \RuntimeException('SportsManagement native Predictiontemplate table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTablePredictionTemplate', false)) {
    class_alias(PredictiontemplateTable::class, 'sportsmanagementTablePredictionTemplate');
}
