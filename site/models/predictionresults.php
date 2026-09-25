<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Predictionresults model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionresultsModel;

if (!class_exists(PredictionresultsModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementPredictionModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementPredictionReadModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/PredictionresultsModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(PredictionresultsModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictionresults model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPredictionResults', false)) {
    class_alias(PredictionresultsModel::class, 'sportsmanagementModelPredictionResults');
}
