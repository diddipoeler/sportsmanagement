<?php
/**
 * Joomla 5/6 native helper for the SportsManagement matches slider module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementMatchesSlider\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class MatchesSliderHelper
{
    /** @return array<int,object> */
    public function getData(
        Registry $params,
        object $module,
        CMSApplicationInterface $app,
        ?DatabaseInterface $joomlaDatabase = null
    ): array {
        if (!$app instanceof SiteApplication || !$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Matches Slider requires the Joomla site application.', 500);
        }

        $input = $app->getInput();
        $databaseMode = $input->getInt('cfg_which_database', (int) $params->get('cfg_which_database', 0));
        $seasonId = $input->getInt('s', (int) $params->get('s', 0));
        $requestProject = $input->getInt('p', 0);
        $projectIds = $requestProject > 0 ? [$requestProject] : $this->ids($params->get('p', []));

        if (!$projectIds) {
            return [];
        }

        $joomlaDatabase ??= $app->getContainer()->get(DatabaseInterface::class);
        $db = $this->database($databaseMode, $joomlaDatabase);
        $nameColumn = (string) $params->get('team_names', 'short_name');

        if (!in_array($nameColumn, ['name', 'short_name', 'middle_name'], true)) {
            $nameColumn = 'short_name';
        }

        $pictureType = (string) $params->get('picture_type', 'logo_big');
        $logoSelect = match ($pictureType) {
            'logo_small' => [
                $db->quoteName('c1.logo_small', 'logohome'),
                $db->quoteName('c2.logo_small', 'logoaway'),
            ],
            'logo_middle' => [
                $db->quoteName('c1.logo_middle', 'logohome'),
                $db->quoteName('c2.logo_middle', 'logoaway'),
            ],
            'team_picture' => [
                $db->quoteName('pt1.picture', 'logohome'),
                $db->quoteName('pt2.picture', 'logoaway'),
            ],
            'country' => [
                $db->quoteName('co1.picture', 'logohome'),
                $db->quoteName('co2.picture', 'logoaway'),
            ],
            default => [
                $db->quoteName('c1.logo_big', 'logohome'),
                $db->quoteName('c2.logo_big', 'logoaway'),
            ],
        };

        $query = $db->createQuery()
            ->select([
                $db->quoteName('m.id', 'match_id'),
                $db->quoteName('m.match_date'),
                $db->quoteName('m.match_timestamp'),
                $db->quoteName('m.match_number'),
                $db->quoteName('m.team1_result'),
                $db->quoteName('m.team2_result'),
                $db->quoteName('p.id', 'project_id'),
                $db->quoteName('p.season_id'),
                $db->quoteName('r.id', 'round_id'),
                $db->quoteName('st1.team_id', 'team1_id'),
                $db->quoteName('st2.team_id', 'team2_id'),
                $db->quoteName('t1.' . $nameColumn, 'teamhome'),
                $db->quoteName('t2.' . $nameColumn, 'teamaway'),
                "CONCAT_WS(':', " . $db->quoteName('p.id') . ', ' . $db->quoteName('p.alias') . ') AS ' . $db->quoteName('project_slug'),
                "CONCAT_WS(':', " . $db->quoteName('r.id') . ', ' . $db->quoteName('r.alias') . ') AS ' . $db->quoteName('round_slug'),
                $logoSelect[0],
                $logoSelect[1],
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_team', 'pt1')
                . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_team', 'pt2')
                . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st1')
                . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st2')
                . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_team', 't1')
                . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_team', 't2')
                . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_club', 'c1')
                . ' ON ' . $db->quoteName('c1.id') . ' = ' . $db->quoteName('t1.club_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_club', 'c2')
                . ' ON ' . $db->quoteName('c2.id') . ' = ' . $db->quoteName('t2.club_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_countries', 'co1')
                . ' ON ' . $db->quoteName('co1.alpha3') . ' = ' . $db->quoteName('c1.country')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_countries', 'co2')
                . ' ON ' . $db->quoteName('co2.alpha3') . ' = ' . $db->quoteName('c2.country')
            )
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->whereIn($db->quoteName('p.id'), $projectIds, ParameterType::INTEGER);

        if (!(int) $params->get('project_season', 1)) {
            $query->where($db->quoteName('r.id') . ' = ' . $db->quoteName('p.current_round'));
        }

        $teams = $this->ids($params->get('teams', []));

        if ($teams) {
            $homeTeamPlaceholders = $query->bindArray($teams, ParameterType::INTEGER);
            $awayTeamPlaceholders = $query->bindArray($teams, ParameterType::INTEGER);
            $query->where(
                '(' . $db->quoteName('st1.team_id') . ' IN (' . implode(',', $homeTeamPlaceholders) . ')'
                . ' OR ' . $db->quoteName('st2.team_id') . ' IN (' . implode(',', $awayTeamPlaceholders) . '))'
            );
        }

        if ((int) $params->get('use_fav', 0) === 1) {
            $query->where(
                '(FIND_IN_SET(' . $db->quoteName('st1.team_id')
                . ', REPLACE(COALESCE(' . $db->quoteName('p.fav_team') . ", ''), ' ', '')) > 0"
                . ' OR FIND_IN_SET(' . $db->quoteName('st2.team_id')
                . ', REPLACE(COALESCE(' . $db->quoteName('p.fav_team') . ", ''), ' ', '')) > 0)"
            );
        }

        $query->order([
            $db->quoteName('m.match_date') . ' ASC',
            $db->quoteName('m.match_number') . ' ASC',
        ]);
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

    private function database(int $databaseMode, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            $databaseMode
        );
    }
}
