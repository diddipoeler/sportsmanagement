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
    $nativeModel = JPATH_SITE . '/components/com_sportsmanagement/src/Model/PredictionresultsModel.php';

    if (is_file($nativeModel)) {
        require_once $nativeModel;
    }
}

if (!class_exists(PredictionresultsModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictionresults model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPredictionResults', false)) {
    class_alias(PredictionresultsModel::class, 'sportsmanagementModelPredictionResults');
}
