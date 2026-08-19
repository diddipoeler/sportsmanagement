<?php
/** Legacy compatibility bridge for the native tournament-tree node controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\TreetonodeController;

if (!class_exists(TreetonodeController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/TreetonodeController.php';
}

if (!class_exists('sportsmanagementControllerTreetonode', false)) {
    class_alias(TreetonodeController::class, 'sportsmanagementControllerTreetonode');
}
