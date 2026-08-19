<?php
/** Legacy compatibility bridge for the native administrator Eventtypes controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\EventtypesController;

if (!class_exists(EventtypesController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/EventtypesController.php';
}

if (!class_exists('sportsmanagementControllereventtypes', false)) {
    class_alias(EventtypesController::class, 'sportsmanagementControllereventtypes');
}
