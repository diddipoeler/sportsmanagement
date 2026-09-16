<?php
/**
 * Joomla 5/6 SportsManagement matches module PHP implementation.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementMatches\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

trait NativeQueryTrait
{
    /** @param array<int,int> $projectIds @return array<int,object> */
    private function loadMatches(DatabaseInterface $db, Registry $params, array $projectIds): array
    {
        $q = $db->createQuery()
            ->select([
                'm.id AS match_id', 'm.projectteam1_id', 'm.projectteam2_id', 'm.round_id',
                'm.team1_result', 'm.team2_result', 'm.team1_result_split', 'm.team2_result_split',
                'm.match_result_detail', 'm.match_result_type', 'm.team1_result_ot', 'm.team2_result_ot',
                'm.team1_result_so', 'm.team2_result_so', 'm.crowd', 'm.show_report', 'm.playground_id',
                'm.cancel', 'm.cancel_reason', 'm.match_date', 'm.match_timestamp',
                'r.name AS round_name', 'r.alias AS round_alias',
                'p.id AS project_id', 'p.name AS project_name', 'p.alias AS project_alias', 'p.season_id',
                'p.game_regular_time', 'p.game_parts', 'p.halftime', 'p.ordering AS project_ordering',
                'st1.team_id AS team1_id', 'st2.team_id AS team2_id',
                't1.name AS team1_name', 't1.short_name AS team1_short_name', 't1.middle_name AS team1_middle_name', 't1.alias AS team1_alias',
                't2.name AS team2_name', 't2.short_name AS team2_short_name', 't2.middle_name AS team2_middle_name', 't2.alias AS team2_alias',
                'pt1.picture AS team1_picture', 'pt2.picture AS team2_picture',
                'c1.id AS club1_id', 'c2.id AS club2_id', 'c1.logo_big AS club1_big', 'c2.logo_big AS club2_big',
                'c1.country AS club1_country', 'c2.country AS club2_country', 'c1.website AS club1_website', 'c2.website AS club2_website',
                'co1.alpha2 AS club1_alpha2', 'co2.alpha2 AS club2_alpha2',
                'pg.name AS playground_name', 'pg.short_name AS playground_short_name',
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
                "CONCAT_WS(':', r.id, r.alias) AS round_slug",
                "CONCAT_WS(':', m.id, CONCAT_WS('_', t1.alias, t2.alias)) AS match_slug",
            ])
            ->from('#__sportsmanagement_match AS m')
            ->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id')
            ->join('INNER', '#__sportsmanagement_project AS p ON p.id = r.project_id')
            ->join('LEFT', '#__sportsmanagement_project_team AS pt1 ON pt1.id = m.projectteam1_id')
            ->join('LEFT', '#__sportsmanagement_project_team AS pt2 ON pt2.id = m.projectteam2_id')
            ->join('LEFT', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id')
            ->join('LEFT', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id')
            ->join('LEFT', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id')
            ->join('LEFT', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id')
            ->join('LEFT', '#__sportsmanagement_club AS c1 ON c1.id = t1.club_id')
            ->join('LEFT', '#__sportsmanagement_club AS c2 ON c2.id = t2.club_id')
            ->join('LEFT', '#__sportsmanagement_countries AS co1 ON co1.alpha3 = c1.country')
            ->join('LEFT', '#__sportsmanagement_countries AS co2 ON co2.alpha3 = c2.country')
            ->join('LEFT', '#__sportsmanagement_playground AS pg ON pg.id = m.playground_id')
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('m.published') . ' = 1')
            ->whereIn($db->quoteName('p.id'), $projectIds, ParameterType::INTEGER);

        $excluded = $this->ids($params->get('project_not_used', []));
        if ($excluded) {
            $q->whereNotIn($db->quoteName('p.id'), $excluded, ParameterType::INTEGER);
        }

        $teams = $this->ids($params->get('teams', []));
        if ($teams) {
            $homeTeamParameters = $q->bindArray($teams, ParameterType::INTEGER);
            $awayTeamParameters = $q->bindArray($teams, ParameterType::INTEGER);
            $q->where(
                '(' . $db->quoteName('st1.team_id') . ' IN (' . implode(',', $homeTeamParameters) . ')'
                . ' OR ' . $db->quoteName('st2.team_id') . ' IN (' . implode(',', $awayTeamParameters) . '))'
            );
        }

        $clubs = $this->ids($params->get('club_ids', []));
        if ($clubs) {
            $homeClubParameters = $q->bindArray($clubs, ParameterType::INTEGER);
            $awayClubParameters = $q->bindArray($clubs, ParameterType::INTEGER);
            $q->where(
                '(' . $db->quoteName('c1.id') . ' IN (' . implode(',', $homeClubParameters) . ')'
                . ' OR ' . $db->quoteName('c2.id') . ' IN (' . implode(',', $awayClubParameters) . '))'
            );
        }

        if ((int) $params->get('use_fav', 0) === 1) {
            $favorites = $this->favoriteTeams($db, $projectIds);
            if ($favorites) {
                $homeFavoriteParameters = $q->bindArray($favorites, ParameterType::INTEGER);
                $awayFavoriteParameters = $q->bindArray($favorites, ParameterType::INTEGER);
                $q->where(
                    '(' . $db->quoteName('st1.team_id') . ' IN (' . implode(',', $homeFavoriteParameters) . ')'
                    . ' OR ' . $db->quoteName('st2.team_id') . ' IN (' . implode(',', $awayFavoriteParameters) . '))'
                );
            }
        }

        $now = time();
        $showPlayed = (int) $params->get('show_played', 0) === 1;
        $showNext = (int) $params->get('show_nextmatches', 0) === 1;

        if ($showPlayed) {
            $from = $now - $this->seconds((int) $params->get('result_add_time', 0), (string) $params->get('result_add_unit', 'DAY'));
        }

        if ($showNext) {
            $to = $now + $this->seconds((int) $params->get('period_int', 0), (string) $params->get('period_string', 'DAY'));
        }

        if ($showPlayed && !$showNext) {
            $q->where($db->quoteName('m.team1_result') . ' IS NOT NULL')
                ->where($db->quoteName('m.match_timestamp') . ' BETWEEN :playedFrom AND :playedNow')
                ->bind(':playedFrom', $from, ParameterType::INTEGER)
                ->bind(':playedNow', $now, ParameterType::INTEGER);
        } elseif (!$showPlayed && $showNext) {
            $q->where($db->quoteName('m.team1_result') . ' IS NULL')
                ->where($db->quoteName('m.match_timestamp') . ' BETWEEN :upcomingNow AND :upcomingTo')
                ->bind(':upcomingNow', $now, ParameterType::INTEGER)
                ->bind(':upcomingTo', $to, ParameterType::INTEGER);
        } elseif ($showPlayed && $showNext) {
            $q->where($db->quoteName('m.match_timestamp') . ' BETWEEN :windowFrom AND :windowTo')
                ->bind(':windowFrom', $from, ParameterType::INTEGER)
                ->bind(':windowTo', $to, ParameterType::INTEGER);
        }

        $order = (int) $params->get('order_by_project', 0) === 1
            ? 'm.match_date ASC, p.ordering ASC'
            : 'm.match_date ' . (strtolower((string) $params->get('lastsortorder', 'asc')) === 'desc' ? 'DESC' : 'ASC');
        $q->order($order);
        $db->setQuery($q, 0, max(1, (int) $params->get('limit', 1)));

        return $db->loadObjectList() ?: [];
    }

    /** @param array<int,int> $projectIds @return array<int,int> */
    private function favoriteTeams(DatabaseInterface $db, array $projectIds): array
    {
        $q = $db->createQuery()
            ->select($db->quoteName('fav_team'))
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('fav_team') . " != ''")
            ->whereIn($db->quoteName('id'), $projectIds, ParameterType::INTEGER);
        $db->setQuery($q);
        $out = [];

        foreach ($db->loadColumn() ?: [] as $value) {
            foreach ($this->ids($value) as $id) {
                $out[$id] = $id;
            }
        }

        return array_values($out);
    }

    private function seconds(int $amount, string $unit): int
    {
        return max(0, $amount) * match (strtoupper($unit)) {
            'SECOND' => 1,
            'MINUTE' => 60,
            'HOUR' => 3600,
            default => 86400,
        };
    }

    /** @return array<int,int> */
    private function ids($value): array
    {
        $values = is_array($value) ? $value : preg_split('/\s*,\s*/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
        $ids = [];

        foreach ((array) $values as $value) {
            if (($id = (int) $value) > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    private function database(Registry $params, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            (int) $params->get('cfg_which_database', 0)
        );
    }
}
