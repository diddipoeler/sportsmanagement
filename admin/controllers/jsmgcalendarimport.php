<?php
/** Legacy compatibility bridge for the native administrator Google calendar import controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JsmgcalendarimportController;

if (!class_exists(JsmgcalendarimportController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JsmgcalendarimportController.php';
}

if (!class_exists('sportsmanagementControllerjsmgcalendarImport', false)) {
    class_alias(JsmgcalendarimportController::class, 'sportsmanagementControllerjsmgcalendarImport');
}
