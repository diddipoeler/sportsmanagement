<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 prediction-entry controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\PredictionentryController;

if (!class_exists(PredictionentryController::class)) {
    $nativeController = JPATH_SITE . '/components/com_sportsmanagement/src/Controller/PredictionentryController.php';

    if (is_file($nativeController)) {
        require_once $nativeController;
    }
}

if (!class_exists(PredictionentryController::class)) {
    throw new \RuntimeException('SportsManagement native Predictionentry controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerPredictionEntry', false)) {
    class_alias(PredictionentryController::class, 'sportsmanagementControllerPredictionEntry');
}
