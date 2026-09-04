<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 prediction results controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\PredictionresultsController;

if (!class_exists(PredictionresultsController::class)) {
    $nativeController = JPATH_SITE . '/components/com_sportsmanagement/src/Controller/PredictionresultsController.php';

    if (is_file($nativeController)) {
        require_once $nativeController;
    }
}

if (!class_exists(PredictionresultsController::class)) {
    throw new \RuntimeException('SportsManagement native Predictionresults controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerPredictionResults', false)) {
    class_alias(PredictionresultsController::class, 'sportsmanagementControllerPredictionResults');
}
