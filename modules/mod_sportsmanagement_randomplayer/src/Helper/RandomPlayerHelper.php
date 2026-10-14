<?php
/**
 * Joomla 5/6 native data helper for the SportsManagement random player module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementRandomPlayer\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class RandomPlayerHelper
{
    public function getData(Registry $params, DatabaseInterface $fallbackDatabase): array
    {
        $projectIds = $this->normaliseIds($params->get('p'));
        $teamIds = $this->normaliseIds($params->get('teams'));
        $seasonId = self::positiveId($params->get('s', 0));

        // Invalid selectors must not open an optional/external database.
        if (!$projectIds || $seasonId <= 0) {
            return [];
        }

        $db = SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            (int) $params->get('cfg_which_database', 0)
        );
        $projectTeams = $this->getEligibleProjectTeams($db, $projectIds, $teamIds, $seasonId);

        if (!$projectTeams) {
            return [];
        }

        // A selected project team can have no eligible players. Try the
        // remaining teams before displaying an empty module.
        shuffle($projectTeams);
        $players = [];

        foreach ($projectTeams as $projectTeam) {
            $players = $this->getPlayersForProjectTeam(
                $db,
                (int) $projectTeam->projectteam_id,
                (int) $projectTeam->project_id,
                $seasonId
            );

            if ($players) {
                break;
            }
        }

        if (!$players) {
            return [];
        }

        $row = $players[random_int(0, count($players) - 1)];
        $nameFormat = (int) $params->get('name_format', 0);
        $row->display_name = $this->formatName(
            (string) $row->firstname,
            (string) $row->nickname,
            (string) $row->lastname,
            $nameFormat
        );
        $row->project_slug = $row->project_id . ':' . $row->project_alias;
        $row->person_slug = $row->person_id . ':' . $row->person_alias;
        $row->team_slug = $row->team_id . ':' . $row->team_alias;
        $row->picture_url = $this->picture((string) ($row->teamplayer_picture ?: $row->person_picture));
        $row->team_picture_url = $this->mediaUrl((string) $row->team_picture);
        $row->flag_url = $this->mediaUrl((string) $row->country_picture);

        $base = [
            'cfg_which_database' => (int) $params->get('cfg_which_database', 0),
            's' => (int) $row->season_id,
            'p' => $row->project_slug,
        ];
        $row->player_url = $this->route(
            'player',
            $base + ['tid' => $row->team_slug, 'pid' => $row->person_slug]
        );
        $row->team_url = $this->route(
            'teaminfo',
            $base + ['tid' => $row->team_slug, 'ptid' => (int) $row->projectteam_id]
        );

        return ['player' => $row];
    }

    private function getEligibleProjectTeams(
        DatabaseInterface $db,
        array $projectIds,
        array $teamIds,
        int $seasonId
    ): array {
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pt.id', 'projectteam_id'),
                $db->quoteName('pt.project_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp')
                . ' ON ' . $db->quoteName('tp.team_id') . ' = ' . $db->quoteName('st.team_id')
                . ' AND ' . $db->quoteName('tp.season_id') . ' = ' . $db->quoteName('st.season_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id')
                . ' AND ' . $db->quoteName('p.season_id') . ' = ' . $db->quoteName('st.season_id')
            )
            ->whereIn($db->quoteName('pt.project_id'), $projectIds, ParameterType::INTEGER)
            ->where($db->quoteName('st.season_id') . ' = :eligibleSeason')
            ->where($db->quoteName('tp.season_id') . ' = :eligiblePlayerSeason')
            ->where($db->quoteName('tp.persontype') . ' = 1')
            ->where($db->quoteName('tp.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->group([
                $db->quoteName('pt.id'),
                $db->quoteName('pt.project_id'),
            ])
            ->bind(':eligibleSeason', $seasonId, ParameterType::INTEGER)
            ->bind(':eligiblePlayerSeason', $seasonId, ParameterType::INTEGER);

        if ($teamIds) {
            $query->whereIn($db->quoteName('st.team_id'), $teamIds, ParameterType::INTEGER);
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    private function getPlayersForProjectTeam(
        DatabaseInterface $db,
        int $projectTeamId,
        int $projectId,
        int $seasonId
    ): array {
        $query = $db->createQuery()
            ->select([
                $db->quoteName('p.id', 'project_id'),
                $db->quoteName('p.name', 'project_name'),
                $db->quoteName('p.alias', 'project_alias'),
                $db->quoteName('p.season_id'),
                $db->quoteName('ps.id', 'person_id'),
                $db->quoteName('ps.firstname'),
                $db->quoteName('ps.nickname'),
                $db->quoteName('ps.lastname'),
                $db->quoteName('ps.alias', 'person_alias'),
                $db->quoteName('ps.country'),
                $db->quoteName('ps.picture', 'person_picture'),
                $db->quoteName('tp.picture', 'teamplayer_picture'),
                $db->quoteName('pos.name', 'position_name'),
                $db->quoteName('t.id', 'team_id'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('t.short_name'),
                $db->quoteName('t.middle_name'),
                $db->quoteName('t.alias', 'team_alias'),
                $db->quoteName('t.picture', 'team_picture'),
                $db->quoteName('pt.id', 'projectteam_id'),
                $db->quoteName('co.picture', 'country_picture'),
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
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id')
                . ' AND ' . $db->quoteName('p.season_id') . ' = ' . $db->quoteName('st.season_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_person', 'ps')
                . ' ON ' . $db->quoteName('ps.id') . ' = ' . $db->quoteName('tp.person_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('tp.project_position_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_position', 'pos')
                . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_countries', 'co')
                . ' ON ' . $db->quoteName('co.alpha3') . ' = ' . $db->quoteName('ps.country')
            )
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('ps.published') . ' = 1')
            ->where($db->quoteName('pt.id') . ' = :projectTeamId')
            ->where($db->quoteName('pt.project_id') . ' = :projectId')
            ->where($db->quoteName('tp.season_id') . ' = :playerSeasonId')
            ->where($db->quoteName('st.season_id') . ' = :teamSeasonId')
            ->where($db->quoteName('tp.persontype') . ' = 1')
            ->where($db->quoteName('tp.published') . ' = 1')
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER)
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':playerSeasonId', $seasonId, ParameterType::INTEGER)
            ->bind(':teamSeasonId', $seasonId, ParameterType::INTEGER);
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    private function route(string $view, array $parameters): string
    {
        return SiteRouteHelper::view($view, $parameters);
    }

    private function picture(string $path): string
    {
        $url = $this->mediaUrl($path);

        // Never inspect arbitrary paths outside JPATH_ROOT. The URL
        // validation also rejects unexpected schemes and traversal.
        if ($url !== '' && (preg_match('#^https?://#i', $path)
            || is_file(JPATH_ROOT . '/' . ltrim(trim($path), '/')))) {
            return $url;
        }

        return $this->mediaUrl((string) ComponentHelper::getParams('com_sportsmanagement')->get('ph_player', ''));
    }

    private function mediaUrl(string $path): string
    {
        $path = trim($path);

        if ($path === '' || str_starts_with($path, '//')
            || preg_match('/[\x00-\x1F\x7F\\\\]/', $path)) {
            return '';
        }

        if (preg_match('#^https?://#i', $path)) {
            $parts = parse_url($path);

            return is_array($parts) && !empty($parts['host'])
                && !isset($parts['user']) && !isset($parts['pass'])
                ? $path : '';
        }

        // Reject custom URI schemes and traversal even for local images.
        // Permit a single leading slash for existing Joomla media paths.
        $relativePath = ltrim($path, '/');

        if (preg_match('~^[a-z][a-z0-9+.-]*:~i', $relativePath)
            || preg_match('~(?:^|/)\.\.?(?:/|$)~', $relativePath)
            || preg_match('~%(?:2e|2f|5c)~i', $relativePath)) {
            return '';
        }

        return rtrim((string) Uri::root(), '/') . '/' . $relativePath;
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

    /** Parse a single positive ID or Joomla ID:alias without prefix casts. */
    private static function positiveId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $raw = trim((string) $value);

        if (!preg_match('/^([0-9]+)(?::[\pL\pN_-]+)?$/Du', $raw, $parts)) {
            return 0;
        }

        $digits = ltrim($parts[1], '0');
        $digits = $digits === '' ? '0' : $digits;
        $max = (string) PHP_INT_MAX;

        if ($digits === '0'
            || strlen($digits) > strlen($max)
            || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            return 0;
        }

        return (int) $digits;
    }

    private function normaliseIds(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];
        $ids = [];

        // Both scalar and multiselect parameters may contain delimited IDs.
        $candidates = [];
        foreach ($values as $entry) {
            if (!is_string($entry) && !is_int($entry)) {
                continue;
            }
            foreach (preg_split('/[\s,;]+/', (string) $entry, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $candidate) {
                $candidates[] = $candidate;
            }
        }

        foreach ($candidates as $candidate) {
            if (is_scalar($candidate) && preg_match('/^\s*(\d+)(?::[A-Za-z0-9_-]+)?\s*$/D', (string) $candidate, $match)) {
                // Casting an overflowing ID can silently clamp it to PHP_INT_MAX.
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
        }

        return array_values($ids);
    }
}
