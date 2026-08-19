<?php
/** Legacy compatibility bridge for the native administrator template controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\TemplateController;

if (!class_exists(TemplateController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/TemplateController.php';
}

if (!class_exists('sportsmanagementControllertemplate', false)) {
    class_alias(TemplateController::class, 'sportsmanagementControllertemplate');
}
