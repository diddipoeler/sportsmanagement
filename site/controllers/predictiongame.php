<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 prediction-game controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\PredictiongameController;

if (!class_exists(PredictiongameController::class)) {
    $nativeController = JPATH_SITE . '/components/com_sportsmanagement/src/Controller/PredictiongameController.php';

    if (is_file($nativeController)) {
        require_once $nativeController;
    }
}

if (!class_exists(PredictiongameController::class)) {
    throw new \RuntimeException('SportsManagement native Predictiongame controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerPredictiongame', false)) {
    class_alias(PredictiongameController::class, 'sportsmanagementControllerPredictiongame');
}
