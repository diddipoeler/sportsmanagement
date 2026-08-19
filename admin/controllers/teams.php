<?php
/** Legacy compatibility bridge for the native administrator Teams controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\TeamsController;

if (!class_exists(TeamsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/TeamsController.php';
}

if (!class_exists('sportsmanagementControllerteams', false)) {
    class_alias(TeamsController::class, 'sportsmanagementControllerteams');
}
