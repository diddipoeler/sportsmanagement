<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 events ranking controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\EventsrankingController;

if (!class_exists(EventsrankingController::class)) {
    $nativeController = JPATH_SITE . '/components/com_sportsmanagement/src/Controller/EventsrankingController.php';

    if (is_file($nativeController)) {
        require_once $nativeController;
    }
}

if (!class_exists(EventsrankingController::class)) {
    throw new \RuntimeException('SportsManagement native Eventsranking controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerEventsRanking', false)) {
    class_alias(EventsrankingController::class, 'sportsmanagementControllerEventsRanking');
}
