<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 prediction member model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictionmemberModel;

if (!class_exists(PredictionmemberModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictionmemberModel.php';
}

if (!class_exists(PredictionmemberModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictionmember model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelpredictionmember', false)) {
    class_alias(PredictionmemberModel::class, 'sportsmanagementModelpredictionmember');
}
