<?php
/** Legacy compatibility bridge for the native LMO import controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextlmoimportsController;

if (!class_exists(JlextlmoimportsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlextlmoimportsController.php';
}

if (!class_exists('sportsmanagementControllerjlextlmoimports', false)) {
    class_alias(JlextlmoimportsController::class, 'sportsmanagementControllerjlextlmoimports');
}
