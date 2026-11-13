<?php
/**
 * Native Joomla 5/6 data and route helper for the events ranking module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementEventsRanking\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class EventsRankingHelper
{
    /**
     * Load event-type rankings for the configured sports projects.
     * Validates selected projects, events and optional constraints, retrieves summaries through a selected database and returns empty data on recoverable query failures.
     *
     * @param Registry $params Module project, event and display settings.
     * @param DatabaseInterface $fallbackDatabase Primary Joomla database driver.
     * @return array{project:?object,eventtypes:array,rankings:array} Data for the native module layout.
     */
    public function getData(Registry $params, DatabaseInterface $fallbackDatabase): array
    {
        $empty = ['project' => null, 'eventtypes' => [], 'rankings' => []];
        $selectedProjects = $params->get('p');
        $projectIds = $this->normaliseIds($selectedProjects);
        $selectedEvents = $params->get('evid');
        $eventIds = $this->normaliseIds($selectedEvents);

        // A corrupt selection of events must not silently render every
        // event type. No project selection likewise requires no database.
        if (!$projectIds
            || (self::hasSelection($selectedEvents) && !$eventIds)) {
            return $empty;
        }

        $divisionId = self::optionalId($params->get('division_id', 0));
        $matchId = self::optionalId($params->get('mid', 0));
        $teamId = self::optionalId($params->get('tid', 0));
        $limit = max(1, self::boundedLimit($params->get('limit', 5)));
        $rawDirection = $params->get('ranking_order', 'DESC');
        $primaryDirection = $this->direction(is_string($rawDirection) ? $rawDirection : 'DESC');

        // Reject malformed explicit constraints instead of broadening the
        // query to all divisions, matches or teams.
        foreach ([
            [$params->get('division_id', 0), $divisionId],
            [$params->get('mid', 0), $matchId],
            [$params->get('tid', 0), $teamId],
        ] as [$raw, $id]) {
            if ($id === 0 && self::hasSelection($raw)) {
                return $empty;
            }
        }

        try {
            $db = $this->database($params, $fallbackDatabase);
            $project = $this->getProject($db, $projectIds[0]);
            $eventTypes = $this->getEventTypes($db, $projectIds, $eventIds);
            $rankings = [];

            foreach ($eventTypes as $eventType) {
                $eventType->icon_url = $this->mediaUrl((string) ($eventType->icon ?? ''));
                $dart = $project && (string) $project->sport_type_name === 'COM_SPORTSMANAGEMENT_ST_DART';
                $rankings[(int) $eventType->id] = $this->getRanking(
                    $db,
                    (int) $eventType->id,
                    $projectIds,
                    $divisionId,
                    $teamId,
                    $matchId,
                    $limit,
                    $dart,
                    $primaryDirection,
                    $this->direction((string) ($eventType->directionscounter ?? 'DESC')),
                    max(1, min(2, (int) ($eventType->directionspointpos ?? 1))),
                    $params
                );
            }

            return [
                'project' => $project,
                'eventtypes' => $eventTypes,
                'rankings' => $rankings,
            ];
        } catch (\Throwable $e) {
            // Joomla pages must survive missing tables and offline optional
            // databases without disclosing SQL or server paths to visitors.
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::ERROR, 'com_sportsmanagement');

            return $empty;
        }
    }

    /**
     * Parse an optional positive identifier or Joomla ID:alias value.
     * Rejects malformed and non-scalar input so invalid user filters cannot silently broaden the ranking query.
     *
     * @param mixed $value Raw optional record selector.
     * @return int Valid positive ID or zero when absent or invalid.
     */
    private static function optionalId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $raw = trim((string) $value);

        if (!preg_match('/^([0-9]+)(?::[\pL\pN_-]+)?$/Du', $raw, $matches)) {
            return 0;
        }

        $digits = ltrim($matches[1], '0');

        if ($digits === '') {
            return 0;
        }

        $max = (string) PHP_INT_MAX;

        if (strlen($digits) > strlen($max)
            || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            return 0;
        }

        return (int) $digits;
    }

    /**
     * Determine whether a filter contains a meaningful explicit selection.
     * Distinguishes an unset or all-zero selection from malformed values that must not be treated as an unrestricted query.
     *
     * @param mixed $value Scalar or nested array selection.
     * @return bool Whether a selection was explicitly provided.
     */
    private static function hasSelection(mixed $value): bool
    {
        if ($value === null || $value === '' || $value === 0 || $value === '0') {
            return false;
        }

        if (is_string($value) && preg_match('/^0+$/D', trim($value)) === 1) {
            return false;
        }

        if (!is_array($value)) {
            return true;
        }

        foreach ($value as $selection) {
            if (self::hasSelection($selection)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fetch the selected project and sport type information for event calculations.
     * Uses Joomla's query builder to read project metadata and identify sport-specific ranking behaviour.
     *
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param int $projectId Project identifier.
     * @return object|null Project information or null if not found.
     */
    private function getProject(DatabaseInterface $db, int $projectId): ?object
    {
        $query = $db->createQuery()
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.name'),
                $db->quoteName('p.alias'),
                $db->quoteName('p.season_id'),
                $db->quoteName('p.sports_type_id'),
                $db->quoteName('st.name', 'sport_type_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_sports_type', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('p.sports_type_id'))
            ->where($db->quoteName('p.id') . ' = :eventsProjectId')
            ->bind(':eventsProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $project = $db->loadObject() ?: null;

        if ($project) {
            $project->slug = $project->id . ':' . $project->alias;
        }

        return $project;
    }

    /**
     * Find the event types used by the selected sports projects.
     * Uses integer-bound project and optional event ID filters and returns event metadata in configured display order.
     *
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param array<int,int> $projectIds Selected projects.
     * @param array<int,int> $eventIds Optional selected event types.
     * @return array<int,object> Matching event definitions.
     */
    private function getEventTypes(DatabaseInterface $db, array $projectIds, array $eventIds): array
    {
        if (!$projectIds) {
            return [];
        }

        $query = $db->createQuery()
            ->select('DISTINCT ' . implode(', ', [
                $db->quoteName('et.id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.alias'),
                $db->quoteName('et.icon'),
                $db->quoteName('et.directionspoint'),
                $db->quoteName('et.directionscounter'),
                $db->quoteName('et.directionspointpos'),
                $db->quoteName('et.ordering'),
            ]))
            ->from($db->quoteName('#__sportsmanagement_eventtype', 'et'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match_event', 'me') . ' ON ' . $db->quoteName('me.event_type_id') . ' = ' . $db->quoteName('et.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('me.match_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->whereIn($db->quoteName('r.project_id'), $projectIds, ParameterType::INTEGER)
            ->order($db->quoteName('et.ordering') . ' ASC');

        if ($eventIds) {
            $query->whereIn($db->quoteName('et.id'), $eventIds, ParameterType::INTEGER);
        }

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    /**
     * Calculate and enrich an event ranking for one event type.
     * Aggregates event totals with optional division, team and match filters, applies dart-specific ordering, then attaches safe player, team and image links.
     *
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param int $eventTypeId Event being ranked.
     * @param array<int,int> $projectIds Projects included in the ranking.
     * @param int $divisionId Optional division filter.
     * @param int $teamId Optional team filter.
     * @param int $matchId Optional match filter.
     * @param int $limit Maximum number of ranking rows.
     * @param bool $dart Whether dart-specific scoring is enabled.
     * @param string $primaryDirection Primary sort direction.
     * @param string $counterDirection Counter sort direction.
     * @param int $directionPointPosition Dart point/counter column selector.
     * @param Registry $params Module display settings.
     * @return array<int,object> Ordered and enriched ranking rows.
     */
    private function getRanking(
        DatabaseInterface $db,
        int $eventTypeId,
        array $projectIds,
        int $divisionId,
        int $teamId,
        int $matchId,
        int $limit,
        bool $dart,
        string $primaryDirection,
        string $counterDirection,
        int $directionPointPosition,
        Registry $params
    ): array {
        if (!$projectIds || $eventTypeId <= 0) {
            return [];
        }

        $query = $db->createQuery();
        if ($dart && $directionPointPosition === 2) {
            $query->select('me.event_sum AS zaehler, COUNT(me.event_sum) AS p');
        } elseif ($dart) {
            $query->select('me.event_sum AS p, COUNT(me.event_sum) AS zaehler');
        } else {
            $query->select('SUM(me.event_sum) AS p');
        }

        $query->select([
            $db->quoteName('pl.firstname', 'fname'),
            $db->quoteName('pl.nickname', 'nname'),
            $db->quoteName('pl.lastname', 'lname'),
            $db->quoteName('pl.country'),
            $db->quoteName('pl.id', 'pid'),
            $db->quoteName('pl.picture'),
            $db->quoteName('tp.picture', 'teamplayerpic'),
            $db->quoteName('t.id', 'tid'),
            $db->quoteName('t.name', 'team_name'),
            $db->quoteName('t.short_name'),
            $db->quoteName('t.middle_name'),
            $db->quoteName('t.picture', 'team_picture'),
            $db->quoteName('pt.id', 'projectteam_id'),
            $db->quoteName('c.id', 'club_id'),
            $db->quoteName('c.name', 'club_name'),
            $db->quoteName('c.logo_big'),
            $db->quoteName('co.picture', 'country_picture'),
            $db->quoteName('p.id', 'project_id'),
            $db->quoteName('p.alias', 'project_alias'),
            $db->quoteName('p.season_id'),
            $db->quoteName('pl.alias', 'person_alias'),
            $db->quoteName('t.alias', 'team_alias'),
            $db->quoteName('c.alias', 'club_alias'),
        ])
            ->from($db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $db->quoteName('tp.id') . ' = ' . $db->quoteName('me.teamplayer_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id') . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tp.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id') . ' AND ' . $db->quoteName('p.season_id') . ' = ' . $db->quoteName('st.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'pl') . ' ON ' . $db->quoteName('pl.id') . ' = ' . $db->quoteName('tp.person_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('me.match_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_countries', 'co') . ' ON ' . $db->quoteName('co.alpha3') . ' = ' . $db->quoteName('pl.country'))
            ->where($db->quoteName('me.event_type_id') . ' = :rankingEventTypeId')
            ->where($db->quoteName('pl.published') . ' = 1')
            ->whereIn($db->quoteName('pt.project_id'), $projectIds, ParameterType::INTEGER)
            ->whereIn($db->quoteName('p.id'), $projectIds, ParameterType::INTEGER)
            ->whereIn($db->quoteName('r.project_id'), $projectIds, ParameterType::INTEGER)
            ->bind(':rankingEventTypeId', $eventTypeId, ParameterType::INTEGER);

        if ($divisionId > 0) {
            $query->where($db->quoteName('pt.division_id') . ' = :rankingDivisionId')
                ->bind(':rankingDivisionId', $divisionId, ParameterType::INTEGER);
        }
        if ($teamId > 0) {
            $query->where($db->quoteName('st.team_id') . ' = :rankingTeamId')
                ->bind(':rankingTeamId', $teamId, ParameterType::INTEGER);
        }
        if ($matchId > 0) {
            $query->where($db->quoteName('me.match_id') . ' = :rankingMatchId')
                ->bind(':rankingMatchId', $matchId, ParameterType::INTEGER);
        }

        if ($dart) {
            $query->group([$db->quoteName('me.event_sum'), $db->quoteName('me.teamplayer_id')]);
            $query->order('p ' . $primaryDirection . ', zaehler ' . $counterDirection);
        } else {
            $query->group($db->quoteName('me.teamplayer_id'));
            $query->order('p ' . $primaryDirection);
        }

        $db->setQuery($query, 0, $limit);
        $rows = $db->loadObjectList() ?: [];
        $previousValue = null;
        $previousRank = 0;
        $cfg = self::databaseSelector($params->get('cfg_which_database', 0)) ?? 0;
        $nameFormat = self::boundedLimit($params->get('name_format', 0), 0, 50);
        $rawTeamNameType = $params->get('teamnametype', 'short_name');
        $teamNameType = is_string($rawTeamNameType) ? $rawTeamNameType : 'short_name';
        if (!in_array($teamNameType, ['name', 'short_name', 'middle_name'], true)) {
            $teamNameType = 'short_name';
        }

        foreach ($rows as $index => $row) {
            // Build Joomla ID:alias slugs in PHP, not with MySQL-only CONCAT_WS.
            $row->person_slug = (int) $row->pid . ':' . (string) ($row->person_alias ?? '');
            $row->team_slug = (int) $row->tid . ':' . (string) ($row->team_alias ?? '');
            $row->club_slug = (int) ($row->club_id ?? 0) > 0
                ? (int) $row->club_id . ':' . (string) ($row->club_alias ?? '')
                : '';
            $value = (string) $row->p;
            $row->rank = $previousValue !== null && $value === $previousValue ? $previousRank : $index + 1;
            $previousValue = $value;
            $previousRank = $row->rank;
            $row->display_name = $this->formatName((string) $row->fname, (string) $row->nname, (string) $row->lname, $nameFormat);
            $row->team_display_name = (string) ($row->{$teamNameType} ?: $row->team_name);
            $row->picture_url = $this->picture((string) ($row->teamplayerpic ?: $row->picture));
            $row->team_logo_url = $this->mediaUrl((string) $row->logo_big);
            $row->country_logo_url = $this->mediaUrl((string) $row->country_picture);
            $projectSlug = $row->project_id . ':' . $row->project_alias;
            $base = ['cfg_which_database' => $cfg, 's' => (int) $row->season_id, 'p' => $projectSlug];
            $row->player_url = $this->route('player', $base + ['tid' => $row->team_slug, 'pid' => $row->person_slug]);
            $rawTeamLink = $params->get('teamlink', '');
            $row->team_url = $this->teamRoute(is_string($rawTeamLink) ? $rawTeamLink : '', $base, $row);
        }

        return $rows;
    }

    /**
     * Resolve the configured link target for a ranked team.
     * Builds a native route for team information, roster, team plan or club details, returning an empty URL for disabled or unsupported targets.
     *
     * @param string $target Configured destination view.
     * @param array $base Shared project and database route variables.
     * @param object $row Ranking row with team and club slugs.
     * @return string Team or club page URL.
     */
    private function teamRoute(string $target, array $base, object $row): string
    {
        return match ($target) {
            'teaminfo' => $this->route('teaminfo', $base + ['tid' => $row->team_slug, 'ptid' => 0]),
            'roster' => $this->route('roster', $base + ['tid' => $row->team_slug, 'ptid' => 0, 'division' => 0]),
            'teamplan' => $this->route('teamplan', $base + ['tid' => $row->team_slug, 'ptid' => 0, 'division' => 0, 'mode' => 0]),
            'clubinfo' => $this->route('clubinfo', $base + ['cid' => $row->club_slug]),
            default => '',
        };
    }

    /**
     * Create a SportsManagement frontend URL using the native route helper.
     * Keeps all event ranking links consistent with Joomla's current menu and router configuration.
     *
     * @param string $view Component view to open.
     * @param array $parameters View-specific route parameters.
     * @return string Resolved frontend URL.
     */
    private function route(string $view, array $parameters): string
    {
        return SiteRouteHelper::view($view, $parameters);
    }

    /**
     * Build a publicly accessible portrait URL from a ranking image field.
     * Validates the media location, checks local paths against the Joomla document root and preserves eligible remote HTTP image URLs.
     *
     * @param string $path Original image path or URL.
     * @return string Valid image URL or an empty string.
     */
    private function picture(string $path): string
    {
        $url = $this->mediaUrl($path);

        if ($url !== '') {
            if (preg_match('~^https?://~i', trim($path))) {
                return $url;
            }

            $root = realpath(JPATH_ROOT);
            $local = $this->localMediaPath($path);
            $absolute = $root !== false && $local !== ''
                ? realpath($root . DIRECTORY_SEPARATOR . $local)
                : false;

            // Images must be actual files inside Joomla, not symlinks to
            // outside paths. Reuse the safe URL generated above.
            if ($root !== false
                && $absolute !== false
                && str_starts_with($absolute, $root . DIRECTORY_SEPARATOR)
                && is_file($absolute)
            ) {
                return $url;
            }
        }

        return $this->mediaUrl((string) ComponentHelper::getParams('com_sportsmanagement')->get('ph_player', ''));
    }

    /**
     * Normalise a ranking icon or logo path into a browser-ready URL.
     * Rejects malformed or unsafe URLs and converts permitted relative media paths through Joomla's configured root.
     *
     * @param string $path Original icon, country flag or club logo path.
     * @return string Safe media URL or an empty string.
     */
    private function mediaUrl(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return '';
        }

        if (preg_match('~^https?://~i', $path)) {
            if (preg_match('/[\x00-\x20\x7F]/', $path)
                || str_contains($path, '\\')
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

        $local = $this->localMediaPath($path);

        return $local === ''
            ? ''
            : rtrim((string) Uri::root(), '/') . '/' . $local;
    }

    /**
     * Validate a local image path before resolving it on the server.
     * Disallows traversal segments, control characters, protocols and backslashes to prevent unintended file access.
     *
     * @param string $path Candidate relative media path.
     * @return string Safe relative path or an empty string.
     */
    private function localMediaPath(string $path): string
    {
        $path = trim($path);

        if ($path === ''
            || preg_match('/[\x00-\x1F\x7F]/', $path)
            || str_contains($path, '\\')
            || str_starts_with($path, '//')
            || preg_match('~^[a-z][a-z0-9+.-]*:~i', $path)
            || preg_match('~(?:^|/)\.\.(?:/|$)~', $path)
        ) {
            return '';
        }

        return ltrim($path, '/');
    }

    /**
     * Format a player's name according to the event ranking display option.
     * Combines first name, optional nickname and surname in the historical order configured by the module.
     *
     * @param string $firstName Player's first name.
     * @param string $nickName Optional nickname.
     * @param string $lastName Player's surname.
     * @param int $format Configured naming scheme.
     * @return string Formatted display name.
     */
    private function formatName(string $firstName, string $nickName, string $lastName, int $format): string
    {
        $nick = $nickName !== '' ? "'{$nickName}'" : '';
        $parts = match ($format) {
            1 => [$lastName !== '' ? $lastName . ',' : '', $nick, $firstName],
            2 => [$lastName !== '' ? $lastName . ',' : '', $firstName, $nick],
            3 => [$firstName, $lastName],
            4 => [$lastName !== '' ? $lastName . ',' : '', $firstName],
            5 => [$nick !== '' ? $nick . ' -' : '', $firstName, $lastName],
            6 => [$nick !== '' ? $nick . ' -' : '', $lastName !== '' ? $lastName . ',' : '', $firstName],
            7 => [$firstName, $lastName, $nick !== '' ? '(' . $nickName . ')' : ''],
            default => [$firstName, $nick, $lastName],
        };

        return trim(implode(' ', array_values(array_filter($parts, static fn($value) => $value !== ''))));
    }

    /**
     * Extract safe positive numeric identifiers from module filters.
     * Accepts scalar or array select values, removes duplicates and discards malformed or overflowing numbers before query binding.
     *
     * @param mixed $value Raw ID selection.
     * @return array<int,int> Valid unique identifiers.
     */
    private function normaliseIds(mixed $value): array
    {
        if (!is_array($value) && !is_string($value) && !is_int($value)) {
            return [];
        }

        $values = is_array($value) ? $value : explode(',', (string) $value);
        $ids = [];

        foreach ($values as $candidate) {
            if (!is_string($candidate) && !is_int($candidate)) {
                continue;
            }

            // Joomla selectors may supply an ID or an ID:alias slug.
            // Reject partial matches such as "12garbage" and overflow.
            if (!preg_match('/^\s*([0-9]+)(?::[^,\s:]+)?\s*$/D', (string) $candidate, $match)) {
                continue;
            }

            $digits = ltrim($match[1], '0');
            if ($digits === '') {
                continue;
            }

            $id = filter_var($digits, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);
            if ($id !== false) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    /**
     * Restrict database ordering to an explicitly allowed direction.
     * Uses ASC only when requested and otherwise defaults to DESC, keeping SQL order clauses independent of raw input.
     *
     * @param string $value Requested sorting direction.
     * @return string ASC or DESC.
     */
    private function direction(string $value): string
    {
        return strtoupper($value) === 'ASC' ? 'ASC' : 'DESC';
    }

    /**
     * Validate a nonnegative SportsManagement database selector.
     *
     * Rejects structured, signed, fractional and overflowing values instead
     * of silently switching the ranking to an unrelated database connection.
     *
     * @param mixed $value Raw module database configuration.
     * @return int|null Valid nonnegative selector, or null if invalid.
     */
    private static function databaseSelector(mixed $value): ?int
    {
        if (!is_int($value) && (!is_string($value) || strlen($value) > 256
            || preg_match('/^[0-9]+$/D', $value) !== 1)) {
            return null;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $number === false ? null : $number;
    }

    /**
     * Clamp a numeric module limit without PHP 8 array-to-integer conversion.
     *
     * Accepts decimal strings and clamps oversized valid values to the limit,
     * while malformed values fall back to the configured default.
     *
     * @param mixed $value Raw Joomla module numeric option.
     * @param int $default Fallback when malformed.
     * @param int $maximum Maximum accepted value.
     * @return int Nonnegative bounded numeric option.
     */
    private static function boundedLimit(mixed $value, int $default = 5, int $maximum = 100): int
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
     * Resolve the SportsManagement connection selected for event ranking.
     * Delegates to the native database resolver with a fallback Joomla driver and configured database selector.
     *
     * @param Registry $params Module database configuration.
     * @param DatabaseInterface $fallbackDatabase Joomla's primary database.
     * @return DatabaseInterface Selected database driver.
     */
    private function database(Registry $params, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        $selector = self::databaseSelector($params->get('cfg_which_database', 0));

        if ($selector === null) {
            throw new \InvalidArgumentException('Invalid events ranking database selector.');
        }

        return SportsManagementDatabaseResolver::resolve($fallbackDatabase, $selector);
    }
}
