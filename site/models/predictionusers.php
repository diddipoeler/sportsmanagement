<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Predictionusers model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionusersModel;

if (!class_exists(PredictionusersModel::class)) {
    $nativeModel = JPATH_SITE . '/components/com_sportsmanagement/src/Model/PredictionusersModel.php';

    if (is_file($nativeModel)) {
        require_once $nativeModel;
    }
}

if (!class_exists(PredictionusersModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictionusers model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPredictionUsers', false)) {
    class_alias(PredictionusersModel::class, 'sportsmanagementModelPredictionUsers');
}
