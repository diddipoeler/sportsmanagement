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
    $nativeModel = JPATH_SITE . '/components/com_sportsmanagement/src/Model/PredictionrankingModel.php';

    if (is_file($nativeModel)) {
        require_once $nativeModel;
    }
}

if (!class_exists(PredictionrankingModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictionranking model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPredictionRanking', false)) {
    class_alias(PredictionrankingModel::class, 'sportsmanagementModelPredictionRanking');
}
