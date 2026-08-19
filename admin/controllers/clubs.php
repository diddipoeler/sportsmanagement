<?php
/** Legacy compatibility bridge for the native administrator Clubs controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\ClubsController;

if (!class_exists(ClubsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/ClubsController.php';
}

if (!class_exists('sportsmanagementControllerclubs', false)) {
    class_alias(ClubsController::class, 'sportsmanagementControllerclubs');
}
