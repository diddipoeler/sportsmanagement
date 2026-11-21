<?php
/**
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementSportsTypeStatistics\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class SportsTypeStatisticsHelper
{
    /**
     * Load the selected sport and its scoped statistics for module display.
     * Validates sport and database selectors, resolves the Joomla or external
     * SportsManagement connection and computes project, league, season, venue,
     * team, player and fixture totals for the selected sport only.
     * Rejects unsupported database selectors, corrupt metadata, mismatched
     * sport records and invalid count values rather than turning arbitrary
     * PHP values into numbers.
     * Returns an empty state without opening an external database when no
     * sport has been selected.
     * @param Registry $params Sports type statistics module parameters.
     * @param DatabaseInterface $fallbackDatabase Primary Joomla database.
     * @return array Sport metadata and aggregate count values.
     */
    public function getData(Registry $params, DatabaseInterface $fallbackDatabase): array
    {
        $sportTypeId = self::nonNegativeSelector($params->get('sportstypes', 0));
        $databaseSelector = self::nonNegativeSelector($params->get('cfg_which_database', 0));

        // A malformed selector must never select an unrelated sport or DB.
        // Without a selected sport there is no useful query to run: do not
        // resolve a potentially remote connection just for an empty widget.
        if ($sportTypeId === null || $sportTypeId === 0
            || $databaseSelector === null || $databaseSelector > 1) {
            return ['sportstype' => null, 'counts' => ['projects' => 0]];
        }

        $db = $this->database($databaseSelector, $fallbackDatabase);

        $sportType = null;
        if ($sportTypeId > 0) {
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('id'),
                    $db->quoteName('name'),
                    $db->quoteName('icon'),
                ])
                ->from($db->quoteName('#__sportsmanagement_sports_type'))
                ->where($db->quoteName('id') . ' = :sportTypeId')
                ->bind(':sportTypeId', $sportTypeId, ParameterType::INTEGER);
            $db->setQuery($query, 0, 1);
            $record = $db->loadObject();

            // Defensive validation for imported records or database adapters
            // returning an unexpected row. Never display another sport as
            // the currently selected category.
            $recordId = is_object($record)
                ? self::nonNegativeSelector($record->id ?? null) : null;
            $sportType = $recordId === $sportTypeId ? $record : null;

            if ($sportType !== null) {
                $icon = $sportType->icon ?? null;
                $name = $sportType->name ?? null;
                $sportType->icon_url = is_string($icon) ? self::imageUrl($icon) : '';
                $sportType->name = is_string($name) && strlen($name) <= 512
                    ? $name : '';
            }
        }

        $counts = [
            'projects' => $this->countProjects($db, $sportTypeId),
        ];

        // The layout displays only a no-projects message for this state.
        // Avoid querying unrelated global tables when this sport has no projects.
        if ($counts['projects'] === 0) {
            return ['sportstype' => $sportType, 'counts' => $counts];
        }

        $requested = [
            'leagues' => ['show_leagues', fn() => $this->countProjectDimension($db, 'league_id', $sportTypeId)],
            'seasons' => ['show_seasons', fn() => $this->countProjectDimension($db, 'season_id', $sportTypeId)],
            'playgrounds' => ['show_playgrounds', fn() => $this->countSportPlaygrounds($db, $sportTypeId)],
            'clubs' => ['show_clubs', fn() => $this->countSportClubs($db, $sportTypeId)],
            'teams' => ['show_teams', fn() => $this->countProjectRelation($db, '#__sportsmanagement_project_team', $sportTypeId)],
            'players' => ['show_players', fn() => $this->countSportPlayers($db, $sportTypeId)],
            'divisions' => ['show_divisions', fn() => $this->countProjectRelation($db, '#__sportsmanagement_division', $sportTypeId)],
            'rounds' => ['show_rounds', fn() => $this->countProjectRelation($db, '#__sportsmanagement_round', $sportTypeId)],
            'matches' => ['show_matches', fn() => $this->countMatches($db, $sportTypeId)],
            'player_events' => ['show_player_events', fn() => $this->countMatchChild($db, '#__sportsmanagement_match_event', $sportTypeId)],
            'player_stats' => ['show_player_stats', fn() => $this->countMatchChild($db, '#__sportsmanagement_match_statistic', $sportTypeId)],
        ];

        foreach ($requested as $key => [$parameter, $callback]) {
            if (self::enabledFlag($params->get($parameter, 1))) {
                $counts[$key] = $callback();
            }
        }

        return [
            'sportstype' => $sportType,
            'counts' => $counts,
        ];
    }

    /**
     * Convert a configured sports icon into a validated browser URL.
     *
     * Accepts absolute HTTP(S) URLs without credentials or unescaped control
     * characters, and encodes relative file paths under the Joomla site root.
     * Rejects other schemes, traversal, encoded separators and URL fragments
     * so a corrupt icon setting cannot create an unsafe image source.
     *
     * @param string $path Absolute image URL or site-relative icon path.
     * @return string Safe image URL, or an empty string for invalid input.
     */
    public static function imageUrl(string $path): string
    {
        $path = trim($path);

        if ($path === '' || preg_match('/[\x00-\x1F\x7F]/', $path)
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
     * Count projects belonging to a single sports category.
     * Uses a bound sport-type identifier and returns zero for invalid IDs
     * instead of counting projects across unrelated sports.
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param int $sportTypeId Positive sports category identifier.
     * @return int Number of projects in that sports category.
     */
    private function countProjects(DatabaseInterface $db, int $sportTypeId): int
    {
        if ($sportTypeId <= 0) {
            return 0;
        }

        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('sports_type_id') . ' = :sportTypeId')
            ->bind(':sportTypeId', $sportTypeId, ParameterType::INTEGER);
        $db->setQuery($query);

        return self::nonNegativeCount($db->loadResult());
    }

    /**
     * Count distinct leagues or seasons referenced by the selected sport.
     *
     * The caller chooses only the trusted league_id or season_id project
     * column, and the query filters projects by a bound sport identifier.
     * Projects without a positive dimension ID do not contribute.
     *
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param string $field Trusted project league_id or season_id column.
     * @param int $sportTypeId Positive sports category identifier.
     * @return int Number of distinct referenced leagues or seasons.
     */
    private function countProjectDimension(DatabaseInterface $db, string $field, int $sportTypeId): int
    {
        if ($sportTypeId <= 0 || !in_array($field, ['league_id', 'season_id'], true)) {
            return 0;
        }

        $column = $db->quoteName('p.' . $field);
        $query = $db->createQuery()
            ->select('COUNT(DISTINCT ' . $column . ')')
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->where($db->quoteName('p.sports_type_id') . ' = :sportTypeId')
            ->where($column . ' > 0')
            ->bind(':sportTypeId', $sportTypeId, ParameterType::INTEGER);
        $db->setQuery($query);

        return self::nonNegativeCount($db->loadResult());
    }

    /**
     * Count distinct playgrounds used by projects in the selected sport.
     *
     * Reads both project-team standard venues and match-specific venues,
     * validates returned IDs, and deduplicates them across both sources.
     * A missing or invalid sport ID returns zero without issuing queries.
     *
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param int $sportTypeId Positive sports category identifier.
     * @return int Distinct venue count across project teams and matches.
     */
    private function countSportPlaygrounds(DatabaseInterface $db, int $sportTypeId): int
    {
        if ($sportTypeId <= 0) {
            return 0;
        }

        $grounds = [];

        // Project-team standard venues are the same association used by the
        // native playground ticker module.
        $query = $db->createQuery()
            ->select('DISTINCT ' . $db->quoteName('pg.id'))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_playground', 'pg')
                . ' ON ' . $db->quoteName('pg.id') . ' = ' . $db->quoteName('pt.standard_playground'))
            ->where($db->quoteName('p.sports_type_id') . ' = :sportTypeId')
            ->bind(':sportTypeId', $sportTypeId, ParameterType::INTEGER);
        $db->setQuery($query);

        $venueIds = $db->loadColumn();

        foreach (is_array($venueIds) ? $venueIds : [] as $rawId) {
            $id = self::nonNegativeCount($rawId);

            if ($id > 0) {
                $grounds[$id] = true;
            }
        }

        // Matches may take place at a different venue than the home team's
        // standard ground. Include those without counting overlaps twice.
        $query = $db->createQuery()
            ->select('DISTINCT ' . $db->quoteName('pg.id'))
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_playground', 'pg')
                . ' ON ' . $db->quoteName('pg.id') . ' = ' . $db->quoteName('m.playground_id'))
            ->where($db->quoteName('p.sports_type_id') . ' = :sportTypeId')
            ->bind(':sportTypeId', $sportTypeId, ParameterType::INTEGER);
        $db->setQuery($query);

        $venueIds = $db->loadColumn();

        foreach (is_array($venueIds) ? $venueIds : [] as $rawId) {
            $id = self::nonNegativeCount($rawId);

            if ($id > 0) {
                $grounds[$id] = true;
            }
        }

        return count($grounds);
    }

    /**
     * Count rows linked to projects of the selected sport.
     * Joins a trusted project-relation table to the projects dataset and
     * scopes the result by the bound sports category ID.
     * @param DatabaseInterface $db Selected database connection.
     * @param string $table Trusted project relation table name.
     * @param int $sportTypeId Sports category identifier.
     * @return int Related records within that sport.
     */
    private function countProjectRelation(DatabaseInterface $db, string $table, int $sportTypeId): int
    {
        if ($sportTypeId <= 0) {
            return 0;
        }

        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName($table, 'rel'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('rel.project_id'))
            ->where($db->quoteName('p.sports_type_id') . ' = :sportTypeId')
            ->bind(':sportTypeId', $sportTypeId, ParameterType::INTEGER);
        $db->setQuery($query);

        return self::nonNegativeCount($db->loadResult());
    }

    /**
     * Count clubs represented by teams participating in the selected sport.
     *
     * Traverses the project's season-team association back to the underlying
     * team and club, and counts distinct club IDs so repeat project
     * participation does not inflate the result.
     *
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param int $sportTypeId Positive sports category identifier.
     * @return int Number of participating clubs without duplicates.
     */
    private function countSportClubs(DatabaseInterface $db, int $sportTypeId): int
    {
        if ($sportTypeId <= 0) {
            return 0;
        }

        $query = $db->createQuery()
            ->select('COUNT(DISTINCT ' . $db->quoteName('t.club_id') . ')')
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->where($db->quoteName('p.sports_type_id') . ' = :sportTypeId')
            ->where($db->quoteName('t.club_id') . ' > 0')
            ->bind(':sportTypeId', $sportTypeId, ParameterType::INTEGER);
        $db->setQuery($query);

        return self::nonNegativeCount($db->loadResult());
    }

    /**
     * Count distinct players registered for projects in the selected sport.
     *
     * Joins project teams to their season-specific person rosters using
     * both team and season IDs. Filters active player-person relationships
     * by the sport and excludes unrelated seasons and non-player entries.
     *
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param int $sportTypeId Positive sports category identifier.
     * @return int Number of unique registered player persons.
     */
    private function countSportPlayers(DatabaseInterface $db, int $sportTypeId): int
    {
        if ($sportTypeId <= 0) {
            return 0;
        }

        $query = $db->createQuery()
            ->select('COUNT(DISTINCT ' . $db->quoteName('tp.person_id') . ')')
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp')
                . ' ON ' . $db->quoteName('tp.team_id') . ' = ' . $db->quoteName('st.team_id')
                . ' AND ' . $db->quoteName('tp.season_id') . ' = ' . $db->quoteName('st.season_id'))
            ->where($db->quoteName('p.sports_type_id') . ' = :sportTypeId')
            ->where($db->quoteName('tp.persontype') . ' = 1')
            ->where($db->quoteName('tp.person_id') . ' > 0')
            ->bind(':sportTypeId', $sportTypeId, ParameterType::INTEGER);
        $db->setQuery($query);

        return self::nonNegativeCount($db->loadResult());
    }

    /**
     * Count fixtures belonging to projects of a sports category.
     * Joins match rounds back to projects so the aggregation excludes
     * matches from other configured sports.
     * @param DatabaseInterface $db Selected database connection.
     * @param int $sportTypeId Sports category to count.
     * @return int Total matching fixture records.
     */
    private function countMatches(DatabaseInterface $db, int $sportTypeId): int
    {
        if ($sportTypeId <= 0) {
            return 0;
        }

        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id'))
            ->where($db->quoteName('p.sports_type_id') . ' = :sportTypeId')
            ->bind(':sportTypeId', $sportTypeId, ParameterType::INTEGER);
        $db->setQuery($query);

        return self::nonNegativeCount($db->loadResult());
    }

    /**
     * Count match-related detail rows within the selected sport.
     * Joins a trusted match-child table through fixtures and rounds to
     * sport-filtered projects, avoiding cross-sport aggregate leakage.
     * @param DatabaseInterface $db Selected database connection.
     * @param string $table Trusted match detail table.
     * @param int $sportTypeId Selected sport-type ID.
     * @return int Matching detail records.
     */
    private function countMatchChild(DatabaseInterface $db, string $table, int $sportTypeId): int
    {
        if ($sportTypeId <= 0) {
            return 0;
        }

        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName($table, 'child'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('child.match_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id'))
            ->where($db->quoteName('p.sports_type_id') . ' = :sportTypeId')
            ->bind(':sportTypeId', $sportTypeId, ParameterType::INTEGER);
        $db->setQuery($query);

        return self::nonNegativeCount($db->loadResult());
    }

    /**
     * Validate an untrusted SQL COUNT(*) result or referenced venue ID.
     *
     * Accepts complete nonnegative decimal identifiers, including leading
     * zeros. Nested records, partial numbers and overflows are never cast to
     * positive counts or inserted into the venue deduplication set.
     *
     * @param mixed $value Raw database count or playground identifier.
     * @return int Nonnegative count or ID; zero for invalid values.
     */
    private static function nonNegativeCount(mixed $value): int
    {
        return self::nonNegativeSelector($value) ?? 0;
    }

    /**
     * Parse a Joomla module show_* toggle without implicit casts.
     *
     * Supports true, 1 and zero-padded decimal strings representing 1.
     * Malformed nested and partial numeric settings fail closed.
     *
     * @param mixed $value Configured module display toggle.
     * @return bool Whether the statistic should be queried.
     */
    private static function enabledFlag(mixed $value): bool
    {
        if ($value === true || $value === 1) {
            return true;
        }

        return is_string($value) && strlen($value) <= 64
            && preg_match('/^0*1$/D', $value) === 1;
    }

    /**
     * Normalise a module selector or database count into a safe integer.
     *
     * Accepts nonnegative integers and complete decimal strings including
     * legacy zero-padded values. Rejects partial casts, booleans, arrays,
     * oversized input and integer overflow before those values reach queries.
     *
     * @param mixed $value Raw sport, database or count setting.
     * @return int|null Nonnegative selector, or null for invalid values.
     */
    private static function nonNegativeSelector(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }

        if (!is_string($value) || $value === '' || strlen($value) > 128
            || preg_match('/^[0-9]+$/D', $value) !== 1) {
            return null;
        }

        $digits = ltrim($value, '0');

        if ($digits === '') {
            return 0;
        }

        $maximum = (string) PHP_INT_MAX;

        if (strlen($digits) > strlen($maximum)
            || (strlen($digits) === strlen($maximum) && strcmp($digits, $maximum) > 0)) {
            return null;
        }

        return (int) $digits;
    }

    /**
     * Choose the configured SportsManagement database for statistics.
     * Uses the component's Joomla 5/6 resolver and the previously
     * validated database selector while retaining the Joomla fallback.
     * @param int $selector Validated database selection.
     * @param DatabaseInterface $fallbackDatabase Joomla database instance.
     * @return DatabaseInterface Effective statistics database.
     */
    private function database(int $selector, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve($fallbackDatabase, $selector);
    }
}
