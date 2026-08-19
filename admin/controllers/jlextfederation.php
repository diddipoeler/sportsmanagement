<?php
/** Legacy compatibility bridge for the native administrator federation controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextfederationController;

if (!class_exists(JlextfederationController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlextfederationController.php';
}

if (!class_exists('sportsmanagementControllerjlextfederation', false)) {
    class_alias(JlextfederationController::class, 'sportsmanagementControllerjlextfederation');
}
