<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 prediction ranking controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\PredictionrankingController;

if (!class_exists(PredictionrankingController::class)) {
    $nativeController = JPATH_SITE . '/components/com_sportsmanagement/src/Controller/PredictionrankingController.php';

    if (is_file($nativeController)) {
        require_once $nativeController;
    }
}

if (!class_exists(PredictionrankingController::class)) {
    throw new \RuntimeException('SportsManagement native Predictionranking controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerPredictionRanking', false)) {
    class_alias(PredictionrankingController::class, 'sportsmanagementControllerPredictionRanking');
}
