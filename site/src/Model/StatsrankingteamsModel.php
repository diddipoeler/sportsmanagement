<?php
/**
 * Native Joomla 5/6 team stats ranking model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

final class StatsrankingteamsModel extends SportsManagementStatsRankingModel
{
    public static int $divisionid = 0;
    public static int $teamid = 0;
    public static int $cfg_which_database = 0;
    public static int $projectid = 0;

    protected string $statsTemplate = 'statsrankingteams';

    /**
     * Load the team ranking rows for every configured project statistic.
     *
     * Resolves the requested sort order, ignores invalid statistic objects
     * and nonpositive statistic IDs, and invokes each statistic's native
     * getTeamsRanking() method with the selected project and page limits.
     *
     * @param mixed $order Optional ranking order selection.
     * @return array Team ranking rows indexed by statistic identifier.
     */
    public function getTeamsStats($order = null): array
    {
        $stats = $this->getProjectUniqueStats();
        $rankingOrder = $this->resolveOrder($order);
        $results = [];

        foreach ($stats as $stat) {
            if (!is_object($stat) || !method_exists($stat, 'getTeamsRanking')) {
                continue;
            }
            $statId = (int) ($stat->id ?? 0);
            if ($statId <= 0) {
                continue;
            }
            $results[$statId] = $stat->getTeamsRanking(
                static::$projectid,
                $this->limit,
                $this->limitstart,
                $rankingOrder
            );
        }

        return $results;
    }

    /**
     * Combine per-statistic ranking rows into cumulative team totals.
     *
     * Skips invalid team IDs, sums numeric totals by team, preserves each
     * statistic's value under its ID and sorts the combined entries by
     * descending total for display in the team statistics table.
     *
     * @param array $teamsstats Rankings grouped by statistic identifier.
     * @return array Team totals sorted from highest to lowest.
     */
    public function getTeamsTotal($teamsstats = []): array
    {
        $totals = [];

        foreach ((array) $teamsstats as $statId => $rows) {
            foreach ((array) $rows as $row) {
                $teamId = (int) ($row->team_id ?? 0);
                if ($teamId <= 0) {
                    continue;
                }

                $value = (float) ($row->total ?? 0);
                $totals[$teamId] ??= [
                    'team_id' => $teamId,
                    'total' => 0.0,
                ];
                $totals[$teamId]['total'] += $value;
                $totals[$teamId][$statId] = $value;
            }
        }

        $totals = array_values($totals);
        usort(
            $totals,
            static fn(array $left, array $right): int => $right['total'] <=> $left['total']
        );

        return $totals;
    }
}
