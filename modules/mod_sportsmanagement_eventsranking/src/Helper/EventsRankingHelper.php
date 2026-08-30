<?php
namespace Diddipoeler\Module\SportsManagementEventsRanking\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class EventsRankingHelper
{
    public function getData(Registry $params, CMSApplicationInterface $app, DatabaseInterface $fallbackDatabase): array
    {
        $db = $this->database($params, $fallbackDatabase);
        $projectIds = $this->normaliseIds($params->get('p'));
        $eventIds = $this->normaliseIds($params->get('evid'));
        $divisionId = max(0, (int) $params->get('division_id', 0));
        $matchId = max(0, (int) $params->get('mid', 0));
        $teamId = max(0, (int) $params->get('tid', 0));
        $limit = max(1, min(100, (int) $params->get('limit', 5)));
        $primaryDirection = $this->direction((string) $params->get('ranking_order', 'DESC'));

        $project = $projectIds ? $this->getProject($db, $projectIds[0]) : null;
        $eventTypes = $this->getEventTypes($db, $projectIds, $eventIds);
        $rankings = [];

        foreach ($eventTypes as $eventType) {
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
    }

    private function getProject(DatabaseInterface $db, int $projectId): ?object
    {
        $query = $db->getQuery(true)
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
            ->where($db->quoteName('p.id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);
        $project = $db->loadObject() ?: null;

        if ($project) {
            $project->slug = $project->id . ':' . $project->alias;
        }

        return $project;
    }

    private function getEventTypes(DatabaseInterface $db, array $projectIds, array $eventIds): array
    {
        if (!$projectIds) {
            return [];
        }

        $query = $db->getQuery(true)
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
            ->where($db->quoteName('r.project_id') . ' IN (' . implode(',', $projectIds) . ')')
            ->order($db->quoteName('et.ordering') . ' ASC');

        if ($eventIds) {
            $query->where($db->quoteName('et.id') . ' IN (' . implode(',', $eventIds) . ')');
        }

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

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

        $query = $db->getQuery(true);
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
            'CONCAT_WS(\':\', ' . $db->quoteName('pl.id') . ', ' . $db->quoteName('pl.alias') . ') AS ' . $db->quoteName('person_slug'),
            'CONCAT_WS(\':\', ' . $db->quoteName('t.id') . ', ' . $db->quoteName('t.alias') . ') AS ' . $db->quoteName('team_slug'),
            'CONCAT_WS(\':\', ' . $db->quoteName('c.id') . ', ' . $db->quoteName('c.alias') . ') AS ' . $db->quoteName('club_slug'),
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
            ->where($db->quoteName('me.event_type_id') . ' = ' . $eventTypeId)
            ->where($db->quoteName('pl.published') . ' = 1')
            ->where($db->quoteName('pt.project_id') . ' IN (' . implode(',', $projectIds) . ')')
            ->where($db->quoteName('p.id') . ' IN (' . implode(',', $projectIds) . ')')
            ->where($db->quoteName('r.project_id') . ' IN (' . implode(',', $projectIds) . ')');

        if ($divisionId > 0) {
            $query->where($db->quoteName('pt.division_id') . ' = ' . $divisionId);
        }
        if ($teamId > 0) {
            $query->where($db->quoteName('st.team_id') . ' = ' . $teamId);
        }
        if ($matchId > 0) {
            $query->where($db->quoteName('me.match_id') . ' = ' . $matchId);
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
        $cfg = (int) $params->get('cfg_which_database', 0);
        $nameFormat = (int) $params->get('name_format', 0);
        $teamNameType = (string) $params->get('teamnametype', 'short_name');
        if (!in_array($teamNameType, ['name', 'short_name', 'middle_name'], true)) {
            $teamNameType = 'short_name';
        }

        foreach ($rows as $index => $row) {
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
            $row->team_url = $this->teamRoute((string) $params->get('teamlink', ''), $base, $row);
        }

        return $rows;
    }

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

    private function route(string $view, array $parameters): string
    {
        return Route::_('index.php?' . http_build_query(['option' => 'com_sportsmanagement', 'view' => $view] + $parameters));
    }

    private function picture(string $path): string
    {
        if ($path !== '') {
            if (preg_match('#^https?://#i', $path)) {
                return $path;
            }
            if (is_file(JPATH_ROOT . '/' . ltrim($path, '/'))) {
                return $this->mediaUrl($path);
            }
        }

        return $this->mediaUrl((string) ComponentHelper::getParams('com_sportsmanagement')->get('ph_player', ''));
    }

    private function mediaUrl(string $path): string
    {
        if ($path === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return rtrim((string) Uri::root(), '/') . '/' . ltrim($path, '/');
    }

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

    private function normaliseIds(mixed $value): array
    {
        $values = is_array($value) ? $value : preg_split('/\s*,\s*/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
        $ids = [];
        foreach ((array) $values as $candidate) {
            if (is_scalar($candidate) && preg_match('/^\s*(\d+)/', (string) $candidate, $match)) {
                $id = (int) $match[1];
                if ($id > 0) {
                    $ids[$id] = $id;
                }
            }
        }
        return array_values($ids);
    }

    private function direction(string $value): string
    {
        return strtoupper($value) === 'ASC' ? 'ASC' : 'DESC';
    }

    private function database(Registry $params, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            (int) $params->get('cfg_which_database', 0)
        );
    }
}
