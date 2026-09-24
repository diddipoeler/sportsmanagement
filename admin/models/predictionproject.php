<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Predictionproject model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictionprojectModel;

if (!class_exists(PredictionprojectModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictionprojectModel.php';
}

if (!class_exists(PredictionprojectModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictionproject model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelpredictionproject', false)) {
    class_alias(PredictionprojectModel::class, 'sportsmanagementModelpredictionproject');
}
