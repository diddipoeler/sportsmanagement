<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Predictionmembers controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\PredictionmembersController;

if (!class_exists(PredictionmembersController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/PredictionmembersController.php';
}

if (!class_exists('sportsmanagementControllerpredictionmembers', false)) {
    class_alias(PredictionmembersController::class, 'sportsmanagementControllerpredictionmembers');
}
