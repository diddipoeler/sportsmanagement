<?php
/** Legacy compatibility bridge for the native DBB import controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextdbbimportController;

if (!class_exists(JlextdbbimportController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlextdbbimportController.php';
}

if (!class_exists('sportsmanagementControllerjlextdbbimport', false)) {
    class_alias(JlextdbbimportController::class, 'sportsmanagementControllerjlextdbbimport');
}
