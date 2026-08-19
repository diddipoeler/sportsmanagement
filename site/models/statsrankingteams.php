<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\StatsrankingteamsModel;

if (!class_exists(StatsrankingteamsModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementStatsRankingModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/StatsrankingteamsModel.php';
}

if (!class_exists('sportsmanagementModelStatsRankingTeams', false)) {
    class_alias(StatsrankingteamsModel::class, 'sportsmanagementModelStatsRankingTeams');
}
