<?php
/** Legacy compatibility bridge for the native administrator individual-sport controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextindividualsportController;

if (!class_exists(JlextindividualsportController::class)) {
    require_once JPATH_COMPONENT_ADMINISTRATOR . '/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_COMPONENT_ADMINISTRATOR . '/src/Controller/JlextindividualsportController.php';
}

if (!class_exists('sportsmanagementControllerjlextindividualsport', false)) {
    class_alias(JlextindividualsportController::class, 'sportsmanagementControllerjlextindividualsport');
}
