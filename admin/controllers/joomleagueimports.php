<?php
/** Legacy compatibility bridge for the native JoomLeague imports controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JoomleagueimportsController;

if (!class_exists(JoomleagueimportsController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JoomleagueimportsController.php';
}

if (!class_exists('sportsmanagementControllerjoomleagueimports', false)) {
    class_alias(JoomleagueimportsController::class, 'sportsmanagementControllerjoomleagueimports');
}
