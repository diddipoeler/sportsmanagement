<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

final class StatsrankingModel extends SportsManagementStatsRankingModel
{
    public static int $divisionid = 0;
    public static int $teamid = 0;
    public static int $cfg_which_database = 0;
    public static int $projectid = 0;

    protected string $statsTemplate = 'statsranking';
}
