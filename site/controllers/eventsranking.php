<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 events ranking controller.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\EventsrankingController;

if (!class_exists(EventsrankingController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/EventsrankingController.php';
}

if (!class_exists('sportsmanagementControllerEventsRanking', false)) {
    class_alias(EventsrankingController::class, 'sportsmanagementControllerEventsRanking');
}
