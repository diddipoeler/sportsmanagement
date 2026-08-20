<?php
/** Legacy compatibility bridge for the native administrator players controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\PlayersController;

if (!class_exists(PlayersController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/PlayersController.php';
}

if (!class_exists('sportsmanagementControllerplayers', false)) {
    class_alias(PlayersController::class, 'sportsmanagementControllerplayers');
}
