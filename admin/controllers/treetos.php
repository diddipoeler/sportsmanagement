<?php
/** Legacy compatibility bridge for the native administrator treetos controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\TreetosController;

if (!class_exists(TreetosController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/TreetosController.php';
}

if (!class_exists('sportsmanagementControllertreetos', false)) {
    class_alias(TreetosController::class, 'sportsmanagementControllertreetos');
}
