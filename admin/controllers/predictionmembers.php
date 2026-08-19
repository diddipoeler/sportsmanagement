<?php
/** Legacy compatibility bridge for the native prediction members controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\PredictionmembersController;

if (!class_exists(PredictionmembersController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/PredictionmembersController.php';
}

if (!class_exists('sportsmanagementControllerpredictionmembers', false)) {
    class_alias(PredictionmembersController::class, 'sportsmanagementControllerpredictionmembers');
}
