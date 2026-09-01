<?php
namespace Diddipoeler\Module\SportsManagementClubicons\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\RankingEngine;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class ClubiconsHelper
{
    private const PLACEHOLDERS = [
        'logo_big' => 'images/com_sportsmanagement/database/placeholders/placeholder_150.png',
        'projectteam_picture' => 'images/com_sportsmanagement/database/placeholders/placeholder_450_2.png',
        'team_picture' => 'images/com_sportsmanagement/database/placeholders/placeholder_450_2.png',
    ];

    public function getData(Registry $params, object $module, CMSApplicationInterface $app): array
    {
        $projectId = $this->projectId($params, $app);
        if ($projectId <= 0) {
            return ['project' => null, 'ranking' => [], 'teams' => []];
        }

        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);
        $db = $this->database($params, $joomlaDatabase);
        $divisionId = $this->firstId($params->get('division_id', 0));
        $result = (new RankingEngine($db))->calculate($projectId, $divisionId);
        $project = $result['project'];
        $ranking = $result['ranking'];

        if (empty($project->id) || !$ranking) {
            return ['project' => $project ?: null, 'ranking' => [], 'teams' => []];
        }

        $websites = $this->websiteMap($db, $ranking);
        $teams = [];
        foreach ($ranking as $row) {
            $team = $row->team;
            if (!$team || $row->projectteamid <= 0) {
                continue;
            }

            $teams[$row->projectteamid] = [
                'name' => (string) ($team->name ?? ''),
                'link' => $this->teamLink($params, $project, $team, $websites),
                'logo_url' => $this->logoUrl($params, $team),
            ];
        }

        return [
            'project' => $project,
            'ranking' => $ranking,
            'teams' => $teams,
        ];
    }

    private function projectId(Registry $params, CMSApplicationInterface $app): int
    {
        $input = $app->getInput();
        if (
            (int) $params->get('usepfromcomponent', 0) === 1
            && $input->getCmd('option', '') === 'com_sportsmanagement'
            && $input->getInt('p', 0) > 0
        ) {
            return $input->getInt('p', 0);
        }

        return $this->firstId($params->get('project_ids', 0));
    }

    private function firstId(mixed $value): int
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        return preg_match('/^\s*(\d+)/', (string) $value, $match) ? (int) $match[1] : 0;
    }

    private function teamLink(Registry $params, object $project, object $team, array $websites): string
    {
        $teamLink = (int) $params->get('teamlink', 0);
        if ($teamLink === 0) {
            return '';
        }

        if ($teamLink === 5) {
            $teamId = (int) ($team->id ?? 0);
            return (string) ($websites[$teamId] ?? '');
        }

        $common = [
            'cfg_which_database' => (int) $params->get('cfg_which_database', 0),
            's' => (int) $params->get('s', 0),
            'p' => (string) ($project->slug ?? $project->id ?? ''),
        ];

        return match ($teamLink) {
            1 => SiteRouteHelper::view('teaminfo', $common + [
                'tid' => (string) ($team->team_slug ?? $team->id ?? ''),
                'ptid' => 0,
            ]),
            2 => SiteRouteHelper::view('roster', $common + [
                'tid' => (string) ($team->team_slug ?? $team->id ?? ''),
                'ptid' => 0,
            ]),
            3 => SiteRouteHelper::view('teamplan', $common + [
                'tid' => (string) ($team->team_slug ?? $team->id ?? ''),
                'division' => 0,
                'mode' => 0,
                'ptid' => 0,
            ]),
            4 => SiteRouteHelper::view('clubinfo', $common + [
                'cid' => (string) ($team->club_slug ?? $team->club_id ?? ''),
            ]),
            default => '',
        };
    }

    private function logoUrl(Registry $params, object $team): string
    {
        $type = (string) $params->get('logotype', 'logo_big');
        if (!array_key_exists($type, self::PLACEHOLDERS)) {
            $type = 'logo_big';
        }

        $path = trim((string) ($team->{$type} ?? ''));
        if ($path === '') {
            $path = self::PLACEHOLDERS[$type];
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        if ((int) $params->get('cfg_which_database', 0) === 1) {
            $server = rtrim((string) ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database_server', ''), '/');
            if ($server !== '') {
                return $server . '/' . ltrim($path, '/');
            }
        }

        return rtrim((string) Uri::root(), '/') . '/' . ltrim($path, '/');
    }

    private function websiteMap(DatabaseInterface $db, array $ranking): array
    {
        $teamIds = [];
        foreach ($ranking as $row) {
            if ((int) ($row->teamid ?? 0) > 0) {
                $teamIds[] = (int) $row->teamid;
            }
        }
        $teamIds = array_values(array_unique($teamIds));
        if (!$teamIds) {
            return [];
        }

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('t.id'),
                $db->quoteName('t.website', 'team_www'),
                $db->quoteName('c.website', 'club_www'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->where($db->quoteName('t.id') . ' IN (' . implode(',', $teamIds) . ')');
        $db->setQuery($query);

        $map = [];
        foreach ($db->loadObjectList() ?: [] as $row) {
            $url = trim((string) ($row->club_www ?? '')) ?: trim((string) ($row->team_www ?? ''));
            if ($url !== '') {
                $map[(int) $row->id] = $url;
            }
        }

        return $map;
    }

    private function database(Registry $params, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            (int) $params->get('cfg_which_database', 0)
        );
    }
}
