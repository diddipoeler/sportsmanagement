<?php
/** Legacy compatibility bridge for the native administrator federations controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextfederationsController;

if (!class_exists(JlextfederationsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlextfederationsController.php';
}

if (!class_exists('sportsmanagementControllerjlextfederations', false)) {
    class_alias(JlextfederationsController::class, 'sportsmanagementControllerjlextfederations');
}
