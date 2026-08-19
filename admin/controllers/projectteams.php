<?php
/** Legacy compatibility bridge for the native administrator Projectteams controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\ProjectteamsController;

if (!class_exists(ProjectteamsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/ProjectteamsController.php';
}

if (!class_exists('sportsmanagementControllerprojectteams', false)) {
    class_alias(ProjectteamsController::class, 'sportsmanagementControllerprojectteams');
}
