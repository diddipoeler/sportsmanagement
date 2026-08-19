<?php
/** Legacy compatibility bridge for the native administrator Leagues controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\LeaguesController;

if (!class_exists(LeaguesController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/LeaguesController.php';
}

if (!class_exists('sportsmanagementControllerleagues', false)) {
    class_alias(LeaguesController::class, 'sportsmanagementControllerleagues');
}
