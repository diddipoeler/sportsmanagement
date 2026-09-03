<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Trainingsdata model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\TrainingsdataModel;

if (!class_exists(TrainingsdataModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TrainingsdataModel.php';
}

if (!class_exists('sportsmanagementModeltrainingsdata', false)) {
    class_alias(TrainingsdataModel::class, 'sportsmanagementModeltrainingsdata');
}
