<?php
/** Legacy compatibility bridge for the native administrator treeto controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\TreetoController;

if (!class_exists(TreetoController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/TreetoController.php';
}

if (!class_exists('sportsmanagementControllerTreeto', false)) {
    class_alias(TreetoController::class, 'sportsmanagementControllerTreeto');
}
