<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 PredictionadminTable.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictionadminTable;

if (!class_exists(PredictionadminTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/PredictionadminTable.php';
}

if (!class_exists('sportsmanagementTablePredictionAdmin', false)) {
    class_alias(PredictionadminTable::class, 'sportsmanagementTablePredictionAdmin');
}
