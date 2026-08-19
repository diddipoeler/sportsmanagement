<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\ProjectRelationService;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Native Joomla 5/6 list and compatibility model for project teams.
 *
 * The historic public helper methods are intentionally retained because project,
 * match and modal views still call them directly.
 */
final class ProjectteamsModel extends SportsManagementListModel
{
    public static int $_project_id = 0;
    public static int $_division_id = 0;
    public static array $_pro_teams_in_used = [];

    public string $_identifier = 'pteams';
    public int $_season_id = 0;
    public int $project_art_id = 0;
    public int $sports_type_id = 0;

    private ?ProjectRelationService $relations = null;
    private ?object $project = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            't.name', 'name', 't.lastname', 'ppl.lastname', 'lastname',
            'tl.admin', 'pt.admin', 'd.name', 'division', 'obj.country', 'c.country', 'country',
            'tl.picture', 'pt.picture', 'tl.matches_finally', 'pt.matches_finally', 'matches_finally',
            'pt.start_points', 'start_points', 'pt.penalty_points', 'penalty_points',
            'pt.is_in_score', 'is_in_score', 'pt.use_finally', 'use_finally',
            'pt.published', 'published', 'state', 'tl.id', 'pt.id', 'id',
            'st.team_id', 'team_id', 'st.id', 'season_team_id',
            'tl.ordering', 'pt.ordering', 't.ordering',
        ];
        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 't.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = Factory::getApplication();
        $input = $app->getInput();
        $projectId = $input->getInt('pid', 0) ?: (int) $app->getUserState('com_sportsmanagement.pid', 0);
        $divisionId = $input->getInt('division', 0);

        self::$_project_id = $projectId;
        self::$_division_id = $divisionId;
        $this->setState('filter.pid', $projectId);

        if ($projectId > 0) {
            $app->setUserState('com_sportsmanagement.pid', $projectId);
        }

        foreach ([
            'search_nation' => 'filter_search_nation',
            'search_division' => 'filter_search_division',
            'playground_id' => 'filter_playground_id',
            'is_in_score' => 'filter_is_in_score',
            'use_finally' => 'filter_use_finally',
        ] as $state => $request) {
            if ((string) $this->getState('filter.' . $state) === '') {
                $value = $input->getString($request, '');

                if ($value !== '') {
                    $this->setState('filter.' . $state, $value);
                }
            }
        }

        $project = $this->getProjectContext();

        if ($project) {
            $this->_season_id = (int) $project->season_id;
            $this->project_art_id = (int) $project->project_art_id;
            $this->sports_type_id = (int) $project->sports_type_id;
            $app->setUserState('com_sportsmanagement.season_id', $this->_season_id);
            $app->setUserState('com_sportsmanagement.project_art_id', $this->project_art_id);
            $app->setUserState('com_sportsmanagement.sports_type_id', $this->sports_type_id);

            if ($this->project_art_id === 3 && (string) $this->getState('list.ordering') === 't.name') {
                $this->setState('list.ordering', 'ppl.lastname');
            }
        }
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $project = $this->getProjectContext();
        $projectId = (int) ($project->id ?? 0);
        $seasonId = (int) ($project->season_id ?? 0);
        $individual = (int) ($project->project_art_id ?? 0) === 3;

        $query = $db->getQuery(true)
            ->select('pt.*')
            ->select([
                $db->quoteName('pt.id', 'projectteamid'),
                $db->quoteName('pt.team_id', 'season_relation_id'),
                $db->quoteName('u.name', 'editor'),
                $db->quoteName('u.email', 'email'),
                $db->quoteName('u.email', 'admin_email'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u')
                . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('pt.admin')
            )
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId);

        if ($individual) {
            $query->select([
                $db->quoteName('sp.id', 'season_team_id'),
                $db->quoteName('sp.person_id', 'team_id'),
                "CONCAT_WS(', ', ppl.lastname, ppl.firstname) AS " . $db->quoteName('name'),
                $db->quoteName('ppl.lastname'),
                $db->quoteName('ppl.firstname'),
                $db->quoteName('ppl.notes'),
                $db->quoteName('se.name', 'seasonname'),
                '0 AS ' . $db->quoteName('playercount'),
                '0 AS ' . $db->quoteName('staffcount'),
                '0 AS ' . $db->quoteName('club_id'),
                "'' AS " . $db->quoteName('clubname'),
                "'' AS " . $db->quoteName('club_email'),
                "'' AS " . $db->quoteName('club_logo'),
                "'' AS " . $db->quoteName('country'),
                "'' AS " . $db->quoteName('latitude'),
                "'' AS " . $db->quoteName('longitude'),
                "'' AS " . $db->quoteName('location'),
                "'' AS " . $db->quoteName('address'),
                "'' AS " . $db->quoteName('zipcode'),
                "'' AS " . $db->quoteName('founded_year'),
                "'' AS " . $db->quoteName('unique_id'),
                "'' AS " . $db->quoteName('division_name'),
                "'' AS " . $db->quoteName('playground_name'),
                "'' AS " . $db->quoteName('playground_picture'),
            ])
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_season_person_id', 'sp')
                    . ' ON ' . $db->quoteName('sp.id') . ' = ' . $db->quoteName('pt.team_id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_person', 'ppl')
                    . ' ON ' . $db->quoteName('ppl.id') . ' = ' . $db->quoteName('sp.person_id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_season', 'se')
                    . ' ON ' . $db->quoteName('se.id') . ' = ' . $db->quoteName('sp.season_id')
                );
        } else {
            $playerCount = '(SELECT COUNT(tp1.id) FROM '
                . $db->quoteName('#__sportsmanagement_season_team_person_id') . ' AS tp1'
                . ' WHERE tp1.team_id = st.team_id AND tp1.season_id = ' . $seasonId
                . ' AND tp1.persontype = 1 AND tp1.published = 1)';
            $staffCount = '(SELECT COUNT(tp2.id) FROM '
                . $db->quoteName('#__sportsmanagement_season_team_person_id') . ' AS tp2'
                . ' WHERE tp2.team_id = st.team_id AND tp2.season_id = ' . $seasonId
                . ' AND tp2.persontype = 2 AND tp2.published = 1)';

            $query->select([
                $db->quoteName('st.id', 'season_team_id'),
                $db->quoteName('st.team_id', 'team_id'),
                $db->quoteName('t.name'),
                $db->quoteName('t.short_name'),
                $db->quoteName('t.middle_name'),
                $db->quoteName('t.club_id'),
                $db->quoteName('t.info', 'team_info'),
                $db->quoteName('se.name', 'seasonname'),
                $db->quoteName('c.name', 'clubname'),
                $db->quoteName('c.email', 'club_email'),
                $db->quoteName('c.logo_big', 'club_logo'),
                $db->quoteName('c.country'),
                $db->quoteName('c.latitude'),
                $db->quoteName('c.longitude'),
                $db->quoteName('c.location'),
                $db->quoteName('c.address'),
                $db->quoteName('c.zipcode'),
                $db->quoteName('c.founded_year'),
                $db->quoteName('c.unique_id'),
                $db->quoteName('d.name', 'division_name'),
                $db->quoteName('plg.name', 'playground_name'),
                $db->quoteName('plg.picture', 'playground_picture'),
                $playerCount . ' AS ' . $db->quoteName('playercount'),
                $staffCount . ' AS ' . $db->quoteName('staffcount'),
            ])
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                    . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_season', 'se')
                    . ' ON ' . $db->quoteName('se.id') . ' = ' . $db->quoteName('st.season_id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_team', 't')
                    . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_club', 'c')
                    . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_division', 'd')
                    . ' ON ' . $db->quoteName('d.id') . ' = ' . $db->quoteName('pt.division_id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_playground', 'plg')
                    . ' ON ' . $db->quoteName('plg.id') . ' = ' . $db->quoteName('pt.standard_playground')
                );
        }

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where(
                $individual
                    ? '(' . $db->quoteName('ppl.lastname') . ' LIKE ' . $token
                        . ' OR ' . $db->quoteName('ppl.firstname') . ' LIKE ' . $token . ')'
                    : '(' . $db->quoteName('t.name') . ' LIKE ' . $token
                        . ' OR ' . $db->quoteName('c.name') . ' LIKE ' . $token . ')'
            );
        }

        $state = $this->getState('filter.state');
        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('pt.published') . ' = ' . (int) $state);
        }

        $inScore = $this->getState('filter.is_in_score');
        if ($inScore !== '' && is_numeric($inScore)) {
            $query->where($db->quoteName('pt.is_in_score') . ' = ' . (int) $inScore);
        }

        $useFinally = $this->getState('filter.use_finally');
        if ($useFinally !== '' && is_numeric($useFinally)) {
            $query->where($db->quoteName('pt.use_finally') . ' = ' . (int) $useFinally);
        }

        $division = (int) $this->getState('filter.search_division');
        if (!$individual && $division > 0) {
            $query->where($db->quoteName('pt.division_id') . ' = ' . $division);
        }

        $nation = trim((string) $this->getState('filter.search_nation'));
        if (!$individual && $nation !== '') {
            $query->where($db->quoteName('c.country') . ' = ' . $db->quote($nation));
        }

        $playgroundFilter = $this->getState('filter.playground_id');
        if (!$individual && $playgroundFilter !== '' && is_numeric($playgroundFilter)) {
            $query->where(
                (int) $playgroundFilter === 1
                    ? $db->quoteName('pt.standard_playground') . ' IS NOT NULL AND '
                        . $db->quoteName('pt.standard_playground') . ' > 0'
                    : '(' . $db->quoteName('pt.standard_playground') . ' IS NULL OR '
                        . $db->quoteName('pt.standard_playground') . ' = 0)'
            );
        }

        $nameOrder = $individual ? $db->quoteName('ppl.lastname') : $db->quoteName('t.name');
        $map = [
            't.name' => $nameOrder,
            'name' => $nameOrder,
            't.lastname' => $nameOrder,
            'ppl.lastname' => $nameOrder,
            'lastname' => $nameOrder,
            'tl.admin' => $db->quoteName('pt.admin'),
            'pt.admin' => $db->quoteName('pt.admin'),
            'd.name' => $individual ? $db->quoteName('pt.id') : $db->quoteName('d.name'),
            'division' => $individual ? $db->quoteName('pt.id') : $db->quoteName('d.name'),
            'obj.country' => $individual ? $db->quoteName('pt.id') : $db->quoteName('c.country'),
            'c.country' => $individual ? $db->quoteName('pt.id') : $db->quoteName('c.country'),
            'country' => $individual ? $db->quoteName('pt.id') : $db->quoteName('c.country'),
            'tl.picture' => $db->quoteName('pt.picture'),
            'pt.picture' => $db->quoteName('pt.picture'),
            'tl.matches_finally' => $db->quoteName('pt.matches_finally'),
            'pt.matches_finally' => $db->quoteName('pt.matches_finally'),
            'matches_finally' => $db->quoteName('pt.matches_finally'),
            'pt.start_points' => $db->quoteName('pt.start_points'),
            'start_points' => $db->quoteName('pt.start_points'),
            'pt.penalty_points' => $db->quoteName('pt.penalty_points'),
            'penalty_points' => $db->quoteName('pt.penalty_points'),
            'pt.is_in_score' => $db->quoteName('pt.is_in_score'),
            'is_in_score' => $db->quoteName('pt.is_in_score'),
            'pt.use_finally' => $db->quoteName('pt.use_finally'),
            'use_finally' => $db->quoteName('pt.use_finally'),
            'pt.published' => $db->quoteName('pt.published'),
            'published' => $db->quoteName('pt.published'),
            'state' => $db->quoteName('pt.published'),
            'tl.id' => $db->quoteName('pt.id'),
            'pt.id' => $db->quoteName('pt.id'),
            'id' => $db->quoteName('pt.id'),
            'st.id' => $individual ? $db->quoteName('sp.id') : $db->quoteName('st.id'),
            'season_team_id' => $individual ? $db->quoteName('sp.id') : $db->quoteName('st.id'),
            'st.team_id' => $individual ? $db->quoteName('sp.person_id') : $db->quoteName('st.team_id'),
            'team_id' => $individual ? $db->quoteName('sp.person_id') : $db->quoteName('st.team_id'),
            'tl.ordering' => $db->quoteName('pt.ordering'),
            'pt.ordering' => $db->quoteName('pt.ordering'),
            't.ordering' => $individual ? $db->quoteName('ppl.ordering') : $db->quoteName('t.ordering'),
        ];

        $ordering = (string) $this->getState('list.ordering', $individual ? 'ppl.lastname' : 't.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($map[$ordering] ?? $nameOrder) . ' ' . $direction);

        return $query;
    }

    public function getProjectContext(): ?object
    {
        if ($this->project === null) {
            $this->project = $this->relationService()->getProject((int) $this->getState('filter.pid'));
        }

        return $this->project;
    }

    public function getProjectsByLeagueSeason(int $seasonId, int $leagueId): array
    {
        if ($seasonId <= 0 || $leagueId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
                $db->quoteName('name', 'info'),
                $db->quoteName('picture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('season_id') . ' = ' . $seasonId)
            ->where($db->quoteName('league_id') . ' = ' . $leagueId)
            ->order($db->quoteName('name') . ' ASC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return [];
        }
    }

    public function getDivisionOptions(): array
    {
        return $this->relationService()->getDivisions((int) $this->getState('filter.pid'));
    }

    public function getPlaygroundOptions(): array
    {
        return $this->relationService()->getPlaygrounds();
    }

    public function getProjectTeamDivisionPoints($project_id = 0, $projectteamid = 0, $division_id = 0, $field = '')
    {
        $allowed = [
            'start_points', 'matches_finally', 'points_finally', 'neg_points_finally',
            'penalty_points', 'won_finally', 'draws_finally', 'lost_finally',
            'homegoals_finally', 'guestgoals_finally', 'diffgoals_finally',
        ];
        $field = (string) $field;

        if (!in_array($field, $allowed, true)) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName($field))
            ->from($db->quoteName('#__sportsmanagement_project_team_division'))
            ->where($db->quoteName('project_id') . ' = ' . (int) $project_id)
            ->where($db->quoteName('team_id') . ' = ' . (int) $projectteamid)
            ->where($db->quoteName('division_id') . ' = ' . (int) $division_id);

        try {
            $db->setQuery($query, 0, 1);
            return $db->loadResult() ?? 0;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return 0;
        }
    }

    public function checkProjectTeamDivision($projectteamid = 0, $id = 0, $project_id = 0, $team_id = 0): bool
    {
        $projectTeamId = (int) $projectteamid;
        $projectId = (int) $project_id;

        if ($projectTeamId <= 0 || $projectId <= 0) {
            return false;
        }

        $db = $this->getDatabase();

        try {
            foreach ($this->relationService()->getDivisions($projectId) as $division) {
                $divisionId = (int) ($division->value ?? 0);

                if ($divisionId <= 0) {
                    continue;
                }

                $query = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__sportsmanagement_project_team_division'))
                    ->where($db->quoteName('project_id') . ' = ' . $projectId)
                    ->where($db->quoteName('team_id') . ' = ' . $projectTeamId)
                    ->where($db->quoteName('division_id') . ' = ' . $divisionId);
                $db->setQuery($query);

                if ((int) $db->loadResult() === 0) {
                    $db->insertObject(
                        '#__sportsmanagement_project_team_division',
                        (object) [
                            'project_id' => $projectId,
                            'team_id' => $projectTeamId,
                            'division_id' => $divisionId,
                        ]
                    );
                }
            }

            return true;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    public function addNewProjectTeam($team_id, $project_id): bool
    {
        $teamId = (int) $team_id;
        $projectId = (int) $project_id;

        if ($teamId <= 0 || $projectId <= 0) {
            return false;
        }

        $context = $this->loadProjectSelectionContext($projectId);

        if (!$context || (int) $context->season_id <= 0) {
            return false;
        }

        $db = $this->getDatabase();

        try {
            $db->transactionStart();
            $seasonTeamId = $this->ensureSeasonTeamId($teamId, (int) $context->season_id);

            if ($seasonTeamId <= 0) {
                throw new \RuntimeException('Unable to create season-team relation.');
            }

            $this->ensureProjectTeamId($projectId, $seasonTeamId);
            $db->transactionCommit();
            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());
            return false;
        }
    }

    public function store($data): bool
    {
        $projectId = (int) ($data['id'] ?? 0);
        $selected = $this->normaliseIds($data['project_teamslist'] ?? []);

        if ($projectId <= 0) {
            return false;
        }

        $db = $this->getDatabase();

        try {
            $query = $db->getQuery(true)
                ->select([$db->quoteName('id'), $db->quoteName('team_id')])
                ->from($db->quoteName('#__sportsmanagement_project_team'))
                ->where($db->quoteName('project_id') . ' = ' . $projectId);
            $db->setQuery($query);
            $current = $db->loadObjectList() ?: [];
            $currentByRelation = [];

            foreach ($current as $row) {
                $currentByRelation[(int) $row->team_id] = (int) $row->id;
            }

            $removeIds = [];
            foreach ($currentByRelation as $relationId => $projectTeamId) {
                if (!in_array($relationId, $selected, true)) {
                    $removeIds[] = $projectTeamId;
                }
            }

            $db->transactionStart();

            if ($removeIds) {
                $idList = implode(',', $removeIds);

                foreach (['projectteam1_id', 'projectteam2_id'] as $field) {
                    $query = $db->getQuery(true)
                        ->update($db->quoteName('#__sportsmanagement_match'))
                        ->set($db->quoteName($field) . ' = NULL')
                        ->where($db->quoteName($field) . ' IN (' . $idList . ')');
                    $db->setQuery($query);
                    $db->execute();
                }

                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__sportsmanagement_project_team'))
                    ->where($db->quoteName('id') . ' IN (' . $idList . ')');
                $db->setQuery($query);
                $db->execute();
            }

            foreach ($selected as $seasonRelationId) {
                if (!isset($currentByRelation[$seasonRelationId])) {
                    $db->insertObject(
                        '#__sportsmanagement_project_team',
                        (object) ['project_id' => $projectId, 'team_id' => $seasonRelationId]
                    );
                }
            }

            $db->transactionCommit();
            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());
            return false;
        }
    }

    public function getCountryTeamsPicture(): array
    {
        $project = $this->getProjectContext();

        if (!$project) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('t.id'), $db->quoteName('c.logo_big', 'picture')])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_club', 'c')
                . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id')
            )
            ->order($db->quoteName('t.name') . ' ASC');

        if (!empty($project->country)) {
            $query->where($db->quoteName('c.country') . ' = ' . $db->quote((string) $project->country));
        }

        try {
            $db->setQuery($query);
            return $db->loadAssocList('id', 'picture') ?: [];
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return [];
        }
    }

    public function getCountryTeams($season_id = 0): array
    {
        $project = $this->getProjectContext();
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('t.id', 'value'),
                $db->quoteName('t.name', 'text'),
                $db->quoteName('t.short_name'),
                $db->quoteName('a.name', 'info'),
                $db->quoteName('c.logo_big', 'picture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_club', 'c')
                . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_agegroup', 'a')
                . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('t.agegroup_id')
            )
            ->order($db->quoteName('t.name') . ' ASC');

        if ($project && !empty($project->country)) {
            $query->where($db->quoteName('c.country') . ' = ' . $db->quote((string) $project->country));
        }

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];

            foreach ($rows as $row) {
                $row->text .= ' [' . (string) $row->short_name . '] (' . (string) $row->info . ')';
            }

            return $rows;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return [];
        }
    }

    public function getTeams($country = ''): array
    {
        $project = $this->getProjectContext();

        if (!$project) {
            return [];
        }

        $db = $this->getDatabase();
        $country = trim((string) $country);

        if ((int) $project->project_art_id === 3) {
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('sp.id', 'value'),
                    "CONCAT_WS(' - ', p.lastname, p.firstname) AS " . $db->quoteName('text'),
                    $db->quoteName('p.info'),
                ])
                ->from($db->quoteName('#__sportsmanagement_person', 'p'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_season_person_id', 'sp')
                    . ' ON ' . $db->quoteName('sp.person_id') . ' = ' . $db->quoteName('p.id')
                )
                ->where($db->quoteName('sp.season_id') . ' = ' . (int) $project->season_id)
                ->where($db->quoteName('p.sports_type_id') . ' = ' . (int) $project->sports_type_id)
                ->order($db->quoteName('p.lastname') . ' ASC');
        } else {
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('st.id', 'value'),
                    $db->quoteName('t.name', 'text'),
                    $db->quoteName('t.info'),
                ])
                ->from($db->quoteName('#__sportsmanagement_team', 't'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                    . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id')
                )
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_club', 'c')
                    . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id')
                )
                ->where($db->quoteName('st.season_id') . ' = ' . (int) $project->season_id)
                ->where($db->quoteName('t.sports_type_id') . ' = ' . (int) $project->sports_type_id)
                ->order($db->quoteName('t.name') . ' ASC');

            if ($country !== '') {
                $query->where($db->quoteName('c.country') . ' = ' . $db->quote($country));
            }
        }

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return [];
        }
    }

    public function setNewTeamID(): bool
    {
        $input = Factory::getApplication()->getInput();
        $oldIds = $this->normaliseIds($input->post->get('oldteamid', [], 'array'));
        $newIds = (array) $input->post->get('newteamid', [], 'array');
        $db = $this->getDatabase();

        try {
            foreach ($oldIds as $projectTeamId) {
                $newTeamId = (int) ($newIds[$projectTeamId] ?? 0);

                if ($newTeamId <= 0) {
                    continue;
                }

                $query = $db->getQuery(true)
                    ->select($db->quoteName('st.season_id'))
                    ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
                    ->join(
                        'INNER',
                        $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                        . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
                    )
                    ->where($db->quoteName('pt.id') . ' = ' . $projectTeamId);
                $db->setQuery($query, 0, 1);
                $seasonId = (int) $db->loadResult();
                $seasonTeamId = $this->ensureSeasonTeamId($newTeamId, $seasonId);

                if ($seasonTeamId > 0) {
                    $db->updateObject(
                        '#__sportsmanagement_project_team',
                        (object) ['id' => $projectTeamId, 'team_id' => $seasonTeamId],
                        'id'
                    );
                }
            }

            return true;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    public function getAllTeams($pid): array
    {
        $projectId = (int) $pid;
        $db = $this->getDatabase();

        if ($projectId <= 0) {
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('t.id', 'value'),
                    "CONCAT(t.name, ' [', t.info, ']') AS " . $db->quoteName('text'),
                ])
                ->from($db->quoteName('#__sportsmanagement_team', 't'))
                ->order($db->quoteName('t.name') . ' ASC');

            try {
                $db->setQuery($query);
                return $db->loadObjectList() ?: [];
            } catch (\Throwable $e) {
                $this->setError($e->getMessage());
                return [];
            }
        }

        $context = $this->loadProjectSelectionContext($projectId);

        if (!$context) {
            return [];
        }

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('st.id', 'value'),
                "CONCAT(t.name, ' [', t.info, ']') AS " . $db->quoteName('text'),
                $db->quoteName('s.name', 'season_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_club', 'c')
                . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season', 's')
                . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('st.season_id')
            )
            ->where($db->quoteName('st.season_id') . ' = ' . (int) $context->season_id)
            ->order($db->quoteName('t.name') . ' ASC');

        $assigned = $this->assignedSeasonRelationIds($projectId);
        if ($assigned) {
            $query->where($db->quoteName('st.id') . ' NOT IN (' . implode(',', $assigned) . ')');
        }

        if (!empty($context->country)) {
            $query->where($db->quoteName('c.country') . ' = ' . $db->quote((string) $context->country));
        }

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];

            foreach ($rows as $row) {
                $row->name = Text::_((string) $row->text);
                $row->text .= ' (' . (string) $row->season_name . ')';
            }

            return $rows;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return [];
        }
    }

    public function getProjectTeams($project_id = 0, $in_used = false, $divisionid = 0): array
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return [];
        }

        $context = $this->loadProjectSelectionContext($projectId);
        $db = $this->getDatabase();

        if ($context && (int) $context->project_art_id === 3) {
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('pt.id', 'value'),
                    "CONCAT_WS(' - ', p.lastname, p.firstname) AS " . $db->quoteName('text'),
                    $db->quoteName('p.notes'),
                    $db->quoteName('pt.info'),
                    $db->quoteName('sp.id', 'season_team_id'),
                ])
                ->from($db->quoteName('#__sportsmanagement_person', 'p'))
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_season_person_id', 'sp')
                    . ' ON ' . $db->quoteName('sp.person_id') . ' = ' . $db->quoteName('p.id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_project_team', 'pt')
                    . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('sp.id')
                )
                ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
                ->order($db->quoteName('p.lastname') . ' ASC');
        } else {
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('pt.id', 'value'),
                    $db->quoteName('t.name', 'text'),
                    $db->quoteName('t.notes'),
                    $db->quoteName('pt.info'),
                    $db->quoteName('st.id', 'season_team_id'),
                ])
                ->from($db->quoteName('#__sportsmanagement_team', 't'))
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                    . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_project_team', 'pt')
                    . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
                )
                ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
                ->order($db->quoteName('t.name') . ' ASC');

            if ((int) $divisionid > 0) {
                $query->where($db->quoteName('pt.division_id') . ' = ' . (int) $divisionid);
            }

            if ($in_used && self::$_pro_teams_in_used) {
                $query->where(
                    $db->quoteName('pt.team_id') . ' NOT IN ('
                    . implode(',', $this->normaliseIds(self::$_pro_teams_in_used)) . ')'
                );
            }
        }

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];

            if (!$in_used) {
                self::$_pro_teams_in_used = [];
            }

            foreach ($rows as $row) {
                if (!empty($row->season_team_id)) {
                    self::$_pro_teams_in_used[] = (int) $row->season_team_id;
                }
            }

            self::$_pro_teams_in_used = array_values(array_unique(self::$_pro_teams_in_used));
            return $rows;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return [];
        }
    }

    public function getAllProjectTeams($projectid = 0, $divisionid = 0, $team_ids = null, $cfg_which_database = 0): array
    {
        $projectId = (int) $projectid;

        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pt.id', 'projectteamid'),
                $db->quoteName('pt.team_id'),
                $db->quoteName('pt.picture', 'projectteam_picture'),
                $db->quoteName('pt.project_id'),
                $db->quoteName('t.id'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('t.short_name'),
                $db->quoteName('t.middle_name'),
                $db->quoteName('t.club_id'),
                $db->quoteName('t.website', 'team_www'),
                $db->quoteName('t.picture', 'team_picture'),
                $db->quoteName('c.name', 'club_name'),
                $db->quoteName('c.address', 'club_address'),
                $db->quoteName('c.zipcode', 'club_zipcode'),
                $db->quoteName('c.state', 'club_state'),
                $db->quoteName('c.location', 'club_location'),
                $db->quoteName('c.email', 'club_email'),
                $db->quoteName('c.logo_big'),
                $db->quoteName('c.unique_id'),
                $db->quoteName('c.logo_small'),
                $db->quoteName('c.logo_middle'),
                $db->quoteName('c.country', 'club_country'),
                $db->quoteName('c.website', 'club_www'),
                $db->quoteName('c.latitude'),
                $db->quoteName('c.longitude'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_club', 'c')
                . ' ON ' . $db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id')
            )
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
            ->order($db->quoteName('t.name') . ' ASC');

        $teamIds = $this->normaliseIds($team_ids ?? []);
        if ($teamIds) {
            $query->where($db->quoteName('st.team_id') . ' IN (' . implode(',', $teamIds) . ')');
        }

        if ((int) $divisionid > 0) {
            $query->where($db->quoteName('pt.division_id') . ' = ' . (int) $divisionid);
        }

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return [];
        }
    }

    public function copy($dest, $ptids): bool
    {
        $destinationProjectId = (int) $dest;
        $sourceIds = $this->normaliseIds($ptids);

        if ($destinationProjectId <= 0) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_Destination_project_required'));
            return false;
        }

        if (!$sourceIds) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_no_teams_to_copy'));
            return false;
        }

        $db = $this->getDatabase();
        $destination = $this->loadProjectSelectionContext($destinationProjectId);

        if (!$destination) {
            return false;
        }

        try {
            $db->transactionStart();

            foreach ($sourceIds as $sourceProjectTeamId) {
                $query = $db->getQuery(true)
                    ->select([
                        $db->quoteName('pt.team_id'),
                        $db->quoteName('pt.info'),
                        $db->quoteName('pt.picture'),
                        $db->quoteName('pt.standard_playground'),
                        $db->quoteName('pt.extended'),
                        $db->quoteName('st.team_id', 'raw_team_id'),
                    ])
                    ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
                    ->join(
                        'INNER',
                        $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                        . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
                    )
                    ->where($db->quoteName('pt.id') . ' = ' . $sourceProjectTeamId);
                $db->setQuery($query, 0, 1);
                $source = $db->loadObject();

                if (!$source) {
                    continue;
                }

                $destinationSeasonTeamId = $this->ensureSeasonTeamId(
                    (int) $source->raw_team_id,
                    (int) $destination->season_id
                );
                $destinationProjectTeamId = $this->ensureProjectTeamId(
                    $destinationProjectId,
                    $destinationSeasonTeamId,
                    [
                        'info' => $source->info,
                        'picture' => $source->picture,
                        'standard_playground' => $source->standard_playground,
                        'extended' => $source->extended,
                    ]
                );

                if ($destinationProjectTeamId <= 0) {
                    continue;
                }

                $this->copyLegacyProjectTeamPeople(
                    '#__sportsmanagement_team_player',
                    ['person_id', 'jerseynumber', 'picture', 'extended', 'published'],
                    $sourceProjectTeamId,
                    $destinationProjectTeamId
                );
                $this->copyLegacyProjectTeamPeople(
                    '#__sportsmanagement_team_staff',
                    ['person_id', 'picture', 'extended', 'published'],
                    $sourceProjectTeamId,
                    $destinationProjectTeamId
                );
            }

            $db->transactionCommit();
            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());
            return false;
        }
    }

    public function getProjectTeamsCount($project_id): int
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_project_team'))
            ->where($db->quoteName('project_id') . ' = ' . (int) $project_id);

        try {
            $db->setQuery($query);
            return (int) $db->loadResult();
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return 0;
        }
    }

    public function getMatchesCount($project_id = 0, $projectteam_id = 0): int
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(m.id)')
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->where($db->quoteName('r.project_id') . ' = ' . (int) $project_id);

        if ((int) $projectteam_id > 0) {
            $projectTeamId = (int) $projectteam_id;
            $query->where(
                '(' . $db->quoteName('m.projectteam1_id') . ' = ' . $projectTeamId
                . ' OR ' . $db->quoteName('m.projectteam2_id') . ' = ' . $projectTeamId . ')'
            );
        }

        try {
            $db->setQuery($query);
            return (int) $db->loadResult();
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return 0;
        }
    }

    public function saveShort(): bool
    {
        $app = Factory::getApplication();
        $post = $app->getInput()->post->getArray();
        $ids = $this->normaliseIds($post['cid'] ?? []);

        if (!$ids) {
            $this->setError(Text::_('JGLOBAL_NO_MATCHING_RESULTS'));
            return false;
        }

        $db = $this->getDatabase();
        $projectId = (int) $this->getState('filter.pid');
        $now = Factory::getDate()->toSql();
        $userId = (int) $app->getIdentity()->id;
        $ok = true;
        $intFields = [
            'start_points', 'points_finally', 'neg_points_finally', 'matches_finally', 'won_finally',
            'draws_finally', 'lost_finally', 'homegoals_finally', 'guestgoals_finally', 'diffgoals_finally',
            'penalty_points', 'finaltablerank',
        ];
        $boolFields = ['is_in_score', 'use_finally', 'champion'];

        foreach ($ids as $id) {
            $current = $this->loadProjectTeam($id, $projectId);

            if (!$current) {
                $ok = false;
                continue;
            }

            $divisionId = $this->postedRowInt($post, 'division_id', $id, (int) ($current->division_id ?? 0));
            $playgroundId = $this->postedRowInt(
                $post,
                'standard_playground',
                $id,
                (int) ($current->standard_playground ?? 0)
            );

            if (!$this->relationService()->divisionBelongsToProject($divisionId, $projectId)
                || !$this->relationService()->playgroundExists($playgroundId)) {
                $ok = false;
                continue;
            }

            $object = (object) [
                'id' => $id,
                'division_id' => $divisionId ?: null,
                'standard_playground' => $playgroundId ?: null,
                'modified' => $now,
                'modified_by' => $userId,
            ];

            foreach ($intFields as $field) {
                $object->{$field} = $this->postedRowInt($post, $field, $id, 0);
            }

            foreach ($boolFields as $field) {
                $object->{$field} = $this->postedRowInt($post, $field, $id, 0) === 1 ? 1 : 0;
            }

            try {
                $db->updateObject('#__sportsmanagement_project_team', $object, 'id');
            } catch (\Throwable $e) {
                $this->setError($e->getMessage());
                $ok = false;
            }
        }

        return $ok;
    }

    public function setProjectTeamState(int $state): bool
    {
        return $this->updateSelectedFlag('published', $state, true);
    }

    public function setScoreFlag(int $value): bool
    {
        return $this->updateSelectedFlag('is_in_score', $value, false);
    }

    public function setFinallyFlag(int $value): bool
    {
        return $this->updateSelectedFlag('use_finally', $value, false);
    }

    public function getContextParams(): array
    {
        return ['pid' => (int) $this->getState('filter.pid')];
    }

    private function updateSelectedFlag(string $field, int $value, bool $metadata): bool
    {
        $app = Factory::getApplication();
        $ids = $this->normaliseIds($app->getInput()->post->get('cid', [], 'array'));

        if (!$ids) {
            return false;
        }

        $db = $this->getDatabase();
        $projectId = (int) $this->getState('filter.pid');
        $ok = true;

        foreach ($ids as $id) {
            if (!$this->projectTeamBelongsToProject($id, $projectId)) {
                $ok = false;
                continue;
            }

            $object = (object) ['id' => $id, $field => $value];

            if ($metadata) {
                $object->modified = Factory::getDate()->toSql();
                $object->modified_by = (int) $app->getIdentity()->id;
            }

            try {
                $db->updateObject('#__sportsmanagement_project_team', $object, 'id');
            } catch (\Throwable $e) {
                $this->setError($e->getMessage());
                $ok = false;
            }
        }

        return $ok;
    }

    private function projectTeamBelongsToProject(int $id, int $projectId): bool
    {
        return $this->loadProjectTeam($id, $projectId) !== null;
    }

    private function loadProjectTeam(int $id, int $projectId): ?object
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('division_id'),
                $db->quoteName('standard_playground'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team'))
            ->where($db->quoteName('id') . ' = ' . $id)
            ->where($db->quoteName('project_id') . ' = ' . $projectId);

        try {
            $db->setQuery($query, 0, 1);
            return $db->loadObject() ?: null;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return null;
        }
    }

    private function loadProjectSelectionContext(int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.season_id'),
                $db->quoteName('p.project_art_id'),
                $db->quoteName('p.sports_type_id'),
                $db->quoteName('p.use_nation'),
                $db->quoteName('l.country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_league', 'l')
                . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id')
            )
            ->where($db->quoteName('p.id') . ' = ' . $projectId);

        try {
            $db->setQuery($query, 0, 1);
            return $db->loadObject() ?: null;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return null;
        }
    }

    private function ensureSeasonTeamId(int $teamId, int $seasonId): int
    {
        if ($teamId <= 0 || $seasonId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_season_team_id'))
            ->where($db->quoteName('team_id') . ' = ' . $teamId)
            ->where($db->quoteName('season_id') . ' = ' . $seasonId);
        $db->setQuery($query, 0, 1);
        $id = (int) $db->loadResult();

        if ($id > 0) {
            return $id;
        }

        $db->insertObject(
            '#__sportsmanagement_season_team_id',
            (object) ['team_id' => $teamId, 'season_id' => $seasonId]
        );

        return (int) $db->insertid();
    }

    private function ensureProjectTeamId(int $projectId, int $seasonRelationId, array $defaults = []): int
    {
        if ($projectId <= 0 || $seasonRelationId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_project_team'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->where($db->quoteName('team_id') . ' = ' . $seasonRelationId);
        $db->setQuery($query, 0, 1);
        $id = (int) $db->loadResult();

        if ($id > 0) {
            return $id;
        }

        $record = (object) array_merge(
            ['project_id' => $projectId, 'team_id' => $seasonRelationId],
            $defaults
        );
        $db->insertObject('#__sportsmanagement_project_team', $record);

        return (int) $db->insertid();
    }

    private function assignedSeasonRelationIds(int $projectId): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('team_id'))
            ->from($db->quoteName('#__sportsmanagement_project_team'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId);

        try {
            $db->setQuery($query);
            return $this->normaliseIds($db->loadColumn() ?: []);
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return [];
        }
    }

    private function copyLegacyProjectTeamPeople(
        string $table,
        array $fields,
        int $sourceProjectTeamId,
        int $destinationProjectTeamId
    ): void {
        $db = $this->getDatabase();
        $physical = $db->replacePrefix($table);

        if (!in_array($physical, $db->getTableList(), true)) {
            return;
        }

        $query = $db->getQuery(true)
            ->select(array_map([$db, 'quoteName'], $fields))
            ->from($db->quoteName($table))
            ->where($db->quoteName('projectteam_id') . ' = ' . $sourceProjectTeamId);
        $db->setQuery($query);
        $rows = $db->loadAssocList() ?: [];

        foreach ($rows as $row) {
            $personId = (int) ($row['person_id'] ?? 0);
            $existsQuery = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName($table))
                ->where($db->quoteName('projectteam_id') . ' = ' . $destinationProjectTeamId)
                ->where($db->quoteName('person_id') . ' = ' . $personId);
            $db->setQuery($existsQuery);

            if ((int) $db->loadResult() > 0) {
                continue;
            }

            $row['projectteam_id'] = $destinationProjectTeamId;
            $db->insertObject($table, (object) $row);
        }
    }

    private function postedRowInt(array $post, string $field, int $id, int $default): int
    {
        if (isset($post[$field]) && is_array($post[$field]) && array_key_exists($id, $post[$field])) {
            return (int) $post[$field][$id];
        }

        $legacyKey = $field . $id;

        return array_key_exists($legacyKey, $post) ? (int) $post[$legacyKey] : $default;
    }

    private function normaliseIds($ids): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', (array) $ids),
            static fn (int $id): bool => $id > 0
        )));
    }

    private function relationService(): ProjectRelationService
    {
        return $this->relations ??= new ProjectRelationService($this->getDatabase());
    }
}
