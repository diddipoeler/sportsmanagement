<?php
/** Legacy compatibility bridge for the native project referee controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\ProjectrefereeController;

if (!class_exists(ProjectrefereeController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/ProjectrefereeController.php';
}

if (!class_exists('sportsmanagementControllerprojectreferee', false)) {
    class_alias(ProjectrefereeController::class, 'sportsmanagementControllerprojectreferee');
}
