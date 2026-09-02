<?php
/**
 * Native Joomla 5/6 data helper for the SportsManagement Team Stats Ranking module.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementTeamStatsRanking\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class TeamStatsRankingHelper
{
    public function getData(Registry $params, DatabaseInterface $fallbackDatabase): array
    {
        $projectId = $this->extractId($params->get('p', 0));
        $statId = $this->extractId($params->get('sid', 0));
        $selector = max(0, (int) $params->get('cfg_which_database', 0));

        if ($projectId <= 0 || $statId <= 0) {
            return $this->emptyData();
        }

        try {
            $db = $this->database($selector, $fallbackDatabase);
            $project = $this->loadProject($db, $projectId);
            $stat = $this->loadStatistic($db, $projectId, $statId);

            if (!$project || !$stat) {
                return $this->emptyData();
            }

            $statParams = $this->statParams($stat);
            $order = strtoupper((string) $params->get('ranking_order', $statParams->get('ranking_order', 'DESC')));
            $order = $order === 'ASC' ? 'ASC' : 'DESC';
            $limit = max(1, (int) $params->get('limit', 5));
            $ranking = $this->calculateRanking($db, $projectId, $stat, $statParams, $order, $limit);
            $teams = $this->loadTeams($db, array_column($ranking, 'team_id'));

            return [
                'project' => $project,
                'stat' => $stat,
                'ranking' => $ranking,
                'teams' => $teams,
                'databaseSelector' => $selector,
            ];
        } catch (\Throwable) {
            return $this->emptyData();
        }
    }

    private function database(int $selector, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve($fallbackDatabase, $selector);
    }

    private function loadProject(DatabaseInterface $db, int $projectId): ?object
    {
        $query = $db->getQuery(true)
            ->select([
                'p.id', 'p.name', 'p.alias', 'p.season_id', 'p.sports_type_id',
                's.alias AS season_alias',
                "CONCAT_WS(':', p.id, p.alias) AS slug",
                "CONCAT_WS(':', s.id, s.alias) AS season_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season', 's') . ' ON s.id = p.season_id')
            ->where('p.id = ' . $projectId)
            ->where('p.published = 1');
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    private function loadStatistic(DatabaseInterface $db, int $projectId, int $statId): ?object
    {
        $query = $db->getQuery(true)
            ->select([
                'stat.id', 'stat.name', 'stat.short', 'stat.class', 'stat.icon',
                'stat.params', 'stat.baseparams', 'stat.calculated',
            ])
            ->from($db->quoteName('#__sportsmanagement_statistic', 'stat'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position_statistic', 'ps') . ' ON ps.statistic_id = stat.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'pp')
                . ' ON pp.position_id = ps.position_id AND pp.project_id = ' . $projectId)
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON pos.id = ps.position_id')
            ->where('stat.id = ' . $statId)
            ->where('stat.published = 1')
            ->where('pos.published = 1');
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    private function statParams(object $stat): Registry
    {
        $params = new Registry();

        foreach ([(string) ($stat->baseparams ?? ''), (string) ($stat->params ?? '')] as $encoded) {
            if ($encoded === '') {
                continue;
            }

            try {
                $part = new Registry();
                $part->loadString($encoded);
                $params->merge($part);
            } catch (\Throwable) {
                // Keep any parameters successfully parsed before the malformed part.
            }
        }

        return $params;
    }

    private function calculateRanking(
        DatabaseInterface $db,
        int $projectId,
        object $stat,
        Registry $statParams,
        string $order,
        int $limit
    ): array {
        $class = strtolower((string) ($stat->class ?? 'basic'));
        $precision = max(0, min(8, (int) $statParams->get('precision', 2)));
        $percentage = false;
        $values = [];

        switch ($class) {
            case 'sumevents':
                $values = $this->aggregateEvents($db, $projectId, $this->ids($statParams->get('stat_ids', [])));
                break;

            case 'pergame':
                $values = $this->divideMaps(
                    $this->aggregateStatistics($db, $projectId, $this->ids($statParams->get('stat_ids', []))),
                    $this->playedMatches($db, $projectId)
                );
                break;

            case 'eventpergame':
                $values = $this->divideMaps(
                    $this->aggregateEvents($db, $projectId, $this->ids($statParams->get('stat_ids', []))),
                    $this->playedMatches($db, $projectId)
                );
                break;

            case 'difference':
                $values = $this->subtractMaps(
                    $this->aggregateStatistics($db, $projectId, $this->ids($statParams->get('add_ids', []))),
                    $this->aggregateStatistics($db, $projectId, $this->ids($statParams->get('sub_ids', [])))
                );
                break;

            case 'percentage':
                $values = $this->divideMaps(
                    $this->aggregateStatistics($db, $projectId, $this->ids($statParams->get('numerator_ids', []))),
                    $this->aggregateStatistics($db, $projectId, $this->ids($statParams->get('denominator_ids', [])))
                );
                $percentage = (int) $statParams->get('show_percent_symbol', 1) === 1;
                break;

            case 'complexsumpergame':
                $values = $this->divideMaps(
                    $this->weightedStatistics($db, $projectId, $statParams),
                    $this->playedMatches($db, $projectId)
                );
                break;

            case 'winpergame':
                $values = $this->divideMaps(
                    $this->wonMatches($db, $projectId),
                    $this->playedMatches($db, $projectId)
                );
                break;

            case 'complexsum':
                $weighted = $this->weightedStatistics($db, $projectId, $statParams);
                $values = $weighted !== []
                    ? $weighted
                    : $this->aggregateStatistics($db, $projectId, [(int) $stat->id]);
                break;

            case 'basic':
            case 'sumstats':
            default:
                // Historical team ranking for basic/sumstats and unknown extension
                // statistics is the stored value of the selected statistic itself.
                $values = $this->aggregateStatistics($db, $projectId, [(int) $stat->id]);
                break;
        }

        if ($order === 'ASC') {
            asort($values, SORT_NUMERIC);
        } else {
            arsort($values, SORT_NUMERIC);
        }

        $rows = [];
        $lastValue = null;
        $lastRank = 0;
        $position = 0;

        foreach ($values as $teamId => $value) {
            $position++;
            if (count($rows) >= $limit) {
                break;
            }

            $rank = $lastValue !== null && abs((float) $lastValue - (float) $value) < 0.0000001
                ? $lastRank
                : $position;
            $lastValue = (float) $value;
            $lastRank = $rank;

            $displayValue = $percentage
                ? number_format(100 * (float) $value, $precision) . '%'
                : number_format((float) $value, $precision);

            $rows[] = [
                'team_id' => (int) $teamId,
                'rank' => $rank,
                'value' => (float) $value,
                'total' => $displayValue,
            ];
        }

        return $rows;
    }

    private function aggregateStatistics(DatabaseInterface $db, int $projectId, array $statIds): array
    {
        if ($statIds === []) {
            return [];
        }

        $query = $db->getQuery(true)
            ->select(['SUM(ms.value) AS total', 'st.team_id'])
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.team_id = tp.team_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON pt.team_id = st.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_match_statistic', 'ms')
                . ' ON ms.teamplayer_id = tp.id AND ms.statistic_id IN (' . implode(',', $statIds) . ')')
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON m.id = ms.match_id AND m.published = 1')
            ->where('pt.project_id = ' . $projectId)
            ->group('st.team_id');
        $db->setQuery($query);

        return $this->valueMap($db->loadObjectList() ?: []);
    }

    private function aggregateEvents(DatabaseInterface $db, int $projectId, array $eventIds): array
    {
        if ($eventIds === []) {
            return [];
        }

        $query = $db->getQuery(true)
            ->select(['SUM(me.event_sum) AS total', 'st.team_id'])
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.team_id = tp.team_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON pt.team_id = st.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_match_event', 'me')
                . ' ON me.teamplayer_id = tp.id AND me.event_type_id IN (' . implode(',', $eventIds) . ')')
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON m.id = me.match_id AND m.published = 1')
            ->where('pt.project_id = ' . $projectId)
            ->where('tp.published = 1')
            ->group('st.team_id');
        $db->setQuery($query);

        return $this->valueMap($db->loadObjectList() ?: []);
    }

    private function weightedStatistics(DatabaseInterface $db, int $projectId, Registry $params): array
    {
        $ids = $this->ids($params->get('stat_ids', []));
        $factors = array_map('floatval', preg_split('/\s*,\s*/', (string) $params->get('factors', ''), -1, PREG_SPLIT_NO_EMPTY) ?: []);

        if ($ids === [] || count($ids) !== count($factors)) {
            return [];
        }

        $query = $db->getQuery(true)
            ->select(['SUM(ms.value) AS total', 'ms.statistic_id', 'st.team_id'])
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.team_id = tp.team_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON pt.team_id = st.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_match_statistic', 'ms')
                . ' ON ms.teamplayer_id = tp.id AND ms.statistic_id IN (' . implode(',', $ids) . ')')
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON m.id = ms.match_id AND m.published = 1')
            ->where('pt.project_id = ' . $projectId)
            ->group(['st.team_id', 'ms.statistic_id']);
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        $factorById = array_combine($ids, $factors) ?: [];
        $values = [];

        foreach ($rows as $row) {
            $teamId = (int) $row->team_id;
            $statId = (int) $row->statistic_id;
            $values[$teamId] = ($values[$teamId] ?? 0.0)
                + (float) ($factorById[$statId] ?? 0.0) * (float) $row->total;
        }

        return $values;
    }

    private function playedMatches(DatabaseInterface $db, int $projectId): array
    {
        $query = $db->getQuery(true)
            ->select(['COUNT(DISTINCT m.id) AS total', 'st.team_id'])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.id = pt.team_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm')
                . ' ON (m.projectteam1_id = pt.id OR m.projectteam2_id = pt.id)')
            ->where('pt.project_id = ' . $projectId)
            ->where('m.published = 1')
            ->where('m.team1_result IS NOT NULL')
            ->group('st.team_id');
        $db->setQuery($query);

        return $this->valueMap($db->loadObjectList() ?: []);
    }

    private function wonMatches(DatabaseInterface $db, int $projectId): array
    {
        $query = $db->getQuery(true)
            ->select(['COUNT(DISTINCT m.id) AS total', 'st.team_id'])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.id = pt.team_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm')
                . ' ON (m.projectteam1_id = pt.id OR m.projectteam2_id = pt.id)')
            ->where('pt.project_id = ' . $projectId)
            ->where('m.published = 1')
            ->where('m.team1_result IS NOT NULL')
            ->where('((pt.id = m.projectteam1_id AND m.team1_result > m.team2_result)'
                . ' OR (pt.id = m.projectteam2_id AND m.team2_result > m.team1_result))')
            ->group('st.team_id');
        $db->setQuery($query);

        return $this->valueMap($db->loadObjectList() ?: []);
    }

    private function loadTeams(DatabaseInterface $db, array $teamIds): array
    {
        $teamIds = $this->ids($teamIds);
        if ($teamIds === []) {
            return [];
        }

        $query = $db->getQuery(true)
            ->select([
                't.id', 't.name', 't.short_name', 't.middle_name', 't.alias', 't.club_id',
                'c.name AS club_name', 'c.alias AS club_alias', 'c.logo_small', 'c.logo_middle', 'c.logo_big', 'c.country',
                "CONCAT_WS(':', t.id, t.alias) AS team_slug",
                "CONCAT_WS(':', c.id, c.alias) AS club_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON c.id = t.club_id')
            ->where('t.id IN (' . implode(',', $teamIds) . ')');
        $db->setQuery($query);

        return $db->loadObjectList('id') ?: [];
    }

    private function valueMap(array $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->team_id] = (float) $row->total;
        }

        return $map;
    }

    private function divideMaps(array $numerator, array $denominator): array
    {
        $result = [];
        foreach ($numerator as $teamId => $value) {
            $den = (float) ($denominator[$teamId] ?? 0);
            if ($den > 0) {
                $result[$teamId] = (float) $value / $den;
            }
        }

        return $result;
    }

    private function subtractMaps(array $add, array $sub): array
    {
        $result = [];
        foreach (array_unique(array_merge(array_keys($add), array_keys($sub))) as $teamId) {
            $result[(int) $teamId] = (float) ($add[$teamId] ?? 0) - (float) ($sub[$teamId] ?? 0);
        }

        return $result;
    }

    private function ids(mixed $values): array
    {
        $values = is_array($values) ? $values : preg_split('/[\s,;|]+/', (string) $values, -1, PREG_SPLIT_NO_EMPTY);
        $ids = [];

        foreach ((array) $values as $value) {
            $id = $this->extractId($value);
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    private function extractId(mixed $value): int
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        $text = trim((string) $value);
        return $text === '' ? 0 : max(0, (int) strtok($text, ':'));
    }

    private function emptyData(): array
    {
        return [
            'project' => null,
            'stat' => null,
            'ranking' => [],
            'teams' => [],
            'databaseSelector' => 0,
        ];
    }
}
