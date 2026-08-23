<?php
/** Legacy compatibility bridge for the native ProfiLeague import controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextprofleagimportController;

if (!class_exists(JlextprofleagimportController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlextprofleagimportController.php';
}

if (!class_exists('sportsmanagementControllerjlextprofleagimport', false)) {
    class_alias(JlextprofleagimportController::class, 'sportsmanagementControllerjlextprofleagimport');
}
