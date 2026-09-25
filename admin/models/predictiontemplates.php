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
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictiontemplatesModel.php',
    ] as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(PredictiontemplatesModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictiontemplates model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPredictionTemplates', false)) {
    class_alias(PredictiontemplatesModel::class, 'sportsmanagementModelPredictionTemplates');
}
