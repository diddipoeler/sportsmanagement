<?php
namespace Diddipoeler\Module\SportsManagementRandomPlayer\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class RandomPlayerHelper
{
    public function getData(Registry $params, CMSApplicationInterface $app): array
    {
        $db = $this->database($params);
        $projectIds = $this->normaliseIds($params->get('p'));
        $teamIds = $this->normaliseIds($params->get('teams'));
        $seasonId = max(0, (int) $params->get('s', 0));

        if (!$projectIds || $seasonId <= 0) {
            return [];
        }

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('tp.id', 'teamplayer_id'),
                $db->quoteName('tp.person_id'),
                $db->quoteName('pt.id', 'projectteam_id'),
                $db->quoteName('pt.project_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id') . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tp.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
            ->where($db->quoteName('tp.season_id') . ' = ' . $seasonId)
            ->where($db->quoteName('st.season_id') . ' = ' . $seasonId)
            ->where($db->quoteName('tp.persontype') . ' = 1')
            ->where($db->quoteName('tp.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('pt.project_id') . ' IN (' . implode(',', $projectIds) . ')');

        if ($teamIds) {
            $query->where($db->quoteName('st.team_id') . ' IN (' . implode(',', $teamIds) . ')');
        }

        $db->setQuery($query);
        $candidates = $db->loadObjectList() ?: [];
        if (!$candidates) {
            return [];
        }

        $candidate = $candidates[random_int(0, count($candidates) - 1)];
        $query = $db->getQuery(true)
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
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id') . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tp.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'ps') . ' ON ' . $db->quoteName('ps.id') . ' = ' . $db->quoteName('tp.person_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('tp.project_position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_countries', 'co') . ' ON ' . $db->quoteName('co.alpha3') . ' = ' . $db->quoteName('ps.country'))
            ->where($db->quoteName('tp.id') . ' = ' . (int) $candidate->teamplayer_id)
            ->where($db->quoteName('pt.id') . ' = ' . (int) $candidate->projectteam_id)
            ->where($db->quoteName('pt.project_id') . ' = ' . (int) $candidate->project_id);
        $db->setQuery($query, 0, 1);
        $row = $db->loadObject();
        if (!$row) {
            return [];
        }

        $nameFormat = (int) $params->get('name_format', 0);
        $row->display_name = $this->formatName((string) $row->firstname, (string) $row->nickname, (string) $row->lastname, $nameFormat);
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
        $row->player_url = $this->route('player', $base + ['tid' => $row->team_slug, 'pid' => $row->person_slug]);
        $row->team_url = $this->route('teaminfo', $base + ['tid' => $row->team_slug, 'ptid' => (int) $row->projectteam_id]);

        return ['player' => $row];
    }

    private function route(string $view, array $parameters): string
    {
        return Route::_('index.php?' . http_build_query(['option' => 'com_sportsmanagement', 'view' => $view] + $parameters));
    }

    private function picture(string $path): string
    {
        if ($path !== '' && (preg_match('#^https?://#i', $path) || is_file(JPATH_ROOT . '/' . ltrim($path, '/')))) {
            return $this->mediaUrl($path);
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

    private function database(Registry $params): DatabaseInterface
    {
        if (!class_exists('sportsmanagementHelper')) {
            \JLoader::register('sportsmanagementHelper', JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php');
        }
        try {
            $db = \sportsmanagementHelper::getDBConnection(true, (int) $params->get('cfg_which_database', 0));
            if ($db instanceof DatabaseInterface) {
                return $db;
            }
        } catch (\Throwable) {
        }
        return Factory::getContainer()->get(DatabaseInterface::class);
    }
}
