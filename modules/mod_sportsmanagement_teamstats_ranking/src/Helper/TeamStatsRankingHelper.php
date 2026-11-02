<?php
/**
 * Native Joomla 5/6 data helper for the SportsManagement Team Stats Ranking module.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementTeamStatsRanking\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class TeamStatsRankingHelper
{
    /**
     * Load and calculate the selected team statistic ranking for the module.
     *
     * Validates the configured project and statistic identifiers, chooses the
     * database, calculates a bounded ranking and resolves its team records.
     * Missing projects, statistics or database errors produce empty data.
     * Invalid icon and ranking-order fields fall back to safe defaults.
     *
     * @param Registry $params Joomla module configuration.
     * @param DatabaseInterface $fallbackDatabase Default Joomla connection.
     * @return array<string, mixed> Ranking data, team metadata and selector.
     */
    public function getData(Registry $params, DatabaseInterface $fallbackDatabase): array
    {
        $projectId = $this->extractId($params->get('p', 0));
        $statId = $this->extractId($params->get('sid', 0));
        $selector = self::numericSetting($params->get('cfg_which_database', 0), 0, PHP_INT_MAX);

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

            $rawIcon = $stat->icon ?? '';
            $stat->icon_url = self::mediaUrl(is_string($rawIcon) ? $rawIcon : '');
            $statParams = $this->statParams($stat);
            $rawOrder = $params->get('ranking_order', $statParams->get('ranking_order', 'DESC'));
            $order = is_string($rawOrder) ? strtoupper($rawOrder) : 'DESC';
            $order = $order === 'ASC' ? 'ASC' : 'DESC';
            $limit = self::numericSetting($params->get('limit', 5), 5, 100);
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

    /**
     * Validate a decimal module setting before using it in database queries.
     *
     * Rejects booleans, arrays, partial numeric strings and values outside
     * the PHP integer range. Zero-padded legacy settings remain supported.
     * Positive limits are capped to keep module queries bounded.
     *
     * @param mixed $value Raw Joomla module parameter.
     * @param int $default Value used for invalid or nonpositive limits.
     * @param int $maximum Maximum accepted value.
     * @return int Normalised selector or bounded record limit.
     */
    private static function numericSetting(mixed $value, int $default, int $maximum): int
    {
        if (!is_int($value) && (!is_string($value) || !preg_match('/^[0-9]+$/D', $value))) {
            return $default;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        if ($number === false) {
            return $default;
        }

        return $default > 0 ? min($maximum, max(1, $number)) : min($maximum, $number);
    }

    /**
     * Validate ranking decimal precision without converting arrays or booleans.
     *
     * Accepts nonnegative integer values and decimal strings, preserving zero
     * decimals while limiting formatting precision to eight places.
     * Invalid or overflowing values use the historical two-decimal default.
     *
     * @param mixed $value Configured decimal precision.
     * @return int Number of decimal places between zero and eight.
     */
    private static function rankingPrecision(mixed $value): int
    {
        if (!is_int($value) && (!is_string($value) || !preg_match('/^[0-9]+$/D', $value))) {
            return 2;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $number === false ? 2 : min(8, $number);
    }

    /**
     * Resolve the SportsManagement database for a validated selector.
     *
     * @param int $selector Nonnegative configured database selector.
     * @param DatabaseInterface $fallbackDatabase Default Joomla connection.
     * @return DatabaseInterface Selected statistics database.
     */
    private function database(int $selector, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve($fallbackDatabase, $selector);
    }

    /**
     * Load a published project and construct Joomla route slugs.
     *
     * Fetches the project and optional season alias for the selected project ID.
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param int $projectId Positive project identifier.
     * @return object
     */
    private function loadProject(DatabaseInterface $db, int $projectId): ?object
    {
        $query = $db->createQuery()
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.name'),
                $db->quoteName('p.alias'),
                $db->quoteName('p.season_id'),
                $db->quoteName('p.sports_type_id'),
                $db->quoteName('s.alias', 'season_alias'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season', 's')
                . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id')
            )
            ->where($db->quoteName('p.id') . ' = :projectId')
            ->where($db->quoteName('p.published') . ' = 1')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $project = $db->loadObject();

        if ($project === null || $project === false) {
            return null;
        }

        // Joomla ID:alias segments are independent of SQL dialect.
        $project->slug = (int) ($project->id ?? 0) . ':' . (string) ($project->alias ?? '');
        $project->season_slug = (int) ($project->season_id ?? 0)
            . ':' . (string) ($project->season_alias ?? '');

        return $project;
    }

    /**
     * Load a published statistic belonging to the selected project.
     *
     * Checks position and publication joins before enabling the statistic in the ranking.
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param int $projectId Positive project identifier.
     * @param int $statId Positive statistic identifier.
     * @return object
     */
    private function loadStatistic(DatabaseInterface $db, int $projectId, int $statId): ?object
    {
        $query = $db->createQuery()
            ->select([
                $db->quoteName('stat.id'),
                $db->quoteName('stat.name'),
                $db->quoteName('stat.short'),
                $db->quoteName('stat.class'),
                $db->quoteName('stat.icon'),
                $db->quoteName('stat.params'),
                $db->quoteName('stat.baseparams'),
                $db->quoteName('stat.calculated'),
            ])
            ->from($db->quoteName('#__sportsmanagement_statistic', 'stat'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_position_statistic', 'ps')
                . ' ON ' . $db->quoteName('ps.statistic_id') . ' = ' . $db->quoteName('stat.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_position', 'pp')
                . ' ON ' . $db->quoteName('pp.position_id') . ' = ' . $db->quoteName('ps.position_id')
                . ' AND ' . $db->quoteName('pp.project_id') . ' = :projectId'
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_position', 'pos')
                . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ps.position_id')
            )
            ->where($db->quoteName('stat.id') . ' = :statId')
            ->where($db->quoteName('stat.published') . ' = 1')
            ->where($db->quoteName('pos.published') . ' = 1')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':statId', $statId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    /**
     * Combine base and specific statistic parameters.
     *
     * Loads the base parameters first, then merges statistic-specific overrides while ignoring malformed registry data.
     * @param object $stat Statistic record containing JSON parameter strings.
     * @return Registry Combined Joomla statistic settings.
     */
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

    /**
     * Calculate ordered team rankings for a statistic class.
     *
     * Supports direct statistics, event sums, ratios, differences and weighted totals, then formats ranked values and ties.
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param int $projectId Positive project identifier.
     * @param object $stat Selected statistic definition.
     * @param Registry $statParams Combined statistic settings.
     * @param string $order ASC or DESC ranking direction.
     * @param int $limit Maximum number of result rows.
     * @return array<int, array<string, mixed>> Ranked team entries and formatted values.
     */
    private function calculateRanking(
        DatabaseInterface $db,
        int $projectId,
        object $stat,
        Registry $statParams,
        string $order,
        int $limit
    ): array {
        $class = strtolower((string) ($stat->class ?? 'basic'));
        $precision = self::rankingPrecision($statParams->get('precision', 2));
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
                $percentage = in_array($statParams->get('show_percent_symbol', 1), [1, '1'], true);
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

    /**
     * Aggregate recorded match statistics by team.
     *
     * Filters by the selected project and statistic IDs and sums published match statistic values.
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param int $projectId Positive project identifier.
     * @param array<int, int> $statIds Statistic identifiers to aggregate.
     * @return array<int, float> Statistic totals keyed by team ID.
     */
    private function aggregateStatistics(DatabaseInterface $db, int $projectId, array $statIds): array
    {
        if ($statIds === []) {
            return [];
        }

        $query = $db->createQuery();
        $statPlaceholders = $query->bindArray($statIds, ParameterType::INTEGER);
        $query->select([
            'SUM(' . $db->quoteName('ms.value') . ') AS ' . $db->quoteName('total'),
            $db->quoteName('st.team_id'),
        ])
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tp.season_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match_statistic', 'ms')
                . ' ON ' . $db->quoteName('ms.teamplayer_id') . ' = ' . $db->quoteName('tp.id')
                . ' AND ' . $db->quoteName('ms.projectteam_id') . ' = ' . $db->quoteName('pt.id')
                . ' AND ' . $db->quoteName('ms.statistic_id') . ' IN (' . implode(',', $statPlaceholders) . ')'
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match', 'm')
                . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('ms.match_id')
                . ' AND ' . $db->quoteName('m.published') . ' = 1'
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
                . ' AND ' . $db->quoteName('r.project_id') . ' = ' . $db->quoteName('pt.project_id')
            )
            ->where($db->quoteName('pt.project_id') . ' = :projectId')
            ->group($db->quoteName('st.team_id'))
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query);

        return $this->valueMap($db->loadObjectList() ?: []);
    }

    /**
     * Aggregate recorded match events by team.
     *
     * Sums published match event counts for the selected event IDs and project.
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param int $projectId Positive project identifier.
     * @param array<int, int> $eventIds Event type identifiers to aggregate.
     * @return array<int, float> Event totals keyed by team ID.
     */
    private function aggregateEvents(DatabaseInterface $db, int $projectId, array $eventIds): array
    {
        if ($eventIds === []) {
            return [];
        }

        $query = $db->createQuery();
        $eventPlaceholders = $query->bindArray($eventIds, ParameterType::INTEGER);
        $query->select([
            'SUM(' . $db->quoteName('me.event_sum') . ') AS ' . $db->quoteName('total'),
            $db->quoteName('st.team_id'),
        ])
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tp.season_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match_event', 'me')
                . ' ON ' . $db->quoteName('me.teamplayer_id') . ' = ' . $db->quoteName('tp.id')
                . ' AND ' . $db->quoteName('me.projectteam_id') . ' = ' . $db->quoteName('pt.id')
                . ' AND ' . $db->quoteName('me.event_type_id') . ' IN (' . implode(',', $eventPlaceholders) . ')'
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match', 'm')
                . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('me.match_id')
                . ' AND ' . $db->quoteName('m.published') . ' = 1'
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
                . ' AND ' . $db->quoteName('r.project_id') . ' = ' . $db->quoteName('pt.project_id')
            )
            ->where($db->quoteName('pt.project_id') . ' = :projectId')
            ->where($db->quoteName('tp.published') . ' = 1')
            ->group($db->quoteName('st.team_id'))
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query);

        return $this->valueMap($db->loadObjectList() ?: []);
    }

    /**
     * Compute weighted sums of selected match statistics.
     *
     * Pairs statistic IDs with configured factors, aggregates the recorded totals and applies the corresponding factor for each team.
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param int $projectId Positive project identifier.
     * @param Registry $params Configured statistic IDs and numeric factors.
     * @return array<int, float> Weighted totals keyed by team ID.
     */
    private function weightedStatistics(DatabaseInterface $db, int $projectId, Registry $params): array
    {
        $ids = $this->ids($params->get('stat_ids', []));
        $factors = self::weightFactors($params->get('factors', ''));

        if ($ids === [] || count($ids) !== count($factors)) {
            return [];
        }

        $query = $db->createQuery();
        $statPlaceholders = $query->bindArray($ids, ParameterType::INTEGER);
        $query->select([
            'SUM(' . $db->quoteName('ms.value') . ') AS ' . $db->quoteName('total'),
            $db->quoteName('ms.statistic_id'),
            $db->quoteName('st.team_id'),
        ])
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tp.season_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match_statistic', 'ms')
                . ' ON ' . $db->quoteName('ms.teamplayer_id') . ' = ' . $db->quoteName('tp.id')
                . ' AND ' . $db->quoteName('ms.projectteam_id') . ' = ' . $db->quoteName('pt.id')
                . ' AND ' . $db->quoteName('ms.statistic_id') . ' IN (' . implode(',', $statPlaceholders) . ')'
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match', 'm')
                . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('ms.match_id')
                . ' AND ' . $db->quoteName('m.published') . ' = 1'
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
                . ' AND ' . $db->quoteName('r.project_id') . ' = ' . $db->quoteName('pt.project_id')
            )
            ->where($db->quoteName('pt.project_id') . ' = :projectId')
            ->group([
                $db->quoteName('st.team_id'),
                $db->quoteName('ms.statistic_id'),
            ])
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        $factorById = array_combine($ids, $factors) ?: [];

        return self::weightedValueMap($rows, $factorById);
    }

    /**
     * Sum weighted statistic rows while rejecting malformed legacy data.
     *
     * Only positive integer team/statistic IDs and finite numeric totals
     * contribute to the result. Invalid or overflowing calculations are
     * discarded instead of contaminating the entire ranking with INF or NAN.
     *
     * @param array<int, mixed> $rows Database aggregate records.
     * @param array<int, float> $factorById Weight for each statistic identifier.
     * @return array<int, float> Finite weighted totals keyed by team ID.
     */
    private static function weightedValueMap(array $rows, array $factorById): array
    {
        $values = [];

        foreach ($rows as $row) {
            if (!is_object($row)) {
                continue;
            }

            $teamId = filter_var($row->team_id ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $statId = filter_var($row->statistic_id ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $total = $row->total ?? null;

            if ($teamId === false || $statId === false || !array_key_exists($statId, $factorById)
                || (!is_int($total) && !is_float($total) && !is_string($total))
                || !is_numeric($total)) {
                continue;
            }

            $weight = $factorById[$statId];
            $number = (float) $total;

            if (!is_finite($number) || !is_finite($weight)) {
                continue;
            }

            $contribution = $weight * $number;
            $next = ($values[$teamId] ?? 0.0) + $contribution;

            if (is_finite($contribution) && is_finite($next)) {
                $values[$teamId] = $next;
            }
        }

        return $values;
    }

    /**
     * Parse comma-separated finite statistic weighting factors.
     *
     * Rejects arrays, booleans, empty entries, partially numeric values and
     * non-finite results rather than silently converting them to zero or one.
     *
     * @param mixed $value Raw factors setting from statistic parameters.
     * @return array<int, float> Valid weights or an empty list for invalid input.
     */
    private static function weightFactors(mixed $value): array
    {
        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $factors = [];

        foreach (explode(',', $value) as $part) {
            $part = trim($part);

            if ($part === '' || !is_numeric($part)) {
                return [];
            }

            $factor = (float) $part;

            if (!is_finite($factor)) {
                return [];
            }

            $factors[] = $factor;
        }

        return $factors;
    }

    /**
     * Count played, published matches for each team in a project.
     *
     * Counts distinct fixtures with a recorded result, to serve as the denominator for per-game statistics.
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param int $projectId Positive project identifier.
     * @return array<int, float> Played-match counts keyed by team ID.
     */
    private function playedMatches(DatabaseInterface $db, int $projectId): array
    {
        $query = $db->createQuery()
            ->select([
                'COUNT(DISTINCT ' . $db->quoteName('m.id') . ') AS ' . $db->quoteName('total'),
                $db->quoteName('st.team_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match', 'm')
                . ' ON (' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt.id')
                . ' OR ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt.id') . ')'
            )
            ->where($db->quoteName('pt.project_id') . ' = :projectId')
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('m.team1_result') . ' IS NOT NULL')
            ->group($db->quoteName('st.team_id'))
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query);

        return $this->valueMap($db->loadObjectList() ?: []);
    }

    /**
     * Count wins for each team in a published project fixture.
     *
     * Compares home and away scores for played matches and counts distinct victories.
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param int $projectId Positive project identifier.
     * @return array<int, float> Win counts keyed by team ID.
     */
    private function wonMatches(DatabaseInterface $db, int $projectId): array
    {
        $query = $db->createQuery()
            ->select([
                'COUNT(DISTINCT ' . $db->quoteName('m.id') . ') AS ' . $db->quoteName('total'),
                $db->quoteName('st.team_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match', 'm')
                . ' ON (' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt.id')
                . ' OR ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt.id') . ')'
            )
            ->where($db->quoteName('pt.project_id') . ' = :projectId')
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('m.team1_result') . ' IS NOT NULL')
            ->where(
                '((' . $db->quoteName('pt.id') . ' = ' . $db->quoteName('m.projectteam1_id')
                . ' AND ' . $db->quoteName('m.team1_result') . ' > ' . $db->quoteName('m.team2_result') . ')'
                . ' OR (' . $db->quoteName('pt.id') . ' = ' . $db->quoteName('m.projectteam2_id')
                . ' AND ' . $db->quoteName('m.team2_result') . ' > ' . $db->quoteName('m.team1_result') . '))'
            )
            ->group($db->quoteName('st.team_id'))
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query);

        return $this->valueMap($db->loadObjectList() ?: []);
    }

    /**
     * Load display metadata and logo URLs for ranked teams.
     *
     * Fetches team and club details only for validated IDs and prepares their route slugs and image URLs.
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param array<int, int> $teamIds Requested ranked team IDs.
     * @return array<int, object> Team records keyed by database ID.
     */
    private function loadTeams(DatabaseInterface $db, array $teamIds): array
    {
        $teamIds = $this->ids($teamIds);
        if ($teamIds === []) {
            return [];
        }

        $query = $db->createQuery()
            ->select([
                $db->quoteName('t.id'),
                $db->quoteName('t.name'),
                $db->quoteName('t.short_name'),
                $db->quoteName('t.middle_name'),
                $db->quoteName('t.alias'),
                $db->quoteName('t.club_id'),
                $db->quoteName('c.name', 'club_name'),
                $db->quoteName('c.alias', 'club_alias'),
                $db->quoteName('c.logo_small'),
                $db->quoteName('c.logo_middle'),
                $db->quoteName('c.logo_big'),
                $db->quoteName('c.country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_club', 'c')
                . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id')
            )
            ->whereIn($db->quoteName('t.id'), $teamIds, ParameterType::INTEGER);
        $db->setQuery($query);
        $teams = $db->loadObjectList('id') ?: [];

        foreach ($teams as $team) {
            $team->team_slug = (int) ($team->id ?? 0) . ':' . (string) ($team->alias ?? '');
            $clubId = (int) ($team->club_id ?? 0);
            $team->club_slug = $clubId > 0
                ? $clubId . ':' . (string) ($team->club_alias ?? '')
                : '';
            $team->logo_url = self::mediaUrl((string) ($team->logo_big ?? ''));
        }

        return $teams;
    }

    /**
     * Convert aggregated database rows into numeric team totals.
     *
     * Maps each valid team ID to a finite numeric total. Malformed rows,
     * unsupported types, invalid IDs and non-finite values are skipped.
     * @param array<int, mixed> $rows Database aggregation result rows.
     * @return array<int, float> Totals keyed by team identifier.
     */
    private function valueMap(array $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            if (!is_object($row)) {
                continue;
            }

            $teamId = $this->extractId($row->team_id ?? null);
            $total = $row->total ?? null;

            if ($teamId <= 0
                || (!is_int($total) && !is_float($total) && !is_string($total))
                || !is_numeric($total)
            ) {
                continue;
            }

            $number = (float) $total;

            if (!is_finite($number)) {
                continue;
            }

            $map[$teamId] = $number;
        }

        return $map;
    }

    /**
     * Calculate per-team ratios for two statistic maps.
     *
     * Only emits results for positive denominators to avoid division by zero.
     * @param array<int, float> $numerator Numerator totals keyed by team ID.
     * @param array<int, float> $denominator Denominator totals keyed by team ID.
     * @return array<int, float> Ratios keyed by team ID.
     */
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

    /**
     * Subtract one map of team totals from another.
     *
     * Uses the union of team identifiers so missing entries are treated as zero.
     * @param array<int, float> $add Positive totals keyed by team ID.
     * @param array<int, float> $sub Totals to subtract by team ID.
     * @return array<int, float> Differences keyed by team ID.
     */
    private function subtractMaps(array $add, array $sub): array
    {
        $result = [];
        foreach (array_unique(array_merge(array_keys($add), array_keys($sub))) as $teamId) {
            $result[(int) $teamId] = (float) ($add[$teamId] ?? 0) - (float) ($sub[$teamId] ?? 0);
        }

        return $result;
    }

    /**
     * Normalise module selections into unique positive identifiers.
     *
     * Accepts integer/string scalars or arrays, parses delimited selectors,
     * and rejects booleans and objects rather than implicitly casting them
     * to integers before use in bound SQL conditions.
     * @param mixed $values Raw selection or list of selections.
     * @return array<int, int> Unique validated positive identifiers.
     */
    private function ids(mixed $values): array
    {
        if (!is_array($values)) {
            if (!is_string($values) && !is_int($values)) {
                return [];
            }

            $values = preg_split('/[\s,;|]+/', (string) $values, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        $ids = [];

        foreach ((array) $values as $value) {
            $id = $this->extractId($value);
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    /**
     * Read a positive Joomla ID or ID:alias selector safely.
     *
     * Supports one-element selector arrays and zero-padded decimal IDs but rejects unsupported types, invalid formats and integer overflow.
     * @param mixed $value Raw Joomla identifier or selector.
     * @return int Positive ID, or zero when invalid.
     */
    private function extractId(mixed $value): int
    {
        // Single selectors can arrive wrapped in a single-element array.
        if (is_array($value)) {
            if (count($value) !== 1) {
                return 0;
            }

            $value = reset($value);
        }

        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $text = trim((string) $value);

        if (!preg_match('/^([0-9]+)(?::[^:\s,;|]+)?$/D', $text, $match)) {
            return 0;
        }

        $digits = ltrim($match[1], '0');

        if ($digits === '') {
            return 0;
        }

        $id = filter_var($digits, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $id === false ? 0 : $id;
    }

    /**
     * Normalise team logo and statistic icon paths for safe HTML output.
     *
     * Allows absolute HTTP(S) URLs without credentials; rejects unsafe relative paths and encodes local media segments beneath the Joomla installation root.
     * @param string $path Configured remote or local media path.
     * @return string Encoded media URL or empty string for invalid values.
     */
    public static function mediaUrl(string $path): string
    {
        $path = trim($path);

        if ($path === ''
            || preg_match('/[\x00-\x1F\x7F]/', $path)
            || str_contains($path, '\\')
        ) {
            return '';
        }

        if (preg_match('~^https?://~i', $path)) {
            if (preg_match('/[\x00-\x20\x7F]/', $path)
                || filter_var($path, FILTER_VALIDATE_URL) === false
            ) {
                return '';
            }

            $parts = parse_url($path);

            return is_array($parts)
                && !empty($parts['host'])
                && !isset($parts['user'])
                && !isset($parts['pass'])
                ? $path
                : '';
        }

        if (str_starts_with($path, '//')
            || preg_match('~^[a-z][a-z0-9+.-]*:~i', $path)
            || preg_match('~(?:^|/)\.\.?(?:/|$)~', $path)
            || str_contains($path, '%')
            || strpbrk($path, '?#') !== false
        ) {
            return '';
        }

        $relative = ltrim($path, '/');
        $encoded = implode('/', array_map('rawurlencode', explode('/', $relative)));

        return rtrim((string) Uri::root(), '/') . '/' . $encoded;
    }

    /**
     * Build the consistent empty ranking response.
     *
     * Returns null project/statistic records and empty lists when configured data is unavailable or fails to load.
     * @return array<string, mixed> Empty module ranking context.
     */
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
