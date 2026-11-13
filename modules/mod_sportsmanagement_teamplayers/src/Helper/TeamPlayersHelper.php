<?php
/**
 * Native Joomla 5/6 data helper for the TeamPlayers module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementTeamPlayers\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class TeamPlayersHelper
{
    // Bound both the public module output and its intermediate SQL/aggregation
    // work. Large legacy rosters must not produce unbounded IN clauses.
    private const MAX_PLAYERS = 100;
    private const MAX_ROSTER_ROWS = 500;

    /**
     * Load a published project and its bounded player roster for the module.
     *
     * Validates project, team and database selectors; loads a roster capped at 500 rows; optionally aggregates game minutes; prepares player links, flags, photographs and a displayed list capped at 100 players.
     *
     * @param Registry $params Joomla module filters and display settings.
     * @param DatabaseInterface $fallbackDatabase Default Joomla connection.
     * @return array{project:?object,players:array<int,object>,roster:array<int,array<int,object>>} Project context, displayed players and roster grouped by position.
     */
    public function getData(Registry $params, DatabaseInterface $fallbackDatabase): array
    {
        $projectId = $this->positiveId($params->get('p', 0));
        $teamId = $this->positiveId($params->get('team', 0));
        $databaseSelector = $this->databaseSelector($params->get('cfg_which_database', 0));

        // Never query another database after a malformed module configuration.
        if ($projectId <= 0 || $teamId <= 0 || $databaseSelector === null) {
            return ['project' => null, 'players' => [], 'roster' => []];
        }

        $db = $this->database($databaseSelector, $fallbackDatabase);
        $project = $this->project($db, $projectId, $teamId);

        if (!$project) {
            return ['project' => null, 'players' => [], 'roster' => []];
        }

        $componentParams = ComponentHelper::getParams('com_sportsmanagement');
        $rosterRows = $this->roster($db, $project);
        $players = [];
        $roster = [];
        $limit = $this->displayLimit($params->get('limit', 24));
        $showMinutes = (int) $params->get('show_mins_played', 1) === 1;
        $minutesByPlayer = $showMinutes
            ? $this->minutesPlayedByPlayer(
                $db,
                array_map(static fn(object $row): int => (int) ($row->playerid ?? 0), $rosterRows),
                (int) ($project->game_regular_time ?? 0),
                $projectId
            )
            : [];

        foreach ($rosterRows as $row) {
            $row->display_name = $this->formatName($row, (int) $params->get('name_format', 0));
            $row->player_url = (int) $params->get('show_player_link', 1) === 1
                ? $this->playerUrl($row, $project, $params)
                : '';
            $row->flag_html = (int) $params->get('show_player_flag', 1) === 1
                ? $this->flagHtml($row, $componentParams)
                : '';
            $row->minutes_played = $showMinutes
                ? (int) ($minutesByPlayer[(int) $row->playerid] ?? 0)
                : 0;
            $picture = $row->picture !== '' ? $row->picture : $row->ppic;
            $row->image_url = $this->imageUrl($picture);

            $positionId = (int) ($row->position_id ?? 0);
            $roster[$positionId][] = $row;
            $players[] = $row;
        }

        usort($players, static function (object $a, object $b) use ($params): int {
            if ((int) $params->get('show_positions', 1) === 1) {
                $position = strcasecmp((string) ($b->position ?? ''), (string) ($a->position ?? ''));

                if ($position !== 0) {
                    return $position;
                }
            }

            $minutes = (int) ($b->minutes_played ?? 0) <=> (int) ($a->minutes_played ?? 0);

            if ($minutes !== 0) {
                return $minutes;
            }

            return strcasecmp((string) ($a->lastname ?? ''), (string) ($b->lastname ?? ''));
        });

        $players = array_slice($players, 0, $limit);

        return [
            'project' => $project,
            'players' => $players,
            'roster' => $roster,
        ];
    }

    /**
     * Parse a positive Joomla numeric ID or ID:alias selector safely.
     *
     * Rejects booleans, arrays, partial numbers and integer overflow; accepts zero-padded decimal IDs and safe legacy aliases.
     *
     * @param mixed $value Raw numeric or ID:alias selector.
     * @return int Positive ID, or zero when invalid.
     */
    private function positiveId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $raw = trim((string) $value);

        if (!preg_match('/^([0-9]+)(?::[^:\s,;]+)?$/D', $raw, $match)) {
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
     * Clamp the configured number of visible team players.
     *
     * Accepts nonnegative integer and decimal string configuration, including leading zeros, and returns a safe display bound between one and 100.
     *
     * @param mixed $raw Raw module limit setting.
     * @return int Maximum number of visible player entries.
     */
    private function displayLimit(mixed $raw): int
    {
        if (!is_int($raw) && !is_string($raw)) {
            return 24;
        }

        $value = trim((string) $raw);

        if (!preg_match('/^[0-9]+$/D', $value)) {
            return 24;
        }

        // Avoid overflow while retaining zero-padded legacy configuration.
        $digits = ltrim($value, '0');

        if ($digits === '') {
            return 1;
        }

        if (strlen($digits) > 3) {
            return self::MAX_PLAYERS;
        }

        return max(1, min(self::MAX_PLAYERS, (int) $digits));
    }

    /**
     * Load the published project/team context used by the module.
     *
     * Joins the project, project-team, season-team and team tables with bound identifiers and published-state checks, then builds portable Joomla route slugs.
     *
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param int $projectId Positive project ID.
     * @param int $teamId Positive team ID.
     * @return object|null Project/team context or null when not published.
     */
    private function project(DatabaseInterface $db, int $projectId, int $teamId): ?object
    {
        $query = $db->createQuery()
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.alias'),
                $db->quoteName('p.name'),
                $db->quoteName('p.season_id'),
                $db->quoteName('p.game_regular_time'),
                $db->quoteName('pt.id', 'projectteam_id'),
                $db->quoteName('st.id', 'season_team_id'),
                $db->quoteName('t.id', 'team_id'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('t.alias', 'team_alias'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.project_id') . ' = ' . $db->quoteName('p.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('p.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->where($db->quoteName('p.id') . ' = :projectId')
            ->where($db->quoteName('st.team_id') . ' = :teamId')
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('pt.published') . ' = 1')
            ->where($db->quoteName('st.published') . ' = 1')
            ->where($db->quoteName('t.published') . ' = 1')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':teamId', $teamId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $project = $db->loadObject();

        if (!$project) {
            return null;
        }

        $project->project_slug = $projectId . ':' . self::textValue($project->alias ?? null);
        $project->team_slug = $teamId . ':' . self::textValue($project->team_alias ?? null);

        return $project;
    }

    /**
     * Normalise the roster database response before constructing player links.
     *
     * Rejects non-object rows and players without valid roster/person IDs.
     * Normalises text fields to prevent warnings from malformed legacy data,
     * and creates safe person route slugs for retained players.
     *
     * @param mixed $rows Raw database result.
     * @return array<int, object> Valid roster records, sequentially indexed.
     */
    private function normaliseRosterRows(mixed $rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $players = [];

        foreach ($rows as $row) {
            if (!is_object($row)) {
                continue;
            }

            $playerId = $this->positiveId($row->playerid ?? null);
            $personId = $this->positiveId($row->pid ?? null);

            if ($playerId <= 0 || $personId <= 0) {
                continue;
            }

            $row->playerid = $playerId;
            $row->pid = $personId;

            foreach ([
                'firstname', 'nickname', 'lastname', 'person_alias',
                'country', 'alpha2', 'country_name', 'country_picture',
                'picture', 'ppic', 'position',
            ] as $field) {
                $row->{$field} = self::textValue($row->{$field} ?? null);
            }

            $row->position_id = $this->positiveId($row->position_id ?? null);
            $row->person_slug = $personId . ':' . $row->person_alias;
            $players[] = $row;
        }

        return $players;
    }

    /**
     * Read a legacy text value without converting arrays or objects to text.
     *
     * Refuses oversized legacy payloads before they reach roster templates,
     * links or sorting callbacks.
     *
     * @param mixed $value Raw database string field.
     * @return string Bounded value or empty text for invalid data.
     */
    private static function textValue(mixed $value): string
    {
        return is_string($value) && strlen($value) <= 4096 ? $value : '';
    }

    /**
     * Load the published players for a project team.
     *
     * Joins roster, person, project position and country data, bounds the SQL response and normalises malformed records before rendering.
     *
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param object $project Loaded project/team context.
     * @return array<int,object> Valid published player rows.
     */
    private function roster(DatabaseInterface $db, object $project): array
    {
        $projectId = (int) $project->id;
        $seasonId = (int) $project->season_id;
        $teamId = (int) $project->team_id;

        $query = $db->createQuery()
            ->select([
                $db->quoteName('pr.firstname'),
                $db->quoteName('pr.nickname'),
                $db->quoteName('pr.lastname'),
                $db->quoteName('pr.country'),
                $db->quoteName('pr.id', 'pid'),
                $db->quoteName('pr.picture', 'ppic'),
                $db->quoteName('tp.picture'),
                $db->quoteName('tp.id', 'playerid'),
                $db->quoteName('tp.jerseynumber', 'position_number'),
                $db->quoteName('ppos.position_id', 'position_id'),
                $db->quoteName('pos.name', 'position'),
                $db->quoteName('co.alpha2'),
                $db->quoteName('co.name', 'country_name'),
                $db->quoteName('co.picture', 'country_picture'),
                $db->quoteName('pr.alias', 'person_alias'),
            ])
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tp.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
                . ' AND ' . $db->quoteName('pt.project_id') . ' = :projectId')
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'pr') . ' ON ' . $db->quoteName('pr.id') . ' = ' . $db->quoteName('tp.person_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_person_project_position', 'perpos')
                . ' ON ' . $db->quoteName('perpos.project_id') . ' = :positionProjectId'
                . ' AND ' . $db->quoteName('perpos.person_id') . ' = ' . $db->quoteName('pr.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('perpos.project_position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_countries', 'co') . ' ON ' . $db->quoteName('co.alpha3') . ' = ' . $db->quoteName('pr.country'))
            ->where($db->quoteName('tp.persontype') . ' = 1')
            ->where($db->quoteName('tp.season_id') . ' = :seasonId')
            ->where($db->quoteName('tp.team_id') . ' = :teamId')
            ->where($db->quoteName('pr.show_on_frontend') . ' = 1')
            ->where($db->quoteName('pr.published') . ' = 1')
            ->where($db->quoteName('tp.published') . ' = 1')
            ->order([
                $db->quoteName('pos.ordering') . ' ASC',
                $db->quoteName('ppos.position_id') . ' ASC',
                $db->quoteName('tp.ordering') . ' ASC',
                $db->quoteName('tp.jerseynumber') . ' ASC',
                $db->quoteName('pr.lastname') . ' ASC',
                $db->quoteName('pr.firstname') . ' ASC',
            ])
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':positionProjectId', $projectId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER)
            ->bind(':teamId', $teamId, ParameterType::INTEGER);

        $db->setQuery($query, 0, self::MAX_ROSTER_ROWS);
        return $this->normaliseRosterRows($db->loadObjectList());
    }

    /**
     * Aggregate played minutes for the selected roster within one project.
     *
     * Runs bounded SQL counts for starters, substitutions and substituted-out players, then clamps calculated minutes to nonnegative totals.
     *
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param array<int,int> $playerIds Selected team-player roster IDs.
     * @param int $gameTime Length of a regular match in minutes.
     * @param int $projectId Positive project identifier.
     * @return array<int,int> Played minutes indexed by team-player ID.
     */
    private function minutesPlayedByPlayer(
        DatabaseInterface $db,
        array $playerIds,
        int $gameTime,
        int $projectId
    ): array {
        $playerIds = array_values(array_unique(array_filter(
            array_map('intval', $playerIds),
            static fn(int $id): bool => $id > 0
        )));

        if ($playerIds === [] || $gameTime <= 0 || $projectId <= 0) {
            return [];
        }

        $minutes = array_fill_keys($playerIds, 0);

        $query = $db->createQuery()
            ->select([
                $db->quoteName('mp.teamplayer_id', 'player_id'),
                'COUNT(DISTINCT ' . $db->quoteName('mp.match_id') . ') AS totalmatch',
            ])
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('mp.match_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->whereIn($db->quoteName('mp.teamplayer_id'), $playerIds, ParameterType::INTEGER)
            ->where($db->quoteName('mp.came_in') . ' = 0')
            ->where($db->quoteName('r.project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->group($db->quoteName('mp.teamplayer_id'));
        $db->setQuery($query, 0, self::MAX_ROSTER_ROWS);

        foreach ($db->loadObjectList() ?: [] as $row) {
            $playerId = (int) $row->player_id;
            $minutes[$playerId] = ($minutes[$playerId] ?? 0) + ((int) $row->totalmatch * $gameTime);
        }

        $query = $db->createQuery()
            ->select([
                $db->quoteName('mp.teamplayer_id', 'player_id'),
                'COUNT(DISTINCT ' . $db->quoteName('mp.match_id') . ') AS totalmatch',
                'COALESCE(SUM(' . $db->quoteName('mp.in_out_time') . '), 0) AS totalin',
            ])
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('mp.match_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->whereIn($db->quoteName('mp.teamplayer_id'), $playerIds, ParameterType::INTEGER)
            ->where($db->quoteName('mp.came_in') . ' = 1')
            ->where($db->quoteName('mp.in_for') . ' IS NOT NULL')
            ->where($db->quoteName('r.project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->group($db->quoteName('mp.teamplayer_id'));
        $db->setQuery($query, 0, self::MAX_ROSTER_ROWS);

        foreach ($db->loadObjectList() ?: [] as $row) {
            $playerId = (int) $row->player_id;
            $minutes[$playerId] = ($minutes[$playerId] ?? 0)
                + ((int) $row->totalmatch * $gameTime)
                - (int) $row->totalin;
        }

        $query = $db->createQuery()
            ->select([
                $db->quoteName('mp.in_for', 'player_id'),
                'COUNT(DISTINCT ' . $db->quoteName('mp.match_id') . ') AS totalmatch',
                'COALESCE(SUM(' . $db->quoteName('mp.in_out_time') . '), 0) AS totalout',
            ])
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('mp.match_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->whereIn($db->quoteName('mp.in_for'), $playerIds, ParameterType::INTEGER)
            ->where($db->quoteName('mp.came_in') . ' = 1')
            ->where($db->quoteName('r.project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->group($db->quoteName('mp.in_for'));
        $db->setQuery($query, 0, self::MAX_ROSTER_ROWS);

        foreach ($db->loadObjectList() ?: [] as $row) {
            $playerId = (int) $row->player_id;
            $minutes[$playerId] = ($minutes[$playerId] ?? 0)
                + (int) $row->totalout
                - ((int) $row->totalmatch * $gameTime);
        }

        foreach ($minutes as $playerId => $value) {
            $minutes[$playerId] = max(0, (int) $value);
        }

        return $minutes;
    }

    /**
     * Build a native Joomla route to a player's detail page.
     *
     * Includes the validated configured database, season, project, team and person identifiers in the component route.
     *
     * @param object $player Player record with prepared person slug.
     * @param object $project Project/team record with prepared slugs.
     * @param Registry $params Joomla module routing settings.
     * @return string Player page URL.
     */
    private function playerUrl(object $player, object $project, Registry $params): string
    {
        return SiteRouteHelper::view('player', [
            'cfg_which_database' => $this->databaseSelector($params->get('cfg_which_database', 0)) ?? 0,
            's' => $this->positiveId($params->get('s', $project->season_id ?? 0)),
            'p' => (string) ($project->project_slug ?? $project->id ?? ''),
            'tid' => (string) ($project->team_slug ?? $project->team_id ?? ''),
            'pid' => (string) ($player->person_slug ?? $player->pid ?? ''),
        ]);
    }

    /**
     * Format a team player's display name.
     *
     * Supports the module's legacy name format numbers, including nickname placement, initials and multi-line name layouts.
     *
     * @param object $player Player record containing first, last and nickname fields.
     * @param int $format Configured name presentation mode.
     * @return string Display name for the player.
     */
    private function formatName(object $player, int $format): string
    {
        $first = trim(self::textValue($player->firstname ?? null));
        $nick = trim(self::textValue($player->nickname ?? null));
        $last = trim(self::textValue($player->lastname ?? null));
        $quotedNick = $nick !== '' ? "'" . $nick . "'" : '';
        $parts = match ($format) {
            1 => [$last, $quotedNick, $first],
            2 => [$last, $first, $quotedNick],
            4 => [$last, $first],
            5 => [$quotedNick, $first, $last],
            6 => [$quotedNick, $last, $first],
            7 => [$first, $last, $quotedNick],
            10 => [$last],
            12 => [$nick],
            14 => [$last, $first],
            15 => [$last, $first],
            16 => [$first, $last],
            17 => [$last, $first, $quotedNick],
            18 => [$last, $first !== '' ? mb_substr($first, 0, 1) . '.' : ''],
            default => [$first, $quotedNick, $last],
        };

        $name = trim(implode(' ', array_values(array_filter($parts, static fn(string $value): bool => $value !== ''))));

        if ($format === 15 && $first !== '' && $last !== '') {
            return $last . "\n" . $first;
        }

        if ($format === 16 && $first !== '' && $last !== '') {
            return $first . "\n" . $last;
        }

        return $name;
    }

    /**
     * Prepare escaped country-flag markup for a roster player.
     *
     * Validates ISO country codes, uses the CSS flag collection or a safe media URL, and escapes country labels in HTML attributes.
     *
     * @param object $row Player and country details.
     * @param Registry $componentParams SportsManagement component settings.
     * @return string Safe country flag HTML or an empty string.
     */
    private function flagHtml(object $row, Registry $componentParams): string
    {
        $alpha3 = strtoupper(self::textValue($row->country ?? null));
        $alpha2 = strtolower(self::textValue($row->alpha2 ?? null));

        // Database country values are not guaranteed to be valid ISO codes.
        $alpha3 = preg_match('/^[A-Z]{3}$/D', $alpha3) ? $alpha3 : '';
        $alpha2 = preg_match('/^[a-z]{2}$/D', $alpha2) ? $alpha2 : '';
        $label = htmlspecialchars(
            Text::_(self::textValue($row->country_name ?? null) ?: $alpha3),
            ENT_QUOTES,
            'UTF-8'
        );

        if ((int) $componentParams->get('cfg_flags_css', 0) === 1) {
            $cssCode = match ($alpha3) {
                'WAL' => 'gb-wls',
                'SCO' => 'gb-sct',
                'GBR' => 'gb-eng',
                default => $alpha2,
            };

            return $cssCode !== ''
                ? '<span class="fi fi-' . $cssCode . '" title="' . $label . '"></span>'
                : '';
        }

        $picture = $row->country_picture ?? $componentParams->get('ph_flags', '');
        $path = $alpha2 !== ''
            ? 'images/com_sportsmanagement/database/flags/' . $alpha2 . '.png'
            : self::textValue($picture);
        $url = $this->imageUrl($path);

        return $url !== ''
            ? '<img class="jsm-teamplayers-flag" src="'
                . htmlspecialchars($url, ENT_QUOTES, 'UTF-8')
                . '" alt="' . $label . '" title="' . $label . '" />'
            : '';
    }

    /**
     * Normalise a player or country image into a safe site URL.
     *
     * Allows credential-free HTTPS/HTTP absolute URLs and encodes relative Joomla image paths; rejects traversal, encoded paths, non-HTTP schemes and control characters.
     *
     * @param string $path Raw configured image path or URL.
     * @return string Valid media URL or an empty string.
     */
    private function imageUrl(string $path): string
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

        $encodedPath = implode('/', array_map('rawurlencode', explode('/', ltrim($path, '/'))));

        return rtrim((string) Uri::root(), '/') . '/' . $encodedPath;
    }

    /**
     * Validate an external database selector without unsafe integer casts.
     *
     * Accepts a nonnegative integer or zero-padded decimal string within PHP's integer range and rejects malformed selectors.
     *
     * @param mixed $value Raw Joomla database selection.
     * @return int|null Valid selector or null when invalid.
     */
    private function databaseSelector(mixed $value): ?int
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

        $max = (string) PHP_INT_MAX;

        if (strlen($digits) > strlen($max)
            || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            return null;
        }

        return (int) $digits;
    }

    /**
     * Resolve the selected database for Team Players queries.
     *
     * Uses the central SportsManagement database resolver for a previously validated selector.
     *
     * @param int $selector Nonnegative database identifier.
     * @param DatabaseInterface $fallbackDatabase Default Joomla connection.
     * @return DatabaseInterface Selected database connection.
     */
    private function database(int $selector, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve($fallbackDatabase, $selector);
    }
}
