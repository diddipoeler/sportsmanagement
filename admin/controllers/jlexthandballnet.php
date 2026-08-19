<?php
/** Legacy compatibility bridge for the native handball.net import controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlexthandballnetController;

if (!class_exists(JlexthandballnetController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlexthandballnetController.php';
}

if (!class_exists('sportsmanagementControllerjlexthandballnet', false)) {
    class_alias(JlexthandballnetController::class, 'sportsmanagementControllerjlexthandballnet');
}
