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
    public function getData(Registry $params, DatabaseInterface $fallbackDatabase): array
    {
        $sportTypeId = self::nonNegativeSelector($params->get('sportstypes', 0));
        $databaseSelector = self::nonNegativeSelector($params->get('cfg_which_database', 0));

        // A malformed selector must never select an unrelated sport or DB.
        if ($sportTypeId === null || $databaseSelector === null) {
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
            $sportType = $db->loadObject() ?: null;

            if ($sportType !== null) {
                $sportType->icon_url = self::imageUrl((string) ($sportType->icon ?? ''));
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
            if ((int) $params->get($parameter, 1) === 1) {
                $counts[$key] = (int) $callback();
            }
        }

        return [
            'sportstype' => $sportType,
            'counts' => $counts,
        ];
    }

    /**
     * Convert configured sports icons to safe URLs. Absolute HTTP(S) icons
     * must not be prefixed with the Joomla root URL.
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

        return (int) $db->loadResult();
    }

    /**
     * League and season totals must describe the chosen sport, not every
     * league or season stored for unrelated sports. The field name is selected
     * exclusively from the trusted call sites above.
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

        return (int) $db->loadResult();
    }

    /**
     * Count existing venues connected to projects of the selected sport.
     * A ground can be both a team's standard venue and a match venue, so
     * collect distinct IDs from both relationships before counting.
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

        foreach ($db->loadColumn() ?: [] as $id) {
            if ((int) $id > 0) {
                $grounds[(int) $id] = true;
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

        foreach ($db->loadColumn() ?: [] as $id) {
            if ((int) $id > 0) {
                $grounds[(int) $id] = true;
            }
        }

        return count($grounds);
    }

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

        return (int) $db->loadResult();
    }

    /**
     * Count distinct clubs that have at least one team assigned to a project
     * of this sport. Project-team rows reference season-team IDs, not team IDs.
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

        return (int) $db->loadResult();
    }

    /**
     * Count unique players participating in the selected sport. The roster
     * table identifies the team by team_id and season_id, so both fields must
     * match the project team season to avoid counting players from other years.
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

        return (int) $db->loadResult();
    }

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

        return (int) $db->loadResult();
    }

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

        return (int) $db->loadResult();
    }

    /**
     * Accept non-negative decimal IDs, including legacy zero-padded values,
     * but reject partial casts, booleans, arrays and integer overflow.
     */
    private static function nonNegativeSelector(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }

        if (!is_string($value) || !preg_match('/^[0-9]+$/D', $value)) {
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

    private function database(int $selector, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve($fallbackDatabase, $selector);
    }
}
