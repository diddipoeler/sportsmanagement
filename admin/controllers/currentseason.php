<?php
/** Legacy compatibility bridge for the native administrator Currentseason controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\CurrentseasonController;

if (!class_exists(CurrentseasonController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/CurrentseasonController.php';
}

if (!class_exists('sportsmanagementControllercurrentseason', false)) {
    class_alias(CurrentseasonController::class, 'sportsmanagementControllercurrentseason');
}
