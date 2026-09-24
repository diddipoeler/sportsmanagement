<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Eventsranking model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EventsrankingModel;

if (!class_exists(EventsrankingModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/EventsrankingModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(EventsrankingModel::class)) {
    throw new \RuntimeException('SportsManagement native Eventsranking model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelEventsRanking', false)) {
    class_alias(EventsrankingModel::class, 'sportsmanagementModelEventsRanking');
}
