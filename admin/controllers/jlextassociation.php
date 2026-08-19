<?php
/** Legacy compatibility bridge for the native administrator association controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextassociationController;

if (!class_exists(JlextassociationController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlextassociationController.php';
}

if (!class_exists('sportsmanagementControllerjlextassociation', false)) {
    class_alias(JlextassociationController::class, 'sportsmanagementControllerjlextassociation');
}
