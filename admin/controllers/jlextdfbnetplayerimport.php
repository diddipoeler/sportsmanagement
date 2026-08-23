<?php
/** Legacy compatibility bridge for the native DFB.net import controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextdfbnetplayerimportController;

if (!class_exists(JlextdfbnetplayerimportController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlextdfbnetplayerimportController.php';
}

if (!class_exists('sportsmanagementControllerjlextdfbnetplayerimport', false)) {
    class_alias(JlextdfbnetplayerimportController::class, 'sportsmanagementControllerjlextdfbnetplayerimport');
}
