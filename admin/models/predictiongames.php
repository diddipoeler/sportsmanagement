<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Predictiongames list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiongamesModel;

if (!class_exists(PredictiongamesModel::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictiongamesModel.php',
    ] as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(PredictiongamesModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictiongames model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPredictionGames', false)) {
    class_alias(PredictiongamesModel::class, 'sportsmanagementModelPredictionGames');
}
