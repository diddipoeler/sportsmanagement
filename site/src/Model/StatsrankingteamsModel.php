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
     * Malformed statistic IDs and nonpublic provider methods are ignored.
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
            if (!is_object($stat) || !is_callable([$stat, 'getTeamsRanking'])) {
                continue;
            }
            $statId = self::normaliseStatisticId($stat->id ?? null);
            if ($statId === null || $statId <= 0) {
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
     * Skips invalid team IDs, malformed statistic keys and nonfinite or
     * nested totals rather than triggering PHP 8 conversion warnings.
     * Sums finite numeric totals by team, preserves each statistic's value
     * under its ID and sorts the combined entries in descending order.
     *
     * @param array $teamsstats Rankings grouped by statistic identifier.
     * @return array Team totals sorted from highest to lowest.
     */
    public function getTeamsTotal($teamsstats = []): array
    {
        $totals = [];

        foreach ((array) $teamsstats as $statId => $rows) {
            $statisticId = self::normaliseStatisticId($statId);

            if ($statisticId === null || $statisticId <= 0 || !is_iterable($rows)) {
                continue;
            }

            foreach ($rows as $row) {
                if (!is_object($row)) {
                    continue;
                }

                $teamId = self::normaliseStatisticId($row->team_id ?? null);

                if ($teamId === null || $teamId <= 0) {
                    continue;
                }

                $rawTotal = $row->total ?? null;

                if (!is_int($rawTotal) && !is_float($rawTotal)
                    && (!is_string($rawTotal) || strlen($rawTotal) > 64
                        || preg_match('/^-?(?:[0-9]+(?:\.[0-9]*)?|\.[0-9]+)(?:[eE][+-]?[0-9]{1,3})?$/D', $rawTotal) !== 1)) {
                    continue;
                }

                $value = (float) $rawTotal;

                if (!is_finite($value)) {
                    continue;
                }

                $totals[$teamId] ??= [
                    'team_id' => $teamId,
                    'total' => 0.0,
                ];

                // Do not allow a sequence of individually finite values to
                // overflow a team's cumulative total to INF.
                if (!is_finite($totals[$teamId]['total'] + $value)) {
                    continue;
                }

                $totals[$teamId]['total'] += $value;
                $totals[$teamId][$statisticId] = $value;
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
