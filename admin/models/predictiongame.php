<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Predictiongame form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiongameModel;

if (!class_exists(PredictiongameModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictiongameModel.php';
}

if (!class_exists(PredictiongameModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictiongame model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPredictionGame', false)) {
    class_alias(PredictiongameModel::class, 'sportsmanagementModelPredictionGame');
}
