<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Predictionproject controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\PredictionprojectController;

if (!class_exists(PredictionprojectController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/PredictionprojectController.php';
}

if (!class_exists('sportsmanagementControllerpredictionproject', false)) {
    class_alias(PredictionprojectController::class, 'sportsmanagementControllerpredictionproject');
}
