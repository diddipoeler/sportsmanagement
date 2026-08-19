<?php
/** Legacy compatibility bridge for the native administrator Projectteam controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\ProjectteamController;

if (!class_exists(ProjectteamController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/ProjectteamController.php';
}

if (!class_exists('sportsmanagementControllerprojectteam', false)) {
    class_alias(ProjectteamController::class, 'sportsmanagementControllerprojectteam');
}
