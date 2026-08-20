<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\ProjectRelationService;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/** Native Joomla 5/6 administrator list model for team players/staff. */
final class TeamplayersModel extends SportsManagementListModel
{
    private ?ProjectRelationService $relations = null;
    private ?object $project = null;
    private ?object $teamContext = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'ppl.lastname', 'lastname', 'ppl.firstname', 'firstname',
            'tp.jerseynumber', 'jerseynumber', 'tp.market_value', 'market_value',
            'tp.market_text', 'market_text', 'tp.tt_startpoints', 'tt_startpoints',
            'tp.published', 'published', 'state', 'tp.id', 'id', 'ppl.id', 'person_id',
        ];
        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'ppl.lastname', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);
        $app = Factory::getApplication();
        $input = $app->getInput();

        $projectId = $input->getInt('pid', 0) ?: (int) $app->getUserState('com_sportsmanagement.pid', 0);
        $projectTeamId = $input->getInt('project_team_id', 0) ?: (int) $app->getUserState('com_sportsmanagement.project_team_id', 0);
        $personType = $input->getInt('persontype', 0) ?: (int) $app->getUserState('com_sportsmanagement.persontype', 1);
        $this->setState('filter.pid', $projectId);
        $this->setState('filter.project_team_id', $projectTeamId);
        $this->setState('filter.persontype', max(1, $personType));

        $project = $this->getProjectContext();
        $team = $this->getTeamContext();
        $seasonId = (int) ($project->season_id ?? 0);
        $teamId = (int) ($team->team_id ?? 0);
        $seasonTeamId = (int) ($team->season_team_id ?? 0);
        $this->setState('filter.season_id', $seasonId);
        $this->setState('filter.team_id', $teamId);
        $this->setState('filter.season_team_id', $seasonTeamId);

        if ($projectId > 0) $app->setUserState('com_sportsmanagement.pid', $projectId);
        if ($projectTeamId > 0) $app->setUserState('com_sportsmanagement.project_team_id', $projectTeamId);
        if ($personType > 0) $app->setUserState('com_sportsmanagement.persontype', $personType);
        if ($seasonId > 0) $app->setUserState('com_sportsmanagement.season_id', $seasonId);
        if ($teamId > 0) $app->setUserState('com_sportsmanagement.team_id', $teamId);
        if ($seasonTeamId > 0) $app->setUserState('com_sportsmanagement.season_team_id', $seasonTeamId);
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $projectId = (int) $this->getState('filter.pid');
        $teamId = (int) $this->getState('filter.team_id');
        $seasonId = (int) $this->getState('filter.season_id');
        $personType = (int) $this->getState('filter.persontype');

        $projectPosition = '(SELECT ppp.project_position_id FROM ' . $db->quoteName('#__sportsmanagement_person_project_position') . ' AS ppp'
            . ' WHERE ppp.person_id = ppl.id AND ppp.project_id = ' . $projectId . ' AND ppp.persontype = ' . $personType
            . ' ORDER BY ppp.published DESC, ppp.project_position_id ASC LIMIT 1)';
        $projectPublished = '(SELECT ppp.published FROM ' . $db->quoteName('#__sportsmanagement_person_project_position') . ' AS ppp'
            . ' WHERE ppp.person_id = ppl.id AND ppp.project_id = ' . $projectId . ' AND ppp.persontype = ' . $personType
            . ' ORDER BY ppp.published DESC, ppp.project_position_id ASC LIMIT 1)';

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('tp.id'), $db->quoteName('tp.id', 'tpid'), $db->quoteName('tp.person_id'),
                $db->quoteName('tp.team_id'), $db->quoteName('tp.season_id'), $db->quoteName('tp.persontype'),
                $db->quoteName('tp.project_position_id', 'season_project_position_id'),
                $db->quoteName('tp.jerseynumber'), $db->quoteName('tp.market_value'), $db->quoteName('tp.market_text'),
                $db->quoteName('tp.tt_startpoints'), $db->quoteName('tp.picture', 'season_picture'),
                $db->quoteName('tp.published'), $db->quoteName('tp.checked_out'), $db->quoteName('tp.checked_out_time'),
                $db->quoteName('ppl.firstname'), $db->quoteName('ppl.lastname'), $db->quoteName('ppl.nickname'),
                $db->quoteName('ppl.picture'), $db->quoteName('ppl.country'), $db->quoteName('ppl.injury'),
                $db->quoteName('ppl.suspension'), $db->quoteName('ppl.away'), $db->quoteName('ppl.position_id', 'person_position_id'),
                $db->quoteName('u.name', 'editor'), $projectPosition . ' AS ' . $db->quoteName('project_position_id'),
                'COALESCE(' . $projectPublished . ', 1) AS ' . $db->quoteName('project_published'),
            ])
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'ppl') . ' ON ppl.id = tp.person_id')
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = tp.checked_out')
            ->where('tp.team_id = ' . $teamId)
            ->where('tp.season_id = ' . $seasonId)
            ->where('tp.persontype = ' . $personType)
            ->where('ppl.published = 1');

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            $token = $db->quote('%' . $db->escape(mb_strtolower($search), true) . '%', false);
            $query->where('(LOWER(ppl.lastname) LIKE ' . $token . ' OR LOWER(ppl.firstname) LIKE ' . $token . ' OR LOWER(ppl.nickname) LIKE ' . $token . ')');
        }
        $state = $this->getState('filter.state');
        if ($state !== '' && is_numeric($state)) {
            $query->where('tp.published = ' . (int) $state);
        }

        $map = [
            'ppl.lastname' => 'ppl.lastname', 'lastname' => 'ppl.lastname', 'ppl.firstname' => 'ppl.firstname', 'firstname' => 'ppl.firstname',
            'tp.jerseynumber' => 'tp.jerseynumber', 'jerseynumber' => 'tp.jerseynumber', 'tp.market_value' => 'tp.market_value', 'market_value' => 'tp.market_value',
            'tp.market_text' => 'tp.market_text', 'market_text' => 'tp.market_text', 'tp.tt_startpoints' => 'tp.tt_startpoints', 'tt_startpoints' => 'tp.tt_startpoints',
            'tp.published' => 'tp.published', 'published' => 'tp.published', 'state' => 'tp.published', 'tp.id' => 'tp.id', 'id' => 'tp.id', 'ppl.id' => 'ppl.id', 'person_id' => 'ppl.id',
        ];
        $ordering = (string) $this->getState('list.ordering', 'ppl.lastname');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($map[$ordering] ?? 'ppl.lastname') . ' ' . $direction);
        return $query;
    }

    public function getProjectContext(): ?object
    {
        if ($this->project === null) {
            $this->project = $this->relationService()->getProject((int) $this->getState('filter.pid'));
        }
        return $this->project;
    }

    public function getTeamContext(): ?object
    {
        if ($this->teamContext === null) {
            $this->teamContext = $this->relationService()->getProjectTeam((int) $this->getState('filter.project_team_id'), (int) $this->getState('filter.pid'));
        }
        return $this->teamContext;
    }

    public function getProjectPositionOptions(): array
    {
        return $this->relationService()->getProjectPositions((int) $this->getState('filter.pid'), (int) $this->getState('filter.persontype'));
    }

    /** Compatibility: list query already includes project published state. */
    public function getprojectpublished($items = null)
    {
        return is_array($items) ? $items : [];
    }

    /** Compatibility: list query already includes project position. */
    public function getprojectposition($items = null)
    {
        return is_array($items) ? $items : [];
    }

    public function PersonProjectPosition($projectId, $personType): array
    {
        $projectId = max(0, (int) $projectId);
        $personType = max(0, (int) $personType);
        if ($projectId === 0) return [];
        $db = $this->getDatabase();
        $query = $db->getQuery(true)->select('ppp.*')->from($db->quoteName('#__sportsmanagement_person_project_position', 'ppp'))->where('ppp.project_id = ' . $projectId);
        if ($personType > 0) $query->where('ppp.persontype = ' . $personType);
        return $db->setQuery($query)->loadObjectList();
    }

    /** Ensure roster people have project-position relations without mutating schema. */
    public function checkProjectPositions($projectId, $personType, $teamId, $seasonId, $insert = 1): bool
    {
        $projectId = max(0, (int) $projectId); $personType = max(0, (int) $personType);
        $teamId = max(0, (int) $teamId); $seasonId = max(0, (int) $seasonId);
        if (!$projectId || !$teamId || !$seasonId) return false;
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('stp.person_id, ppos.id AS project_position_id')
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'stp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON p.id = stp.person_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ppos.position_id = p.position_id')
            ->where('stp.team_id = ' . $teamId)->where('stp.season_id = ' . $seasonId)->where('stp.persontype = ' . $personType)->where('ppos.project_id = ' . $projectId);
        $rows = $db->setQuery($query)->loadObjectList();
        if (!$rows) return false;
        if (!(int) $insert) return true;
        $now = Factory::getDate()->toSql(); $userId = (int) Factory::getApplication()->getIdentity()->id;
        $db->transactionStart();
        try {
            foreach ($rows as $row) {
                $check = $db->getQuery(true)->select('id')->from($db->quoteName('#__sportsmanagement_person_project_position'))
                    ->where('person_id = ' . (int) $row->person_id)->where('project_id = ' . $projectId)
                    ->where('project_position_id = ' . (int) $row->project_position_id)->where('persontype = ' . $personType);
                if ($db->setQuery($check, 0, 1)->loadResult()) continue;
                $db->insertObject('#__sportsmanagement_person_project_position', (object) [
                    'person_id' => (int) $row->person_id, 'project_id' => $projectId,
                    'project_position_id' => (int) $row->project_position_id, 'persontype' => $personType,
                    'published' => 1, 'modified' => $now, 'modified_by' => $userId,
                ]);
            }
            $db->transactionCommit(); return true;
        } catch (\Throwable $e) {
            $db->transactionRollback(); $this->setError($e->getMessage()); return false;
        }
    }

    public function saveShort(): bool
    {
        $app = Factory::getApplication(); $post = $app->getInput()->post->getArray();
        $ids = $this->normaliseIds((array) ($post['cid'] ?? []));
        if (!$ids) { $this->setError(Text::_('JGLOBAL_NO_MATCHING_RESULTS')); return false; }
        $db = $this->getDatabase(); $projectId = (int) $this->getState('filter.pid');
        $teamId = (int) $this->getState('filter.team_id'); $seasonId = (int) $this->getState('filter.season_id');
        $personType = (int) $this->getState('filter.persontype'); $now = Factory::getDate()->toSql(); $userId = (int) $app->getIdentity()->id;
        $db->transactionStart();
        try {
            foreach ($ids as $relationId) {
                $row = $this->loadValidatedRelation($relationId, $teamId, $seasonId, $personType);
                if (!$row) throw new \RuntimeException('Invalid team-person relation.');
                $projectPositionId = max(0, (int) ($post['project_position_id'][$relationId] ?? 0));
                $projectPosition = $projectPositionId > 0 ? $this->relationService()->getProjectPosition($projectPositionId, $projectId, $personType) : null;
                if ($projectPositionId > 0 && !$projectPosition) throw new \RuntimeException('Invalid project position.');
                $object = (object) [
                    'id' => $relationId, 'project_position_id' => $projectPositionId,
                    'jerseynumber' => max(0, (int) ($post['jerseynumber'][$relationId] ?? 0)),
                    'market_value' => max(0, (int) ($post['market_value'][$relationId] ?? 0)),
                    'market_text' => substr(trim(strip_tags((string) ($post['market_text'][$relationId] ?? ''))), 0, 50),
                    'tt_startpoints' => (int) ($post['tt_startpoints'][$relationId] ?? 0),
                    'modified' => $now, 'modified_by' => $userId,
                ];
                $db->updateObject('#__sportsmanagement_season_team_person_id', $object, 'id');
                $this->replaceProjectPosition((int) $row->person_id, $projectId, $projectPositionId, $personType, (int) ($post['project_published'][$relationId] ?? 1), $now, $userId);
                $basePositionId = (int) ($projectPosition->position_id ?? 0);
                if ($basePositionId > 0) {
                    $matchTable = $personType === 2 ? '#__sportsmanagement_match_staff' : '#__sportsmanagement_match_player';
                    $memberField = $personType === 2 ? 'team_staff_id' : 'teamplayer_id';
                    $query = $db->getQuery(true)->update($db->quoteName($matchTable))->set('project_position_id = ' . $basePositionId)
                        ->where('project_position_id = 0')->where($db->quoteName($memberField) . ' = ' . $relationId);
                    $db->setQuery($query)->execute();
                }
            }
            $db->transactionCommit(); return true;
        } catch (\Throwable $e) {
            $db->transactionRollback(); $this->setError($e->getMessage()); return false;
        }
    }

    public function setRelationState(int $state): bool
    {
        $app = Factory::getApplication(); $ids = $this->normaliseIds((array) $app->getInput()->post->get('cid', [], 'array'));
        if (!$ids) return false;
        $db = $this->getDatabase(); $projectId = (int) $this->getState('filter.pid'); $teamId = (int) $this->getState('filter.team_id');
        $seasonId = (int) $this->getState('filter.season_id'); $personType = (int) $this->getState('filter.persontype');
        $now = Factory::getDate()->toSql(); $userId = (int) $app->getIdentity()->id;
        $db->transactionStart();
        try {
            foreach ($ids as $relationId) {
                $row = $this->loadValidatedRelation($relationId, $teamId, $seasonId, $personType);
                if (!$row) throw new \RuntimeException('Invalid team-person relation.');
                $db->updateObject('#__sportsmanagement_season_team_person_id', (object) ['id' => $relationId, 'published' => $state, 'modified' => $now, 'modified_by' => $userId], 'id');
                $query = $db->getQuery(true)->update($db->quoteName('#__sportsmanagement_person_project_position'))
                    ->set('published = ' . $state)->set('modified = ' . $db->quote($now))->set('modified_by = ' . $userId)
                    ->where('person_id = ' . (int) $row->person_id)->where('project_id = ' . $projectId)->where('persontype = ' . $personType);
                $db->setQuery($query)->execute();
            }
            $db->transactionCommit(); return true;
        } catch (\Throwable $e) {
            $db->transactionRollback(); $this->setError($e->getMessage()); return false;
        }
    }

    public function assignPlayersCountry(): bool
    {
        $teamId = (int) $this->getState('filter.team_id'); $seasonId = (int) $this->getState('filter.season_id');
        $personType = (int) $this->getState('filter.persontype');
        if (!$teamId || !$seasonId) return false;
        $db = $this->getDatabase();
        $query = $db->getQuery(true)->select('c.country')->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON t.club_id = c.id')->where('t.id = ' . $teamId);
        $country = (string) $db->setQuery($query, 0, 1)->loadResult();
        if ($country === '') return false;
        $query = $db->getQuery(true)->select('person_id')->from($db->quoteName('#__sportsmanagement_season_team_person_id'))
            ->where('team_id = ' . $teamId)->where('season_id = ' . $seasonId)->where('persontype = ' . $personType);
        $personIds = $this->normaliseIds($db->setQuery($query)->loadColumn());
        $db->transactionStart();
        try {
            foreach ($personIds as $personId) $db->updateObject('#__sportsmanagement_person', (object) ['id' => $personId, 'country' => $country], 'id');
            $db->transactionCommit(); return true;
        } catch (\Throwable $e) {
            $db->transactionRollback(); $this->setError($e->getMessage()); return false;
        }
    }

    public function deleteRelations(): bool
    {
        $app = Factory::getApplication(); $ids = $this->normaliseIds((array) $app->getInput()->post->get('cid', [], 'array'));
        if (!$ids) return false;
        $db = $this->getDatabase(); $teamId = (int) $this->getState('filter.team_id'); $seasonId = (int) $this->getState('filter.season_id');
        $personType = (int) $this->getState('filter.persontype'); $projectId = (int) $this->getState('filter.pid');
        $valid = [];
        foreach ($ids as $id) { $row = $this->loadValidatedRelation($id, $teamId, $seasonId, $personType); if ($row) $valid[$id] = (int) $row->person_id; }
        if (!$valid) return false;
        $relationList = implode(',', array_keys($valid)); $personList = implode(',', array_values($valid));
        $db->transactionStart();
        try {
            foreach ([
                ['#__sportsmanagement_match_player','teamplayer_id'], ['#__sportsmanagement_match_player','in_for'],
                ['#__sportsmanagement_match_staff','team_staff_id'], ['#__sportsmanagement_match_statistic','teamplayer_id'],
                ['#__sportsmanagement_match_staff_statistic','team_staff_id'], ['#__sportsmanagement_match_event','teamplayer_id'],
                ['#__sportsmanagement_match_event','teamplayer_id2'],
            ] as [$table,$column]) {
                $query = $db->getQuery(true)->delete($db->quoteName($table))->where($db->quoteName($column) . ' IN (' . $relationList . ')');
                $db->setQuery($query)->execute();
            }
            if ($projectId > 0) {
                $query = $db->getQuery(true)->delete($db->quoteName('#__sportsmanagement_person_project_position'))
                    ->where('person_id IN (' . $personList . ')')->where('project_id = ' . $projectId)->where('persontype = ' . $personType);
                $db->setQuery($query)->execute();
            }
            $query = $db->getQuery(true)->delete($db->quoteName('#__sportsmanagement_season_team_person_id'))->where('id IN (' . $relationList . ')');
            $db->setQuery($query)->execute();
            $db->transactionCommit(); return true;
        } catch (\Throwable $e) {
            $db->transactionRollback(); $this->setError($e->getMessage()); return false;
        }
    }

    public function getTeamplayersMatch($teamId = 0, $seasonId = 0, $projectTeamId = 0, $projectId = 0, $matchId = 0): array
    {
        $seasonId = max(0,(int)$seasonId); $projectTeamId = max(0,(int)$projectTeamId); $matchId = max(0,(int)$matchId);
        if (!$seasonId || !$projectTeamId || !$matchId) return [];
        $db = $this->getDatabase();
        $query = $db->getQuery(true)->select('tp.id')->from($db->quoteName('#__sportsmanagement_season_team_person_id','tp'))
            ->join('LEFT',$db->quoteName('#__sportsmanagement_season_team_id','st').' ON st.team_id=tp.team_id AND st.season_id=tp.season_id')
            ->join('LEFT',$db->quoteName('#__sportsmanagement_project_team','pt').' ON pt.team_id=st.id')
            ->where('pt.id='.$projectTeamId)->where('tp.season_id='.$seasonId)->where('tp.persontype=1');
        $ids = $this->normaliseIds($db->setQuery($query)->loadColumn()); if (!$ids) return [];
        $query = $db->getQuery(true)->select('mp.teamplayer_id, mp.project_position_id, pos.name AS project_position_name')
            ->from($db->quoteName('#__sportsmanagement_match_player','mp'))->join('INNER',$db->quoteName('#__sportsmanagement_position','pos').' ON pos.id=mp.project_position_id')
            ->where('mp.match_id='.$matchId)->where('(mp.came_in=0 OR mp.came_in=1)')->where('mp.teamplayer_id IN ('.implode(',',$ids).')');
        return $db->setQuery($query)->loadObjectList();
    }

    public function getProjectTeamplayers($teamId = 0, $seasonId = 0, $projectTeamId = 0, $generate = 0, $projectId = 0): array
    {
        $teamId=max(0,(int)$teamId); $seasonId=max(0,(int)$seasonId); $projectTeamId=max(0,(int)$projectTeamId); $projectId=max(0,(int)$projectId);
        if (!$seasonId) return [];
        $db=$this->getDatabase(); $query=$db->getQuery(true)->select('ppl.*, tp.id AS season_team_person_id')
            ->from($db->quoteName('#__sportsmanagement_person','ppl'))->join('INNER',$db->quoteName('#__sportsmanagement_season_team_person_id','tp').' ON tp.person_id=ppl.id')
            ->join('INNER',$db->quoteName('#__sportsmanagement_season_team_id','st').' ON st.team_id=tp.team_id AND st.season_id=tp.season_id')
            ->where('st.season_id='.$seasonId)->where('tp.season_id='.$seasonId);
        if ($teamId) $query->where('st.team_id='.$teamId);
        if ($projectTeamId) $query->join('INNER',$db->quoteName('#__sportsmanagement_project_team','pt').' ON pt.team_id=st.id')->where('pt.id='.$projectTeamId);
        if ((int)$generate && $projectId) {
            $sub=$db->getQuery(true)->select('ppp.project_position_id')->from($db->quoteName('#__sportsmanagement_person_project_position','ppp'))
                ->where('ppp.person_id=ppl.id')->where('ppp.project_id='.$projectId)->where('ppp.persontype=1');
            $query->select('('.$sub.') AS project_position_id');
        }
        return $db->setQuery($query)->loadObjectList();
    }

    public function getContextParams(): array
    {
        return ['pid'=>(int)$this->getState('filter.pid'),'project_team_id'=>(int)$this->getState('filter.project_team_id'),'team_id'=>(int)$this->getState('filter.team_id'),'season_team_id'=>(int)$this->getState('filter.season_team_id'),'persontype'=>(int)$this->getState('filter.persontype')];
    }

    private function replaceProjectPosition(int $personId,int $projectId,int $projectPositionId,int $personType,int $published,string $now,int $userId): void
    {
        $db=$this->getDatabase(); $query=$db->getQuery(true)->delete($db->quoteName('#__sportsmanagement_person_project_position'))
            ->where('person_id='.$personId)->where('project_id='.$projectId)->where('persontype='.$personType); $db->setQuery($query)->execute();
        if ($projectPositionId>0) $db->insertObject('#__sportsmanagement_person_project_position',(object)['person_id'=>$personId,'project_id'=>$projectId,'project_position_id'=>$projectPositionId,'persontype'=>$personType,'published'=>$published===0?0:1,'modified'=>$now,'modified_by'=>$userId]);
    }

    private function loadValidatedRelation(int $relationId,int $teamId,int $seasonId,int $personType): ?object
    {
        $db=$this->getDatabase(); $query=$db->getQuery(true)->select('id, person_id')->from($db->quoteName('#__sportsmanagement_season_team_person_id'))
            ->where('id='.$relationId)->where('team_id='.$teamId)->where('season_id='.$seasonId)->where('persontype='.$personType);
        return $db->setQuery($query,0,1)->loadObject() ?: null;
    }

    private function normaliseIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval',$ids),static fn(int $id):bool=>$id>0)));
    }

    private function relationService(): ProjectRelationService
    {
        return $this->relations ??= new ProjectRelationService($this->getDatabase());
    }
}
