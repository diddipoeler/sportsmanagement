<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\ProjectRelationService;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

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
            'tp.tt_startpoints', 'tt_startpoints', 'tp.published', 'published', 'state',
            'tp.id', 'id', 'ppl.id', 'person_id',
        ];
        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'ppl.lastname', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);
        $app = Factory::getApplication();
        $input = $app->input;

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
                $db->quoteName('tp.id'),
                $db->quoteName('tp.id', 'tpid'),
                $db->quoteName('tp.person_id'),
                $db->quoteName('tp.team_id'),
                $db->quoteName('tp.season_id'),
                $db->quoteName('tp.persontype'),
                $db->quoteName('tp.project_position_id', 'season_project_position_id'),
                $db->quoteName('tp.jerseynumber'),
                $db->quoteName('tp.market_value'),
                $db->quoteName('tp.market_text'),
                $db->quoteName('tp.tt_startpoints'),
                $db->quoteName('tp.picture', 'season_picture'),
                $db->quoteName('tp.published'),
                $db->quoteName('tp.checked_out'),
                $db->quoteName('tp.checked_out_time'),
                $db->quoteName('ppl.firstname'),
                $db->quoteName('ppl.lastname'),
                $db->quoteName('ppl.nickname'),
                $db->quoteName('ppl.picture'),
                $db->quoteName('ppl.country'),
                $db->quoteName('ppl.injury'),
                $db->quoteName('ppl.suspension'),
                $db->quoteName('ppl.away'),
                $db->quoteName('u.name', 'editor'),
                $projectPosition . ' AS ' . $db->quoteName('project_position_id'),
                'COALESCE(' . $projectPublished . ', 1) AS ' . $db->quoteName('project_published'),
            ])
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'ppl') . ' ON ' . $db->quoteName('ppl.id') . ' = ' . $db->quoteName('tp.person_id'))
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('tp.checked_out'))
            ->where($db->quoteName('tp.team_id') . ' = ' . $teamId)
            ->where($db->quoteName('tp.season_id') . ' = ' . $seasonId)
            ->where($db->quoteName('tp.persontype') . ' = ' . $personType)
            ->where($db->quoteName('ppl.published') . ' = 1');

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('(' . $db->quoteName('ppl.lastname') . ' LIKE ' . $token
                . ' OR ' . $db->quoteName('ppl.firstname') . ' LIKE ' . $token
                . ' OR ' . $db->quoteName('ppl.nickname') . ' LIKE ' . $token . ')');
        }

        $state = $this->getState('filter.state');
        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('tp.published') . ' = ' . (int) $state);
        }

        $map = [
            'ppl.lastname' => $db->quoteName('ppl.lastname'), 'lastname' => $db->quoteName('ppl.lastname'),
            'ppl.firstname' => $db->quoteName('ppl.firstname'), 'firstname' => $db->quoteName('ppl.firstname'),
            'tp.jerseynumber' => $db->quoteName('tp.jerseynumber'), 'jerseynumber' => $db->quoteName('tp.jerseynumber'),
            'tp.market_value' => $db->quoteName('tp.market_value'), 'market_value' => $db->quoteName('tp.market_value'),
            'tp.tt_startpoints' => $db->quoteName('tp.tt_startpoints'), 'tt_startpoints' => $db->quoteName('tp.tt_startpoints'),
            'tp.published' => $db->quoteName('tp.published'), 'published' => $db->quoteName('tp.published'), 'state' => $db->quoteName('tp.published'),
            'tp.id' => $db->quoteName('tp.id'), 'id' => $db->quoteName('tp.id'),
            'ppl.id' => $db->quoteName('ppl.id'), 'person_id' => $db->quoteName('ppl.id'),
        ];
        $ordering = (string) $this->getState('list.ordering', 'ppl.lastname');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($map[$ordering] ?? $map['ppl.lastname']) . ' ' . $direction);

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
            $this->teamContext = $this->relationService()->getProjectTeam(
                (int) $this->getState('filter.project_team_id'),
                (int) $this->getState('filter.pid')
            );
        }
        return $this->teamContext;
    }

    public function getProjectPositionOptions(): array
    {
        return $this->relationService()->getProjectPositions(
            (int) $this->getState('filter.pid'),
            (int) $this->getState('filter.persontype')
        );
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
        $teamId = (int) $this->getState('filter.team_id');
        $seasonId = (int) $this->getState('filter.season_id');
        $personType = (int) $this->getState('filter.persontype');
        $now = Factory::getDate()->toSql();
        $userId = (int) $app->getIdentity()->id;
        $ok = true;

        foreach ($ids as $relationId) {
            $row = $this->loadValidatedRelation($relationId, $teamId, $seasonId, $personType);
            if (!$row) {
                $ok = false;
                continue;
            }

            $projectPositionId = max(0, (int) (($post['project_position_id'][$relationId] ?? 0)));
            $projectPosition = $projectPositionId > 0
                ? $this->relationService()->getProjectPosition($projectPositionId, $projectId, $personType)
                : null;
            if ($projectPositionId > 0 && !$projectPosition) {
                $ok = false;
                continue;
            }

            $object = (object) [
                'id' => $relationId,
                'project_position_id' => $projectPositionId,
                'jerseynumber' => max(0, (int) (($post['jerseynumber'][$relationId] ?? 0))),
                'market_value' => max(0, (int) (($post['market_value'][$relationId] ?? 0))),
                'market_text' => substr(trim(strip_tags((string) (($post['market_text'][$relationId] ?? '')))), 0, 50),
                'tt_startpoints' => (int) (($post['tt_startpoints'][$relationId] ?? 0)),
                'modified' => $now,
                'modified_by' => $userId,
            ];
            if (!$db->updateObject('#__sportsmanagement_season_team_person_id', $object, 'id')) {
                $ok = false;
                continue;
            }

            $delete = $db->getQuery(true)
                ->delete($db->quoteName('#__sportsmanagement_person_project_position'))
                ->where($db->quoteName('person_id') . ' = ' . (int) $row->person_id)
                ->where($db->quoteName('project_id') . ' = ' . $projectId)
                ->where($db->quoteName('persontype') . ' = ' . $personType);
            $db->setQuery($delete)->execute();

            if ($projectPositionId > 0) {
                $profile = (object) [
                    'person_id' => (int) $row->person_id,
                    'project_id' => $projectId,
                    'project_position_id' => $projectPositionId,
                    'persontype' => $personType,
                    'published' => ((int) (($post['project_published'][$relationId] ?? 1))) === 0 ? 0 : 1,
                    'modified' => $now,
                    'modified_by' => $userId,
                ];
                if (!$db->insertObject('#__sportsmanagement_person_project_position', $profile)) {
                    $ok = false;
                }

                $basePositionId = (int) ($projectPosition->position_id ?? 0);
                if ($basePositionId > 0) {
                    $matchTable = $personType === 2 ? '#__sportsmanagement_match_staff' : '#__sportsmanagement_match_player';
                    $memberField = $personType === 2 ? 'team_staff_id' : 'teamplayer_id';
                    $query = $db->getQuery(true)
                        ->update($db->quoteName($matchTable))
                        ->set($db->quoteName('project_position_id') . ' = ' . $basePositionId)
                        ->where($db->quoteName('project_position_id') . ' = 0')
                        ->where($db->quoteName($memberField) . ' = ' . $relationId);
                    $db->setQuery($query)->execute();
                }
            }
        }

        return $ok;
    }

    public function setRelationState(int $state): bool
    {
        $app = Factory::getApplication();
        $ids = array_values(array_filter(array_map('intval', (array) $app->input->post->get('cid', [], 'array'))));
        if (!$ids) {
            return false;
        }

        $db = $this->getDatabase();
        $projectId = (int) $this->getState('filter.pid');
        $teamId = (int) $this->getState('filter.team_id');
        $seasonId = (int) $this->getState('filter.season_id');
        $personType = (int) $this->getState('filter.persontype');
        $now = Factory::getDate()->toSql();
        $userId = (int) $app->getIdentity()->id;
        $ok = true;

        foreach ($ids as $relationId) {
            $row = $this->loadValidatedRelation($relationId, $teamId, $seasonId, $personType);
            if (!$row) {
                $ok = false;
                continue;
            }
            $object = (object) ['id' => $relationId, 'published' => $state, 'modified' => $now, 'modified_by' => $userId];
            if (!$db->updateObject('#__sportsmanagement_season_team_person_id', $object, 'id')) {
                $ok = false;
            }
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__sportsmanagement_person_project_position'))
                ->set($db->quoteName('published') . ' = ' . $state)
                ->set($db->quoteName('modified') . ' = ' . $db->quote($now))
                ->set($db->quoteName('modified_by') . ' = ' . $userId)
                ->where($db->quoteName('person_id') . ' = ' . (int) $row->person_id)
                ->where($db->quoteName('project_id') . ' = ' . $projectId)
                ->where($db->quoteName('persontype') . ' = ' . $personType);
            $db->setQuery($query)->execute();
        }

        return $ok;
    }

    public function getContextParams(): array
    {
        return [
            'pid' => (int) $this->getState('filter.pid'),
            'project_team_id' => (int) $this->getState('filter.project_team_id'),
            'team_id' => (int) $this->getState('filter.team_id'),
            'season_team_id' => (int) $this->getState('filter.season_team_id'),
            'persontype' => (int) $this->getState('filter.persontype'),
        ];
    }

    private function loadValidatedRelation(int $relationId, int $teamId, int $seasonId, int $personType): ?object
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('person_id')])
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id'))
            ->where($db->quoteName('id') . ' = ' . $relationId)
            ->where($db->quoteName('team_id') . ' = ' . $teamId)
            ->where($db->quoteName('season_id') . ' = ' . $seasonId)
            ->where($db->quoteName('persontype') . ' = ' . $personType);
        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }

    private function relationService(): ProjectRelationService
    {
        return $this->relations ??= new ProjectRelationService($this->getDatabase());
    }
}
