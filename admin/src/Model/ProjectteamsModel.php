<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\ProjectRelationService;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class ProjectteamsModel extends SportsManagementListModel
{
    private ?ProjectRelationService $relations = null;
    private ?object $project = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            't.name', 'name', 'ppl.lastname', 'lastname',
            'd.name', 'division', 'pt.start_points', 'start_points',
            'pt.matches_finally', 'matches_finally', 'pt.penalty_points', 'penalty_points',
            'pt.is_in_score', 'is_in_score', 'pt.use_finally', 'use_finally',
            'pt.published', 'published', 'state', 'pt.id', 'id',
            'st.id', 'season_team_id', 'st.team_id', 'team_id', 'c.country', 'country',
        ];
        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 't.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);
        $app = Factory::getApplication();
        $input = $app->input;
        $projectId = $input->getInt('pid', 0) ?: (int) $app->getUserState('com_sportsmanagement.pid', 0);
        $this->setState('filter.pid', $projectId);
        if ($projectId > 0) {
            $app->setUserState('com_sportsmanagement.pid', $projectId);
        }

        $legacy = [
            'search_nation' => 'filter_search_nation',
            'search_division' => 'filter_search_division',
            'playground_id' => 'filter_playground_id',
            'is_in_score' => 'filter_is_in_score',
            'use_finally' => 'filter_use_finally',
        ];
        foreach ($legacy as $state => $request) {
            if ((string) $this->getState('filter.' . $state) === '') {
                $value = $input->getString($request, '');
                if ($value !== '') {
                    $this->setState('filter.' . $state, $value);
                }
            }
        }

        $project = $this->getProjectContext();
        if ($project) {
            $app->setUserState('com_sportsmanagement.season_id', (int) $project->season_id);
            $app->setUserState('com_sportsmanagement.project_art_id', (int) $project->project_art_id);
            $app->setUserState('com_sportsmanagement.sports_type_id', (int) $project->sports_type_id);
            if ((int) $project->project_art_id === 3 && (string) $this->getState('list.ordering') === 't.name') {
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

        $select = [
            $db->quoteName('pt.id'),
            $db->quoteName('pt.id', 'projectteamid'),
            $db->quoteName('pt.project_id'),
            $db->quoteName('pt.team_id', 'season_relation_id'),
            $db->quoteName('pt.division_id'),
            $db->quoteName('pt.start_points'),
            $db->quoteName('pt.points_finally'),
            $db->quoteName('pt.neg_points_finally'),
            $db->quoteName('pt.matches_finally'),
            $db->quoteName('pt.won_finally'),
            $db->quoteName('pt.draws_finally'),
            $db->quoteName('pt.lost_finally'),
            $db->quoteName('pt.homegoals_finally'),
            $db->quoteName('pt.guestgoals_finally'),
            $db->quoteName('pt.diffgoals_finally'),
            $db->quoteName('pt.penalty_points'),
            $db->quoteName('pt.is_in_score'),
            $db->quoteName('pt.use_finally'),
            $db->quoteName('pt.champion'),
            $db->quoteName('pt.finaltablerank'),
            $db->quoteName('pt.standard_playground'),
            $db->quoteName('pt.admin'),
            $db->quoteName('pt.picture'),
            $db->quoteName('pt.published'),
            $db->quoteName('pt.checked_out'),
            $db->quoteName('pt.checked_out_time'),
            $db->quoteName('u.name', 'editor'),
            $db->quoteName('u.email', 'admin_email'),
        ];

        $query = $db->getQuery(true)
            ->select($select)
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('pt.admin'))
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId);

        if ($individual) {
            $query->select([
                $db->quoteName('sp.id', 'season_team_id'),
                $db->quoteName('sp.person_id', 'team_id'),
                "CONCAT_WS(', ', ppl.lastname, ppl.firstname) AS " . $db->quoteName('name'),
                $db->quoteName('ppl.lastname'),
                $db->quoteName('ppl.firstname'),
                $db->quoteName('se.name', 'seasonname'),
                '0 AS ' . $db->quoteName('playercount'),
                '0 AS ' . $db->quoteName('staffcount'),
                "'' AS " . $db->quoteName('clubname'),
                "'' AS " . $db->quoteName('country'),
                "'' AS " . $db->quoteName('division_name'),
                "'' AS " . $db->quoteName('playground_name'),
            ])
                ->join('LEFT', $db->quoteName('#__sportsmanagement_season_person_id', 'sp') . ' ON ' . $db->quoteName('sp.id') . ' = ' . $db->quoteName('pt.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_person', 'ppl') . ' ON ' . $db->quoteName('ppl.id') . ' = ' . $db->quoteName('sp.person_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_season', 'se') . ' ON ' . $db->quoteName('se.id') . ' = ' . $db->quoteName('sp.season_id'));
        } else {
            $playerCount = '(SELECT COUNT(tp1.id) FROM ' . $db->quoteName('#__sportsmanagement_season_team_person_id') . ' AS tp1'
                . ' WHERE tp1.team_id = st.team_id AND tp1.season_id = ' . $seasonId . ' AND tp1.persontype = 1 AND tp1.published = 1)';
            $staffCount = '(SELECT COUNT(tp2.id) FROM ' . $db->quoteName('#__sportsmanagement_season_team_person_id') . ' AS tp2'
                . ' WHERE tp2.team_id = st.team_id AND tp2.season_id = ' . $seasonId . ' AND tp2.persontype = 2 AND tp2.published = 1)';
            $query->select([
                $db->quoteName('st.id', 'season_team_id'),
                $db->quoteName('st.team_id', 'team_id'),
                $db->quoteName('t.name'),
                $db->quoteName('t.club_id'),
                $db->quoteName('se.name', 'seasonname'),
                $db->quoteName('c.name', 'clubname'),
                $db->quoteName('c.email', 'club_email'),
                $db->quoteName('c.country'),
                $db->quoteName('d.name', 'division_name'),
                $db->quoteName('plg.name', 'playground_name'),
                $playerCount . ' AS ' . $db->quoteName('playercount'),
                $staffCount . ' AS ' . $db->quoteName('staffcount'),
            ])
                ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_season', 'se') . ' ON ' . $db->quoteName('se.id') . ' = ' . $db->quoteName('st.season_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_division', 'd') . ' ON ' . $db->quoteName('d.id') . ' = ' . $db->quoteName('pt.division_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_playground', 'plg') . ' ON ' . $db->quoteName('plg.id') . ' = ' . $db->quoteName('pt.standard_playground'));
        }

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where($individual
                ? '(' . $db->quoteName('ppl.lastname') . ' LIKE ' . $token . ' OR ' . $db->quoteName('ppl.firstname') . ' LIKE ' . $token . ')'
                : '(' . $db->quoteName('t.name') . ' LIKE ' . $token . ' OR ' . $db->quoteName('c.name') . ' LIKE ' . $token . ')');
        }

        $state = $this->getState('filter.state');
        if ($state !== '' && is_numeric($state)) $query->where($db->quoteName('pt.published') . ' = ' . (int) $state);
        $inScore = $this->getState('filter.is_in_score');
        if ($inScore !== '' && is_numeric($inScore)) $query->where($db->quoteName('pt.is_in_score') . ' = ' . (int) $inScore);
        $useFinally = $this->getState('filter.use_finally');
        if ($useFinally !== '' && is_numeric($useFinally)) $query->where($db->quoteName('pt.use_finally') . ' = ' . (int) $useFinally);
        $division = (int) $this->getState('filter.search_division');
        if (!$individual && $division > 0) $query->where($db->quoteName('pt.division_id') . ' = ' . $division);
        $nation = trim((string) $this->getState('filter.search_nation'));
        if (!$individual && $nation !== '') $query->where($db->quoteName('c.country') . ' = ' . $db->quote($nation));
        $playgroundFilter = $this->getState('filter.playground_id');
        if (!$individual && $playgroundFilter !== '' && is_numeric($playgroundFilter)) {
            $query->where((int) $playgroundFilter === 1
                ? $db->quoteName('pt.standard_playground') . ' IS NOT NULL AND ' . $db->quoteName('pt.standard_playground') . ' > 0'
                : '(' . $db->quoteName('pt.standard_playground') . ' IS NULL OR ' . $db->quoteName('pt.standard_playground') . ' = 0)');
        }

        $map = [
            't.name' => $individual ? $db->quoteName('ppl.lastname') : $db->quoteName('t.name'),
            'name' => $individual ? $db->quoteName('ppl.lastname') : $db->quoteName('t.name'),
            'ppl.lastname' => $individual ? $db->quoteName('ppl.lastname') : $db->quoteName('t.name'), 'lastname' => $individual ? $db->quoteName('ppl.lastname') : $db->quoteName('t.name'),
            'd.name' => $individual ? $db->quoteName('pt.id') : $db->quoteName('d.name'), 'division' => $individual ? $db->quoteName('pt.id') : $db->quoteName('d.name'),
            'pt.start_points' => $db->quoteName('pt.start_points'), 'start_points' => $db->quoteName('pt.start_points'),
            'pt.matches_finally' => $db->quoteName('pt.matches_finally'), 'matches_finally' => $db->quoteName('pt.matches_finally'),
            'pt.penalty_points' => $db->quoteName('pt.penalty_points'), 'penalty_points' => $db->quoteName('pt.penalty_points'),
            'pt.is_in_score' => $db->quoteName('pt.is_in_score'), 'is_in_score' => $db->quoteName('pt.is_in_score'),
            'pt.use_finally' => $db->quoteName('pt.use_finally'), 'use_finally' => $db->quoteName('pt.use_finally'),
            'pt.published' => $db->quoteName('pt.published'), 'published' => $db->quoteName('pt.published'), 'state' => $db->quoteName('pt.published'),
            'pt.id' => $db->quoteName('pt.id'), 'id' => $db->quoteName('pt.id'),
            'st.id' => $individual ? $db->quoteName('sp.id') : $db->quoteName('st.id'), 'season_team_id' => $individual ? $db->quoteName('sp.id') : $db->quoteName('st.id'),
            'st.team_id' => $individual ? $db->quoteName('sp.person_id') : $db->quoteName('st.team_id'), 'team_id' => $individual ? $db->quoteName('sp.person_id') : $db->quoteName('st.team_id'),
            'c.country' => $individual ? $db->quoteName('pt.id') : $db->quoteName('c.country'), 'country' => $individual ? $db->quoteName('pt.id') : $db->quoteName('c.country'),
        ];
        $ordering = (string) $this->getState('list.ordering', $individual ? 'ppl.lastname' : 't.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($map[$ordering] ?? ($individual ? $db->quoteName('ppl.lastname') : $db->quoteName('t.name'))) . ' ' . $direction);
        return $query;
    }

    public function getProjectContext(): ?object
    {
        if ($this->project === null) {
            $this->project = $this->relationService()->getProject((int) $this->getState('filter.pid'));
        }
        return $this->project;
    }

    public function getDivisionOptions(): array
    {
        return $this->relationService()->getDivisions((int) $this->getState('filter.pid'));
    }

    public function getPlaygroundOptions(): array
    {
        return $this->relationService()->getPlaygrounds();
    }

    public function saveShort(): bool
    {
        $app = Factory::getApplication();
        $post = $app->input->post->getArray();
        $ids = array_values(array_filter(array_map('intval', (array) ($post['cid'] ?? []))));
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
            if (!$this->projectTeamBelongsToProject($id, $projectId)) {
                $ok = false;
                continue;
            }
            $current = $this->loadProjectTeam($id, $projectId);
            if (!$current) {
                $ok = false;
                continue;
            }
            $divisionId = array_key_exists($id, (array) ($post['division_id'] ?? []))
                ? max(0, (int) $post['division_id'][$id])
                : max(0, (int) ($current->division_id ?? 0));
            $playgroundId = array_key_exists($id, (array) ($post['standard_playground'] ?? []))
                ? max(0, (int) $post['standard_playground'][$id])
                : max(0, (int) ($current->standard_playground ?? 0));
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
                $object->{$field} = (int) (($post[$field][$id] ?? 0));
            }
            foreach ($boolFields as $field) {
                $object->{$field} = ((int) (($post[$field][$id] ?? 0))) === 1 ? 1 : 0;
            }
            if (!$db->updateObject('#__sportsmanagement_project_team', $object, 'id')) {
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
        $ids = array_values(array_filter(array_map('intval', (array) $app->input->post->get('cid', [], 'array'))));
        if (!$ids) return false;
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
            if (!$db->updateObject('#__sportsmanagement_project_team', $object, 'id')) $ok = false;
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
            ->select([$db->quoteName('id'), $db->quoteName('division_id'), $db->quoteName('standard_playground')])
            ->from($db->quoteName('#__sportsmanagement_project_team'))
            ->where($db->quoteName('id') . ' = ' . $id)
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }

    private function relationService(): ProjectRelationService
    {
        return $this->relations ??= new ProjectRelationService($this->getDatabase());
    }
}
