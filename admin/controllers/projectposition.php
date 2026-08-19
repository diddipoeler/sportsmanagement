<?php
/** Legacy compatibility bridge for the native administrator Projectposition controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\ProjectpositionController;

if (!class_exists(ProjectpositionController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/ProjectpositionController.php';
}

if (!class_exists('sportsmanagementControllerprojectposition', false)) {
    class_alias(ProjectpositionController::class, 'sportsmanagementControllerprojectposition');
}
