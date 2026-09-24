<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Trainingdata table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\TrainingdataTable;

if (!class_exists(TrainingdataTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/TrainingdataTable.php';
}

if (!class_exists(TrainingdataTable::class)) {
    throw new \RuntimeException('SportsManagement native Trainingdata table could not be loaded.', 500);
}

if (!class_exists('sportsmanagementTableTrainingData', false)) {
    class_alias(TrainingdataTable::class, 'sportsmanagementTableTrainingData');
}
