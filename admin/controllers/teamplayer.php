<?php
/** Legacy compatibility bridge for the native administrator team player controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\TeamplayerController;

if (!class_exists(TeamplayerController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/TeamplayerController.php';
}

if (!class_exists('sportsmanagementControllerteamplayer', false)) {
    class_alias(TeamplayerController::class, 'sportsmanagementControllerteamplayer');
}
