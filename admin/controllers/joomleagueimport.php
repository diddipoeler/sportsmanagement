<?php
/** Legacy compatibility bridge for the native JoomLeague import controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JoomleagueimportController;

if (!class_exists(JoomleagueimportController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JoomleagueimportController.php';
}

if (!class_exists('sportsmanagementControllerjoomleagueimport', false)) {
    class_alias(JoomleagueimportController::class, 'sportsmanagementControllerjoomleagueimport');
}
