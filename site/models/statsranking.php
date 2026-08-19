<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\StatsrankingModel;

if (!class_exists(StatsrankingModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementStatsRankingModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/StatsrankingModel.php';
}

if (!class_exists('sportsmanagementModelStatsRanking', false)) {
    class_alias(StatsrankingModel::class, 'sportsmanagementModelStatsRanking');
}
