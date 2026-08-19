<?php
/** Legacy compatibility bridge for the native administrator projects controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\ProjectsController;

if (!class_exists(ProjectsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/ProjectsController.php';
}

if (!class_exists('sportsmanagementControllerprojects', false)) {
    class_alias(ProjectsController::class, 'sportsmanagementControllerprojects');
}
