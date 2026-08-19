<?php
/** Legacy compatibility bridge for the native administrator project controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\ProjectController;

if (!class_exists(ProjectController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/ProjectController.php';
}

if (!class_exists('sportsmanagementControllerproject', false)) {
    class_alias(ProjectController::class, 'sportsmanagementControllerproject');
}
