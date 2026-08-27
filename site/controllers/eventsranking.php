<?php
/** Legacy compatibility bridge for the native Joomla 5/6 events ranking controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\EventsrankingController;

if (!class_exists(EventsrankingController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/EventsrankingController.php';
}

if (!class_exists('sportsmanagementControllerEventsRanking', false)) {
    class_alias(EventsrankingController::class, 'sportsmanagementControllerEventsRanking');
}
