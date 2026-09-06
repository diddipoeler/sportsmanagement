<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 PredictionresultTable.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictionresultTable;

if (!class_exists(PredictionresultTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PredictionresultTable.php';
}

if (!class_exists('sportsmanagementTablePredictionResult', false)) {
    class_alias(PredictionresultTable::class, 'sportsmanagementTablePredictionResult');
}
