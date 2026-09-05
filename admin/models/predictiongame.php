<?php
/**
 * Legacy compatibility bridge for the native administrator Predictiongame form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiongameModel;

if (!class_exists(PredictiongameModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictiongameModel.php';
}

if (!class_exists('sportsmanagementModelPredictionGame', false)) {
    class_alias(PredictiongameModel::class, 'sportsmanagementModelPredictionGame');
}
