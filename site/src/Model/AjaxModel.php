<?php
/**
 * Native Joomla 5/6 data model for frontend JSON endpoints.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;

/**
 * Native Joomla 5/6 data model for frontend JSON endpoints.
 */
final class AjaxModel extends BaseDatabaseModel
{
    private const NAVIGATION_VIEWS = [
        'allprojectrounds',
        'calendar',
        'clubinfo',
        'clubplan',
        'curve',
        'eventsranking',
        'jlxmlexports',
        'jltournamenttree',
        'leaguechampionoverview',
        'matrix',
        'ranking',
        'rankingalltime',
        'rankingmatrix',
        'referees',
        'results',
        'resultsmatrix',
        'resultsranking',
        'resultsrankingmatrix',
        'roster',
        'rosteralltime',
        'stats',
        'statsranking',
        'statsrankingteams',
        'teaminfo',
        'teamplan',
        'teams',
        'teamstats',
        'teamstree',
        'tournamentbracket',
        'treetonode',
    ];

    public function getLink(
        string $view = '',
        int $projectId = 0,
        int $teamId = 0,
        int $divisionId = 0,
        string $alltimePoints = '3,1,0',
        int $treeNodeId = 0
    ): string {
        $view = strtolower(trim($view));
        $projectId = max(0, $projectId);
        $teamId = max(0, $teamId);
        $divisionId = max(0, $divisionId);

        if ($projectId === 0 || !in_array($view, self::NAVIGATION_VIEWS, true)) {
            return '';
        }

        $project = $this->getProjectNavigationContext($projectId);

        if ($project === null) {
            return '';
        }

        $team = $teamId > 0 ? $this->getTeamNavigationContext($teamId) : null;
        $base = [
            'cfg_which_database' => $this->databaseSelector(),
            's' => $project->season_slug,
            'p' => $project->project_slug,
        ];

        switch ($view) {
            case 'calendar':
                return SiteRouteHelper::view('calendar', $base + [
                    'tid' => $teamId,
                    'division' => 0,
                    'mode' => 0,
                    'ptid' => 0,
                ]);

            case 'curve':
                return SiteRouteHelper::view('curve', $base + [
                    'tid1' => $team?->team_slug ?? 0,
                    'tid2' => 0,
                    'division' => $divisionId,
                ]);

            case 'eventsranking':
                return SiteRouteHelper::view('eventsranking', $base + [
                    'division' => $divisionId,
                    'tid' => $teamId,
                    'evid' => 0,
                    'mid' => 0,
                ]);

            case 'matrix':
            case 'referees':
                return SiteRouteHelper::view($view, $base + [
                    'division' => $divisionId,
                    'r' => 0,
                ]);

            case 'results':
            case 'allprojectrounds':
                return SiteRouteHelper::view($view, $base + [
                    'r' => $project->round_slug,
                    'division' => $divisionId,
                    'mode' => 0,
                    'order' => '',
                    'layout' => '',
                ]);

            case 'resultsmatrix':
            case 'resultsranking':
                return SiteRouteHelper::view($view, $base + [
                    'r' => $project->round_slug,
                    'division' => $divisionId,
                    'mode' => 0,
                    'order' => 0,
                    'layout' => 0,
                ]);

            case 'rankingmatrix':
                return SiteRouteHelper::view('rankingmatrix', $base + [
                    'division' => $divisionId,
                    'r' => $project->round_slug,
                ]);

            case 'rankingalltime':
                return SiteRouteHelper::view('rankingalltime', [
                    'cfg_which_database' => $base['cfg_which_database'],
                    'l' => (int) $project->league_id,
                    'points' => trim($alltimePoints) !== '' ? $alltimePoints : '3,1,0',
                    'type' => 0,
                    'order' => 0,
                    'dir' => 0,
                    's' => 0,
                    'p' => $project->project_slug,
                ]);

            case 'leaguechampionoverview':
                return SiteRouteHelper::view('leaguechampionoverview', [
                    'cfg_which_database' => $base['cfg_which_database'],
                    'l' => (int) $project->league_id,
                    's' => 0,
                    'p' => $project->project_slug,
                ]);

            case 'resultsrankingmatrix':
                return SiteRouteHelper::view('resultsrankingmatrix', $base + [
                    'r' => $project->round_slug,
                    'division' => $divisionId,
                ]);

            case 'roster':
                if ($team === null) {
                    return '';
                }

                return SiteRouteHelper::view('roster', $base + [
                    'tid' => $team->team_slug,
                    'ptid' => 0,
                ]);

            case 'rosteralltime':
                if ($team === null) {
                    return '';
                }

                return SiteRouteHelper::view('rosteralltime', $base + [
                    'tid' => $team->team_slug,
                ]);

            case 'stats':
                return SiteRouteHelper::view('stats', $base + [
                    'division' => $divisionId,
                ]);

            case 'statsranking':
            case 'statsrankingteams':
                return SiteRouteHelper::view($view, $base + [
                    'division' => $divisionId,
                    'tid' => $teamId,
                ]);

            case 'teaminfo':
                if ($team === null) {
                    return '';
                }

                return SiteRouteHelper::view('teaminfo', $base + [
                    'tid' => $team->team_slug,
                    'ptid' => 0,
                ]);

            case 'teamplan':
                if ($teamId <= 0) {
                    return '';
                }

                return SiteRouteHelper::view('teamplan', $base + [
                    'tid' => $teamId,
                    'division' => 0,
                    'mode' => 0,
                    'ptid' => 0,
                ]);

            case 'clubinfo':
            case 'clubplan':
                if ($team === null || $team->club_slug === '') {
                    return '';
                }

                return SiteRouteHelper::view($view, $base + [
                    'cid' => $team->club_slug,
                ]);

            case 'teamstats':
                if ($teamId <= 0) {
                    return '';
                }

                return SiteRouteHelper::view('teamstats', $base + [
                    'tid' => $teamId,
                    'ptid' => 0,
                    'division' => 0,
                ]);

            case 'teams':
            case 'teamstree':
                return SiteRouteHelper::view($view, $base + [
                    'division' => $divisionId,
                ]);

            case 'treetonode':
                return SiteRouteHelper::view('treetonode', $base + [
                    'tnid' => max(0, $treeNodeId),
                ]);

            case 'jltournamenttree':
            case 'tournamentbracket':
                return SiteRouteHelper::view($view, $base + [
                    'r' => $project->round_slug,
                ]);

            case 'ranking':
            case 'jlxmlexports':
            default:
                return SiteRouteHelper::view($view, $base + [
                    'type' => 0,
                    'r' => $project->round_slug,
                    'from' => 0,
                    'to' => 0,
                    'division' => $divisionId,
                ]);
        }
    }

