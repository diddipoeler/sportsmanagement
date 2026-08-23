<?php
/** Legacy compatibility bridge for the native SIS import controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextsisimportController;

if (!class_exists(JlextsisimportController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlextsisimportController.php';
}

if (!class_exists('sportsmanagementControllerjlextsisimport', false)) {
    class_alias(JlextsisimportController::class, 'sportsmanagementControllerjlextsisimport');
}
