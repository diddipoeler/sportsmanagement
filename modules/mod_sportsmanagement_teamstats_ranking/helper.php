<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 Team Stats Ranking module.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementTeamStatsRanking\Site\Helper\TeamStatsRankingHelper;

if (!class_exists(TeamStatsRankingHelper::class)) {
    require_once __DIR__ . '/src/Helper/TeamStatsRankingHelper.php';
}

if (!class_exists('modSportsmanagementTeamStatHelper', false)) {
    class_alias(TeamStatsRankingHelper::class, 'modSportsmanagementTeamStatHelper');
}
