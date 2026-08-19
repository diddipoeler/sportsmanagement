<?php
/** Legacy compatibility bridge for the native DFB-key import controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextdfbkeyimportController;

if (!class_exists(JlextdfbkeyimportController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlextdfbkeyimportController.php';
}

if (!class_exists('sportsmanagementControllerjlextdfbkeyimport', false)) {
    class_alias(JlextdfbkeyimportController::class, 'sportsmanagementControllerjlextdfbkeyimport');
}
