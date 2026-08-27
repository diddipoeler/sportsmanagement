<?php
/** Legacy compatibility bridge for the native Joomla 5/6 tree-to-node controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\TreetonodeController;

if (!class_exists(TreetonodeController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/TreetonodeController.php';
}

if (!class_exists('sportsmanagementControllerTreetonode', false)) {
    class_alias(TreetonodeController::class, 'sportsmanagementControllerTreetonode');
}
