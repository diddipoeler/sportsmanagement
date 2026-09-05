<?php
/**
 * Legacy compatibility bridge for the native administrator Predictiongames list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiongamesModel;

if (!class_exists(PredictiongamesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictiongamesModel.php';
}

if (!class_exists('sportsmanagementModelPredictionGames', false)) {
    class_alias(PredictiongamesModel::class, 'sportsmanagementModelPredictionGames');
}
