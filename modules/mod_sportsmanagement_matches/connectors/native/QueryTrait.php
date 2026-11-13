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
    /**
     * Load published fixtures for configured SportsManagement projects.
     * Build a match query with team, club and favourite-team filters, optional
     * played/upcoming time windows, and bound integer selectors. Add stable
     * ID:alias slugs for the legacy Joomla routing contract. Limit the
     * fixture query to at most 500 rows to avoid unbounded module loads.
     * @param DatabaseInterface $db Resolved component database.
     * @param Registry $params Match selection and sorting configuration.
     * @param array<int,int> $projectIds Valid positive project identifiers.
     * @return array<int,object> Published match rows with route identifiers.
     */
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
            if (!$favorites) {
                // Without favorite teams the requested filter must match no
                // games, not silently fall back to every selected project.
                return [];
            }

            $homeFavoriteParameters = $q->bindArray($favorites, ParameterType::INTEGER);
            $awayFavoriteParameters = $q->bindArray($favorites, ParameterType::INTEGER);
            $q->where(
                '(' . $db->quoteName('st1.team_id') . ' IN (' . implode(',', $homeFavoriteParameters) . ')'
                . ' OR ' . $db->quoteName('st2.team_id') . ' IN (' . implode(',', $awayFavoriteParameters) . '))'
            );
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

        $orderByProject = self::boundedMatchesOption($params->get('order_by_project', 0), 0, 1);
        $rawSortOrder = $params->get('lastsortorder', 'asc');
        $sortOrder = is_string($rawSortOrder) && strtolower($rawSortOrder) === 'desc' ? 'DESC' : 'ASC';
        $order = $orderByProject === 1
            ? 'm.match_date ASC, p.ordering ASC'
            : 'm.match_date ' . $sortOrder;
        $q->order($order);
        $limit = self::boundedMatchesOption($params->get('limit', 1), 1, 500);
        $db->setQuery($q, 0, max(1, $limit));

        $matches = $db->loadObjectList() ?: [];

        foreach ($matches as $match) {
            // Build Joomla ID:alias routes in PHP to avoid MySQL CONCAT_WS,
            // which is unavailable on other supported database drivers.
            $match->project_slug = (int) ($match->project_id ?? 0) . ':' . (string) ($match->project_alias ?? '');
            $match->round_slug = (int) ($match->round_id ?? 0) . ':' . (string) ($match->round_alias ?? '');
            $match->match_slug = (int) ($match->match_id ?? 0) . ':' . trim(
                (string) ($match->team1_alias ?? '') . '_' . (string) ($match->team2_alias ?? ''),
                '_'
            );
        }

        return $matches;
    }

    /**
     * Find all distinct favourite-team IDs for selected projects.
     * Load stored favourites through an integer-filtered Joomla query and
     * normalise delimited entries before applying the fixture filter.
     * @param DatabaseInterface $db Resolved component database.
     * @param array<int,int> $projectIds Selected positive project identifiers.
     * @return array<int,int> Distinct favourite-team identifiers.
     */
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

    /**
     * Convert a configured time period into a nonnegative number of seconds.
     * Recognise SECOND, MINUTE and HOUR units; all other units mean days,
     * matching the historical module configuration. Cap huge intervals so
     * multiplication and subsequent Unix timestamp arithmetic stay integers.
     * @param int $amount Requested interval, clamped to zero or greater.
     * @param string $unit Time unit identifier.
     * @return int Interval length in seconds.
     */
    private function seconds(int $amount, string $unit): int
    {
        $multiplier = match (strtoupper($unit)) {
            'SECOND' => 1,
            'MINUTE' => 60,
            'HOUR' => 3600,
            default => 86400,
        };

        // An oversized module setting would convert int multiplication
        // into a float on PHP 8, contradict the int return type and cause
        // a TypeError. Keep the interval and the later time() + interval
        // query boundary inside the supported integer range.
        $maximum = max(0, PHP_INT_MAX - time());

        return min(max(0, $amount), intdiv($maximum, $multiplier)) * $multiplier;
    }

    /**
     * Extract unique positive integers from module selector settings.
     * Accept integer/string scalar and multi-select values, split whitespace,
     * comma or semicolon lists, and reject booleans, nested arrays, zero and
     * overflowing values before database queries.
     * @param mixed $value Raw project, team or club selector.
     * @return array<int,int> Unique validated positive integers.
     */
    private function ids($value): array
    {
        $values = is_array($value) ? $value : [$value];
        $ids = [];

        foreach ($values as $candidate) {
            // Joomla multi-select parameters may contain comma- or semicolon-
            // separated values. Nested arrays and malformed IDs are ignored.
            if (!is_int($candidate) && !is_string($candidate)) {
                continue;
            }

            foreach (preg_split('/[\s,;]+/', (string) $candidate, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
                if (!ctype_digit($part)) {
                    continue;
                }

                $id = filter_var(ltrim($part, '0'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

                if ($id !== false) {
                    $ids[$id] = $id;
                }
            }
        }

        return array_values($ids);
    }

    /**
     * Read a bounded integer option from legacy Joomla Matches settings.
     *
     * Supports integer and decimal-string settings including leading zeros,
     * rejects malformed structured data and clamps valid oversized values
     * before any potentially overflowing integer conversion.
     *
     * @param mixed $value Raw module option.
     * @param int $default Fallback for invalid input.
     * @param int $maximum Highest accepted option value.
     * @return int Validated option between zero and the maximum.
     */
    private static function boundedMatchesOption(mixed $value, int $default, int $maximum): int
    {
        if (is_int($value)) {
            return max(0, min($maximum, $value));
        }

        if (!is_string($value) || strlen($value) > 4096
            || preg_match('/^[0-9]+$/D', $value) !== 1) {
            return $default;
        }

        $digits = ltrim($value, '0');

        if ($digits === '') {
            return 0;
        }

        if (strlen($digits) > strlen((string) $maximum)
            || (strlen($digits) === strlen((string) $maximum) && strcmp($digits, (string) $maximum) > 0)) {
            return $maximum;
        }

        return (int) $digits;
    }

    /**
     * Select the SportsManagement database used for match data.
     * Resolve the configured database using the injected Joomla connection
     * as a fallback for ordinary site installs. Reject malformed selectors
     * before the resolver sees them, preventing boolean/array coercion into
     * an unintended external-database request.
     * @param Registry $params Module database selector configuration.
     * @param DatabaseInterface $fallbackDatabase Default Joomla connection.
     * @return DatabaseInterface Connection for match and lookup queries.
     * @throws \InvalidArgumentException If a database selector is malformed.
     */
    private function database(Registry $params, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        $rawSelector = $params->get('cfg_which_database', 0);

        if (!is_int($rawSelector) && !is_string($rawSelector)) {
            throw new \InvalidArgumentException('Invalid SportsManagement Matches database selector.', 400);
        }

        $selector = trim((string) $rawSelector);

        if (preg_match('/^[0-9]+$/D', $selector) !== 1) {
            throw new \InvalidArgumentException('Invalid SportsManagement Matches database selector.', 400);
        }

        $digits = ltrim($selector, '0');
        $digits = $digits === '' ? '0' : $digits;
        $max = (string) PHP_INT_MAX;

        if (strlen($digits) > strlen($max)
            || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            throw new \InvalidArgumentException('Invalid SportsManagement Matches database selector.', 400);
        }

        return SportsManagementDatabaseResolver::resolve($fallbackDatabase, (int) $digits);
    }
}