    public function getProjectTeams(int $projectId): array
    {
        $db = $this->sportsDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('t.id', 'value'),
                $db->quoteName('t.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
            )
            ->where($db->quoteName('pt.project_id') . ' = ' . max(0, $projectId))
            ->group([$db->quoteName('t.id'), $db->quoteName('t.name')])
            ->order($db->quoteName('t.name') . ' ASC');

        $db->setQuery($query);

        return $this->withPrompt($db->loadObjectList(), '-- Team selektieren --', '-- keine Teams -- ');
    }

    public function getProjectSelect(int $leagueId): array
    {
        $db = $this->sportsDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('p.id', 'value'),
                $db->quoteName('p.name', 'text'),
                $db->quoteName('p.project_type'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season', 's')
                . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id')
            )
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('p.league_id') . ' = ' . max(0, $leagueId))
            ->order($db->quoteName('s.name') . ' DESC')
            ->order($db->quoteName('p.name') . ' ASC');

        $db->setQuery($query);

        return $this->withPrompt($db->loadObjectList(), '-- Projekt selektieren --', '-- keine Projekte -- ');
    }

    public function getAssocLeagueSelect(string $country, int $associationId): array
    {
        $db = $this->sportsDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('l.id', 'value'),
                $db->quoteName('l.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_league', 'l'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.league_id') . ' = ' . $db->quoteName('l.id')
            )
            ->where($db->quoteName('l.country') . ' = ' . $db->quote($country))
            ->where($db->quoteName('l.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->group([$db->quoteName('l.id'), $db->quoteName('l.name')])
            ->order($db->quoteName('l.name') . ' ASC');

        if ($associationId > 0) {
            $query->where($db->quoteName('l.associations') . ' = ' . $associationId);
        }

        $db->setQuery($query);

        return $this->withPrompt($db->loadObjectList(), '-- Liga selektieren --', '-- keine Ligen -- ');
    }

    public function getCountrySubSubAssocSelect(int $subAssociationId): array
    {
        return $this->getAssociationOptions(
            max(0, $subAssociationId),
            null,
            '-- Kreisverbände -- ',
            '-- keine Kreisverbände -- '
        );
    }

    public function getCountrySubAssocSelect(int $associationId): array
    {
        return $this->getAssociationOptions(
            max(0, $associationId),
            null,
            '-- Landesverbände -- ',
            '-- keine Landesverbände -- '
        );
    }

    public function getCountryAssocSelect(string $country): array
    {
        return $this->getAssociationOptions(
            0,
            $country,
            '-- Regionalverbände -- ',
            '-- keine Regionalverbände -- '
        );
    }

    /**
     * Return published projects in the option shape expected by the navigation module.
     */
    public function getProjectsOptions(int $seasonId = 0, int $leagueId = 0, int $ordering = 0): array
    {
        $db = $this->sportsDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('p.id', 'id'),
                $db->quoteName('p.name', 'name'),
                $db->quoteName('s.name', 'season_name'),
                $db->quoteName('l.name', 'league_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_league', 'l')
                . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season', 's')
                . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id')
            )
            ->where($db->quoteName('p.published') . ' = 1');

        if ($seasonId > 0) {
            $query->where($db->quoteName('p.season_id') . ' = ' . $seasonId);
        }

        if ($leagueId > 0) {
            $query->where($db->quoteName('p.league_id') . ' = ' . $leagueId);
        }

        switch ($ordering) {
            case 1:
                $query->order($db->quoteName('p.ordering') . ' DESC');
                break;

            case 2:
                $query->order($db->quoteName('s.ordering') . ' ASC')
                    ->order($db->quoteName('l.ordering') . ' ASC')
                    ->order($db->quoteName('p.ordering') . ' ASC');
                break;

            case 3:
                $query->order($db->quoteName('s.ordering') . ' DESC')
                    ->order($db->quoteName('l.ordering') . ' DESC')
                    ->order($db->quoteName('p.ordering') . ' DESC');
                break;

            case 4:
                $query->order($db->quoteName('p.name') . ' ASC');
                break;

            case 5:
                $query->order($db->quoteName('p.name') . ' DESC');
                break;

            default:
                $query->order($db->quoteName('p.ordering') . ' ASC');
                break;
        }

        $db->setQuery($query);
        $projects = $db->loadObjectList();

        return array_map(
            static fn ($project): object => (object) [
                'value' => (int) $project->id,
                'text' => (string) $project->name,
                'season_name' => (string) $project->season_name,
                'league_name' => (string) $project->league_name,
            ],
            $projects
        );
    }

    private function getAssociationOptions(
        int $parentId,
        ?string $country,
        string $prompt,
        string $emptyPrompt
    ): array {
        $db = $this->sportsDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('s.id', 'value'),
                $db->quoteName('s.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_associations', 's'))
            ->where($db->quoteName('s.parent_id') . ' = ' . $parentId)
            ->where($db->quoteName('s.published') . ' = 1')
            ->order($db->quoteName('s.name') . ' ASC');

        if ($country !== null) {
            $query->where($db->quoteName('s.country') . ' = ' . $db->quote($country));
        }

        $db->setQuery($query);

        return $this->withPrompt($db->loadObjectList(), $prompt, $emptyPrompt);
    }

    private function getProjectNavigationContext(int $projectId): ?object
    {
        $db = $this->sportsDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.alias'),
                $db->quoteName('p.season_id'),
                $db->quoteName('p.current_round'),
                $db->quoteName('p.league_id'),
                $db->quoteName('p.project_type'),
                $db->quoteName('s.alias', 'season_alias'),
                $db->quoteName('l.alias', 'league_alias'),
                $db->quoteName('r.alias', 'round_alias'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season', 's')
                . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_league', 'l')
                . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('p.current_round')
            )
            ->where($db->quoteName('p.id') . ' = ' . max(0, $projectId));

        $db->setQuery($query, 0, 1);
        $project = $db->loadObject();

        if (!$project) {
            return null;
        }

        $project->project_slug = $this->slug((int) $project->id, (string) $project->alias);
        $project->season_slug = $this->slug((int) $project->season_id, (string) $project->season_alias);
        $project->league_slug = $this->slug((int) $project->league_id, (string) $project->league_alias);
        $project->round_slug = (int) $project->current_round > 0
            ? $this->slug((int) $project->current_round, (string) ($project->round_alias ?? ''))
            : 0;

        return $project;
    }

    private function getTeamNavigationContext(int $teamId): ?object
    {
        $db = $this->sportsDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('t.id'),
                $db->quoteName('t.alias'),
                $db->quoteName('t.club_id'),
                $db->quoteName('c.alias', 'club_alias'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_club', 'c')
                . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id')
            )
            ->where($db->quoteName('t.id') . ' = ' . max(0, $teamId));

        $db->setQuery($query, 0, 1);
        $team = $db->loadObject();

        if (!$team) {
            return null;
        }

        $team->team_slug = $this->slug((int) $team->id, (string) $team->alias);
        $team->club_slug = (int) $team->club_id > 0
            ? $this->slug((int) $team->club_id, (string) ($team->club_alias ?? ''))
            : '';

        return $team;
    }

    private function sportsDatabase(): DatabaseInterface
    {
        $joomlaDatabase = $this->getDatabase();

        return SportsManagementDatabaseResolver::resolve($joomlaDatabase, $this->databaseSelector());
    }

    private function databaseSelector(): int
    {
        /** @var SiteApplication $app */
        $app = Factory::getContainer()->get(SiteApplication::class);

        return $app->getInput()->getInt(
            'cfg_which_database',
            (int) ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0)
        );
    }

    private function slug(int $id, string $alias): int|string
    {
        $alias = trim($alias);

        return $alias !== '' ? $id . ':' . $alias : $id;
    }

    private function withPrompt(array $rows, string $prompt, string $emptyPrompt): array
    {
        return array_merge([
            (object) [
                'value' => 0,
                'text' => $rows === [] ? $emptyPrompt : $prompt,
            ],
        ], $rows);
    }
}
