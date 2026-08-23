<?php
/** Legacy compatibility bridge for the native XML import controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlxmlimportController;

if (!class_exists(JlxmlimportController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlxmlimportController.php';
}

if (!class_exists('sportsmanagementControllerJLXMLImport', false)) {
    class_alias(JlxmlimportController::class, 'sportsmanagementControllerJLXMLImport');
}
