<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 prediction users controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\PredictionusersController;

if (!class_exists(PredictionusersController::class)) {
    $nativeController = JPATH_SITE . '/components/com_sportsmanagement/src/Controller/PredictionusersController.php';

    if (is_file($nativeController)) {
        require_once $nativeController;
    }
}

if (!class_exists(PredictionusersController::class)) {
    throw new \RuntimeException('SportsManagement native Predictionusers controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerPredictionUsers', false)) {
    class_alias(PredictionusersController::class, 'sportsmanagementControllerPredictionUsers');
}
