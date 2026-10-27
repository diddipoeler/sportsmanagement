<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Predictionrounds list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictionroundsModel;

if (!class_exists(PredictionroundsModel::class)) {
    $nativeFiles = [
        __DIR__ . '/../src/Model/SportsManagementListModel.php',
        __DIR__ . '/../src/Model/PredictionroundsModel.php',
    ];

    foreach ($nativeFiles as $nativeFile) {
        if (!is_file($nativeFile)) {
            throw new \RuntimeException('SportsManagement administrator model dependency is missing: ' . basename($nativeFile), 500);
        }

        require_once $nativeFile;
    }
}

if (!class_exists(PredictionroundsModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictionrounds model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPredictionRounds', false)) {
    class_alias(PredictionroundsModel::class, 'sportsmanagementModelPredictionRounds');
}
