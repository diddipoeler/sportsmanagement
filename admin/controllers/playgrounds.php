<?php
/** Legacy compatibility bridge for the native administrator Playgrounds controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\PlaygroundsController;

if (!class_exists(PlaygroundsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/PlaygroundsController.php';
}

if (!class_exists('sportsmanagementControllerPlaygrounds', false)) {
    class_alias(PlaygroundsController::class, 'sportsmanagementControllerPlaygrounds');
}
