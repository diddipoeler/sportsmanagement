<?php
/**
 * Native Joomla 5/6 stats ranking model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
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
