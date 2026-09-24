<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Trainingdata model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TrainingdataModel;

if (!class_exists(TrainingdataModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TrainingdataModel.php';
}

if (!class_exists(TrainingdataModel::class)) {
    throw new \RuntimeException('SportsManagement native Trainingdata model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeltrainingdata', false)) {
    class_alias(TrainingdataModel::class, 'sportsmanagementModeltrainingdata');
}
