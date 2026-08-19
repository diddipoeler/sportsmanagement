<?php
/** Legacy compatibility bridge for the native administrator Playground controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\PlaygroundController;

if (!class_exists(PlaygroundController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/PlaygroundController.php';
}

if (!class_exists('sportsmanagementControllerplayground', false)) {
    class_alias(PlaygroundController::class, 'sportsmanagementControllerplayground');
}
