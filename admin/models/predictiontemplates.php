<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Predictiontemplates list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiontemplatesModel;

if (!class_exists(PredictiontemplatesModel::class)) {
    foreach ([
        __DIR__ . '/../src/Model/SportsManagementListModel.php',
        __DIR__ . '/../src/Model/PredictiontemplatesModel.php',
    ] as $nativeModel) {
        if (!is_file($nativeModel)) {
            throw new \RuntimeException('SportsManagement administrator model dependency is missing: ' . basename($nativeModel), 500);
        }

        require_once $nativeModel;
    }
}

if (!class_exists(PredictiontemplatesModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictiontemplates model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPredictionTemplates', false)) {
    class_alias(PredictiontemplatesModel::class, 'sportsmanagementModelPredictionTemplates');
}
