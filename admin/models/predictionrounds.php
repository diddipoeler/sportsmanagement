<?php
/**
 * Legacy compatibility bridge for the native administrator Predictionrounds list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictionroundsModel;

if (!class_exists(PredictionroundsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictionroundsModel.php';
}

if (!class_exists('sportsmanagementModelPredictionRounds', false)) {
    class_alias(PredictionroundsModel::class, 'sportsmanagementModelPredictionRounds');
}
