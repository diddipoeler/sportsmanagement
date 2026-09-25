<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Predictionrounds controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\PredictionroundsController;

if (!class_exists(PredictionroundsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/PredictionroundsController.php';
}

if (!class_exists(PredictionroundsController::class)) {
    throw new \RuntimeException('SportsManagement native Predictionrounds controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerPredictionRounds', false)) {
    class_alias(PredictionroundsController::class, 'sportsmanagementControllerPredictionRounds');
}
