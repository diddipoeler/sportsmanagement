<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Predictionranking model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionrankingModel;

if (!class_exists(PredictionrankingModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementPredictionModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementPredictionReadModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/PredictionrankingModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(PredictionrankingModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictionranking model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPredictionRanking', false)) {
    class_alias(PredictionrankingModel::class, 'sportsmanagementModelPredictionRanking');
}
