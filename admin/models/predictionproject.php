<?php
/**
 * Legacy compatibility bridge for the native administrator Predictionproject model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictionprojectModel;

if (!class_exists(PredictionprojectModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictionprojectModel.php';
}

if (!class_exists('sportsmanagementModelpredictionproject', false)) {
    class_alias(PredictionprojectModel::class, 'sportsmanagementModelpredictionproject');
}
