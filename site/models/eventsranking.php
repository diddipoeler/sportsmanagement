<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EventsrankingModel;

if (!class_exists(EventsrankingModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/EventsrankingModel.php';
}

if (!class_exists('sportsmanagementModelEventsRanking', false)) {
    class_alias(EventsrankingModel::class, 'sportsmanagementModelEventsRanking');
}
