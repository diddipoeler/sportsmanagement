<?php
/** Legacy compatibility bridge for the native administrator Sportstypes controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\SportstypesController;

if (!class_exists(SportstypesController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportstypesController.php';
}

if (!class_exists('sportsmanagementControllersportstypes', false)) {
    class_alias(SportstypesController::class, 'sportsmanagementControllersportstypes');
}
