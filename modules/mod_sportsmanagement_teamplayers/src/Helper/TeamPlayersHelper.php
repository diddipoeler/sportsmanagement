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
    /**
     * @return array{project:?object,players:array<int,object>,roster:array<int,array<int,object>>}
     */
    public function getData(Registry $params, DatabaseInterface $fallbackDatabase): array
    {
        $projectId = $this->positiveId($params->get('p', 0));
        $teamId = $this->positiveId($params->get('team', 0));

        if ($projectId <= 0 || $teamId <= 0) {
            return ['project' => null, 'players' => [], 'roster' => []];
        }

        $db = $this->database($params, $fallbackDatabase);
        $project = $this->project($db, $projectId, $teamId);

        if (!$project) {
            return ['project' => null, 'players' => [], 'roster' => []];
        }

        $componentParams = ComponentHelper::getParams('com_sportsmanagement');
        $rosterRows = $this->roster($db, $project);
        $players = [];
        $roster = [];
        $limit = max(1, (int) $params->get('limit', 24));
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
            $row->image_url = $this->imageUrl((string) ($row->picture ?: $row->ppic ?? ''));

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
     * Joomla selectors may return an integer or an ID:alias string. Reject
     * partial numeric casts, booleans, arrays and overflowing identifiers.
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

        $project->project_slug = (int) $project->id . ':' . (string) ($project->alias ?? '');
        $project->team_slug = (int) $project->team_id . ':' . (string) ($project->team_alias ?? '');

        return $project;
    }

    /** @return array<int,object> */
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

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        foreach ($rows as $row) {
            $row->person_slug = (int) ($row->pid ?? 0) . ':' . (string) ($row->person_alias ?? '');
        }

        return $rows;
    }

    /** @return array<int,int> */
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
        $db->setQuery($query);

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
        $db->setQuery($query);

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
        $db->setQuery($query);

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

    private function playerUrl(object $player, object $project, Registry $params): string
    {
        return SiteRouteHelper::view('player', [
            'cfg_which_database' => (int) $params->get('cfg_which_database', 0),
            's' => (int) $params->get('s', $project->season_id ?? 0),
            'p' => (string) ($project->project_slug ?? $project->id ?? ''),
            'tid' => (string) ($project->team_slug ?? $project->team_id ?? ''),
            'pid' => (string) ($player->person_slug ?? $player->pid ?? ''),
        ]);
    }

    private function formatName(object $player, int $format): string
    {
        $first = trim((string) ($player->firstname ?? ''));
        $nick = trim((string) ($player->nickname ?? ''));
        $last = trim((string) ($player->lastname ?? ''));
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

    private function flagHtml(object $row, Registry $componentParams): string
    {
        $alpha3 = strtoupper((string) ($row->country ?? ''));
        $alpha2 = strtolower((string) ($row->alpha2 ?? ''));

        // Database country values are not guaranteed to be valid ISO codes.
        $alpha3 = preg_match('/^[A-Z]{3}$/D', $alpha3) ? $alpha3 : '';
        $alpha2 = preg_match('/^[a-z]{2}$/D', $alpha2) ? $alpha2 : '';
        $label = htmlspecialchars(
            Text::_((string) ($row->country_name ?: $alpha3)),
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

        $path = $alpha2 !== ''
            ? 'images/com_sportsmanagement/database/flags/' . $alpha2 . '.png'
            : (string) ($row->country_picture ?? $componentParams->get('ph_flags', ''));
        $url = $this->imageUrl($path);

        return $url !== ''
            ? '<img class="jsm-teamplayers-flag" src="'
                . htmlspecialchars($url, ENT_QUOTES, 'UTF-8')
                . '" alt="' . $label . '" title="' . $label . '" />'
            : '';
    }

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

    private function database(Registry $params, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            (int) $params->get('cfg_which_database', 0)
        );
    }
}
