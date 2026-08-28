<?php
namespace Diddipoeler\Module\SportsManagementMatchesSlider\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class MatchesSliderHelper
{
    /** @return array<int,object> */
    public function getData(Registry $params, object $module, CMSApplicationInterface $app): array
    {
        $input = $app->getInput();
        $databaseMode = $input->getInt('cfg_which_database', (int) $params->get('cfg_which_database', 0));
        $seasonId = $input->getInt('s', (int) $params->get('s', 0));
        $requestProject = $input->getInt('p', 0);
        $projectIds = $requestProject > 0 ? [$requestProject] : $this->ids($params->get('p', []));

        if (!$projectIds) {
            return [];
        }

        $db = $this->database($databaseMode);
        $nameColumn = (string) $params->get('team_names', 'short_name');

        if (!in_array($nameColumn, ['name', 'short_name', 'middle_name'], true)) {
            $nameColumn = 'short_name';
        }

        $pictureType = (string) $params->get('picture_type', 'logo_big');
        $logoSelect = match ($pictureType) {
            'logo_small' => ['c1.logo_small AS logohome', 'c2.logo_small AS logoaway'],
            'logo_middle' => ['c1.logo_middle AS logohome', 'c2.logo_middle AS logoaway'],
            'team_picture' => ['pt1.picture AS logohome', 'pt2.picture AS logoaway'],
            'country' => ['co1.picture AS logohome', 'co2.picture AS logoaway'],
            default => ['c1.logo_big AS logohome', 'c2.logo_big AS logoaway'],
        };

        $query = $db->getQuery(true)
            ->select([
                'm.id AS match_id',
                'm.match_date',
                'm.match_timestamp',
                'm.match_number',
                'm.team1_result',
                'm.team2_result',
                'p.id AS project_id',
                'p.season_id',
                'r.id AS round_id',
                'st1.team_id AS team1_id',
                'st2.team_id AS team2_id',
                't1.' . $nameColumn . ' AS teamhome',
                't2.' . $nameColumn . ' AS teamaway',
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
                "CONCAT_WS(':', r.id, r.alias) AS round_slug",
                $logoSelect[0],
                $logoSelect[1],
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
            ->where('m.published = 1')
            ->where('p.published = 1')
            ->where('p.id IN (' . implode(',', $projectIds) . ')');

        if (!(int) $params->get('project_season', 1)) {
            $query->where('r.id = p.current_round');
        }

        $teams = $this->ids($params->get('teams', []));

        if ($teams) {
            $teamIds = implode(',', $teams);
            $query->where('(st1.team_id IN (' . $teamIds . ') OR st2.team_id IN (' . $teamIds . '))');
        }

        if ((int) $params->get('use_fav', 0) === 1) {
            $query->where(
                "(FIND_IN_SET(st1.team_id, REPLACE(COALESCE(p.fav_team, ''), ' ', '')) > 0"
                . " OR FIND_IN_SET(st2.team_id, REPLACE(COALESCE(p.fav_team, ''), ' ', '')) > 0)"
            );
        }

        $query->order('m.match_date ASC, m.match_number ASC');
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        $pictureServer = $this->pictureServer($databaseMode);

        foreach ($rows as $match) {
            $match->link = $this->matchLink($match, $params, $databaseMode, $seasonId);
            $match->home_logo_url = $this->mediaUrl((string) ($match->logohome ?? ''), $pictureServer);
            $match->away_logo_url = $this->mediaUrl((string) ($match->logoaway ?? ''), $pictureServer);
        }

        return $rows;
    }

    private function matchLink(object $match, Registry $params, int $databaseMode, int $seasonId): string
    {
        $view = match ((string) $params->get('p_link_func', 'results')) {
            'ranking' => 'ranking',
            'resultsrank' => 'resultsranking',
            default => 'results',
        };

        $query = [
            'cfg_which_database' => $databaseMode,
            's' => $seasonId > 0 ? $seasonId : (int) ($match->season_id ?? 0),
            'p' => (string) ($match->project_slug ?? $match->project_id),
            'r' => (string) ($match->round_slug ?? $match->round_id),
            'division' => 0,
        ];

        if ($view === 'ranking') {
            $query += ['type' => 0, 'from' => 0, 'to' => 0];
        } else {
            $query += ['mode' => 0, 'order' => '', 'layout' => ''];
        }

        return SiteRouteHelper::view($view, $query);
    }

    private function pictureServer(int $databaseMode): string
    {
        $componentParams = ComponentHelper::getParams('com_sportsmanagement');
        $server = $databaseMode > 0
            ? trim((string) $componentParams->get('cfg_which_database_server', ''))
            : (string) Uri::root();

        return rtrim($server !== '' ? $server : (string) Uri::root(), '/') . '/';
    }

    private function mediaUrl(string $path, string $pictureServer): string
    {
        $path = trim($path);

        if ($path === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return $pictureServer . ltrim($path, '/');
    }

    /** @return array<int,int> */
    private function ids(mixed $value): array
    {
        $values = is_array($value)
            ? $value
            : preg_split('/\s*,\s*/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
        $ids = [];

        foreach ((array) $values as $item) {
            if (preg_match('/^\s*(\d+)/', (string) $item, $match) && (int) $match[1] > 0) {
                $ids[(int) $match[1]] = (int) $match[1];
            }
        }

        return array_values($ids);
    }

    private function database(int $databaseMode): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(
            Factory::getContainer()->get(DatabaseInterface::class),
            $databaseMode
        );
    }
}
