<?php
/**
 * Native Joomla 5/6 administrator list model for team players and staff.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\ProjectRelationService;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;

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
        $app = $this->administratorApplication();
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

        if ($projectId > 0) {
            $app->setUserState('com_sportsmanagement.pid', $projectId);
        }
        if ($projectTeamId > 0) {
            $app->setUserState('com_sportsmanagement.project_team_id', $projectTeamId);
        }
        if ($personType > 0) {
            $app->setUserState('com_sportsmanagement.persontype', $personType);
        }
        if ($seasonId > 0) {
            $app->setUserState('com_sportsmanagement.season_id', $seasonId);
        }
        if ($teamId > 0) {
            $app->setUserState('com_sportsmanagement.team_id', $teamId);
        }
        if ($seasonTeamId > 0) {
            $app->setUserState('com_sportsmanagement.season_team_id', $seasonTeamId);
        }
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $projectId = (int) $this->getState('filter.pid');
        $teamId = (int) $this->getState('filter.team_id');
        $seasonId = (int) $this->getState('filter.season_id');
        $personType = (int) $this->getState('filter.persontype');

        $projectPosition = '(SELECT ppp.project_position_id FROM ' . $db->quoteName('#__sportsmanagement_person_project_position') . ' AS ppp'
            . ' WHERE ppp.person_id = ppl.id AND ppp.project_id = :positionProjectId AND ppp.persontype = :positionPersonType'
            . ' ORDER BY ppp.published DESC, ppp.project_position_id ASC LIMIT 1)';
        $projectPublished = '(SELECT ppp.published FROM ' . $db->quoteName('#__sportsmanagement_person_project_position') . ' AS ppp'
            . ' WHERE ppp.person_id = ppl.id AND ppp.project_id = :publishedProjectId AND ppp.persontype = :publishedPersonType'
            . ' ORDER BY ppp.published DESC, ppp.project_position_id ASC LIMIT 1)';

        $query = $db->createQuery()
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
            ->where($db->quoteName('tp.team_id') . ' = :teamId')
            ->where($db->quoteName('tp.season_id') . ' = :seasonId')
            ->where($db->quoteName('tp.persontype') . ' = :personType')
            ->where($db->quoteName('ppl.published') . ' = 1')
            ->bind(':positionProjectId', $projectId, ParameterType::INTEGER)
            ->bind(':positionPersonType', $personType, ParameterType::INTEGER)
            ->bind(':publishedProjectId', $projectId, ParameterType::INTEGER)
            ->bind(':publishedPersonType', $personType, ParameterType::INTEGER)
            ->bind(':teamId', $teamId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER)
            ->bind(':personType', $personType, ParameterType::INTEGER);

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            $token = '%' . $db->escape(mb_strtolower($search), true) . '%';
            $query->where(
                '(LOWER(' . $db->quoteName('ppl.lastname') . ') LIKE :searchLastname'
                . ' OR LOWER(' . $db->quoteName('ppl.firstname') . ') LIKE :searchFirstname'
                . ' OR LOWER(' . $db->quoteName('ppl.nickname') . ') LIKE :searchNickname)'
            )
                ->bind(':searchLastname', $token, ParameterType::STRING)
                ->bind(':searchFirstname', $token, ParameterType::STRING)
                ->bind(':searchNickname', $token, ParameterType::STRING);
        }

        $state = $this->getState('filter.state');
        if ($state !== '' && is_numeric($state)) {
            $published = (int) $state;
            $query->where($db->quoteName('tp.published') . ' = :published')
                ->bind(':published', $published, ParameterType::INTEGER);
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

    public function getprojectpublished($items = null)
    {
        return is_array($items) ? $items : [];
    }

    public function getprojectposition($items = null)
    {
        return is_array($items) ? $items : [];
    }

    public function PersonProjectPosition($projectId, $personType): array
    {
        $projectId = max(0, (int) $projectId);
        $personType = max(0, (int) $personType);
        if ($projectId === 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('ppp') . '.*')
            ->from($db->quoteName('#__sportsmanagement_person_project_position', 'ppp'))
            ->where($db->quoteName('ppp.project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);

        if ($personType > 0) {
            $query->where($db->quoteName('ppp.persontype') . ' = :personType')
                ->bind(':personType', $personType, ParameterType::INTEGER);
        }

        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    public function checkProjectPositions($projectId, $personType, $teamId, $seasonId, $insert = 1): bool
    {
        $projectId = max(0, (int) $projectId);
        $personType = max(0, (int) $personType);
        $teamId = max(0, (int) $teamId);
        $seasonId = max(0, (int) $seasonId);
        if (!$projectId || !$teamId || !$seasonId) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('stp.person_id, ppos.id AS project_position_id')
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'stp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON p.id = stp.person_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ppos.position_id = p.position_id')
            ->where($db->quoteName('stp.team_id') . ' = :teamId')
            ->where($db->quoteName('stp.season_id') . ' = :seasonId')
            ->where($db->quoteName('stp.persontype') . ' = :personType')
            ->where($db->quoteName('ppos.project_id') . ' = :projectId')
            ->bind(':teamId', $teamId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER)
            ->bind(':personType', $personType, ParameterType::INTEGER)
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $rows = $db->setQuery($query)->loadObjectList();
        if (!$rows) {
            return false;
        }
        if (!(int) $insert) {
            return true;
        }

        $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $userId = (int) $this->administratorApplication()->getIdentity()->id;
        $db->transactionStart();
        try {
            foreach ($rows as $row) {
                $personId = (int) $row->person_id;
                $projectPositionId = (int) $row->project_position_id;
                $check = $db->createQuery()
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__sportsmanagement_person_project_position'))
                    ->where($db->quoteName('person_id') . ' = :checkPersonId')
                    ->where($db->quoteName('project_id') . ' = :checkProjectId')
                    ->where($db->quoteName('project_position_id') . ' = :checkPositionId')
                    ->where($db->quoteName('persontype') . ' = :checkPersonType')
                    ->bind(':checkPersonId', $personId, ParameterType::INTEGER)
                    ->bind(':checkProjectId', $projectId, ParameterType::INTEGER)
                    ->bind(':checkPositionId', $projectPositionId, ParameterType::INTEGER)
                    ->bind(':checkPersonType', $personType, ParameterType::INTEGER);
                if ($db->setQuery($check, 0, 1)->loadResult()) {
                    continue;
                }
                $db->insertObject('#__sportsmanagement_person_project_position', (object) [
                    'person_id' => $personId,
                    'project_id' => $projectId,
                    'project_position_id' => $projectPositionId,
                    'persontype' => $personType,
                    'published' => 1,
                    'modified' => $now,
                    'modified_by' => $userId,
                ]);
            }
            $db->transactionCommit();
            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());
            return false;
        }
    }

    public function saveShort(): bool
    {
        $app = $this->administratorApplication();
        $post = $app->getInput()->post->getArray();
        $ids = $this->normaliseIds((array) ($post['cid'] ?? []));
        if (!$ids) {
            $this->setError(Text::_('JGLOBAL_NO_MATCHING_RESULTS'));
            return false;
        }

        $db = $this->getDatabase();
        $projectId = (int) $this->getState('filter.pid');
        $teamId = (int) $this->getState('filter.team_id');
        $seasonId = (int) $this->getState('filter.season_id');
        $personType = (int) $this->getState('filter.persontype');
        $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $userId = (int) $app->getIdentity()->id;
        $db->transactionStart();
        try {
            foreach ($ids as $relationId) {
                $row = $this->loadValidatedRelation($relationId, $teamId, $seasonId, $personType);
                if (!$row) {
                    throw new \RuntimeException('Invalid team-person relation.');
                }

                $projectPositionId = max(0, (int) ($post['project_position_id'][$relationId] ?? 0));
                $projectPosition = $projectPositionId > 0
                    ? $this->relationService()->getProjectPosition($projectPositionId, $projectId, $personType)
                    : null;
                if ($projectPositionId > 0 && !$projectPosition) {
                    throw new \RuntimeException('Invalid project position.');
                }

                $object = (object) [
                    'id' => $relationId,
                    'project_position_id' => $projectPositionId,
                    'jerseynumber' => max(0, (int) ($post['jerseynumber'][$relationId] ?? 0)),
                    'market_value' => max(0, (int) ($post['market_value'][$relationId] ?? 0)),
                    'market_text' => substr(trim(strip_tags((string) ($post['market_text'][$relationId] ?? ''))), 0, 50),
                    'tt_startpoints' => (int) ($post['tt_startpoints'][$relationId] ?? 0),
                    'modified' => $now,
                    'modified_by' => $userId,
                ];
                $db->updateObject('#__sportsmanagement_season_team_person_id', $object, 'id');
                $this->replaceProjectPosition(
                    (int) $row->person_id,
                    $projectId,
                    $projectPositionId,
                    $personType,
                    (int) ($post['project_published'][$relationId] ?? 1),
                    $now,
                    $userId
                );

                $basePositionId = (int) ($projectPosition->position_id ?? 0);
                if ($basePositionId > 0) {
                    $matchTable = $personType === 2 ? '#__sportsmanagement_match_staff' : '#__sportsmanagement_match_player';
                    $memberField = $personType === 2 ? 'team_staff_id' : 'teamplayer_id';
                    $query = $db->createQuery()
                        ->update($db->quoteName($matchTable))
                        ->set($db->quoteName('project_position_id') . ' = :basePositionId')
                        ->where($db->quoteName('project_position_id') . ' = 0')
                        ->where($db->quoteName($memberField) . ' = :relationId')
                        ->bind(':basePositionId', $basePositionId, ParameterType::INTEGER)
                        ->bind(':relationId', $relationId, ParameterType::INTEGER);
                    $db->setQuery($query)->execute();
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

    public function setRelationState(int $state): bool
    {
        $app = $this->administratorApplication();
        $ids = $this->normaliseIds((array) $app->getInput()->post->get('cid', [], 'array'));
        if (!$ids) {
            return false;
        }

        $db = $this->getDatabase();
        $projectId = (int) $this->getState('filter.pid');
        $teamId = (int) $this->getState('filter.team_id');
        $seasonId = (int) $this->getState('filter.season_id');
        $personType = (int) $this->getState('filter.persontype');
        $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $userId = (int) $app->getIdentity()->id;
        $db->transactionStart();
        try {
            foreach ($ids as $relationId) {
                $row = $this->loadValidatedRelation($relationId, $teamId, $seasonId, $personType);
                if (!$row) {
                    throw new \RuntimeException('Invalid team-person relation.');
                }
                $db->updateObject(
                    '#__sportsmanagement_season_team_person_id',
                    (object) ['id' => $relationId, 'published' => $state, 'modified' => $now, 'modified_by' => $userId],
                    'id'
                );
                $personId = (int) $row->person_id;
                $query = $db->createQuery()
                    ->update($db->quoteName('#__sportsmanagement_person_project_position'))
                    ->set($db->quoteName('published') . ' = :state')
                    ->set($db->quoteName('modified') . ' = :modified')
                    ->set($db->quoteName('modified_by') . ' = :modifiedBy')
                    ->where($db->quoteName('person_id') . ' = :personId')
                    ->where($db->quoteName('project_id') . ' = :projectId')
                    ->where($db->quoteName('persontype') . ' = :personType')
                    ->bind(':state', $state, ParameterType::INTEGER)
                    ->bind(':modified', $now, ParameterType::STRING)
                    ->bind(':modifiedBy', $userId, ParameterType::INTEGER)
                    ->bind(':personId', $personId, ParameterType::INTEGER)
                    ->bind(':projectId', $projectId, ParameterType::INTEGER)
                    ->bind(':personType', $personType, ParameterType::INTEGER);
                $db->setQuery($query)->execute();
            }
            $db->transactionCommit();
            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());
            return false;
        }
    }

    public function assignPlayersCountry(): bool
    {
        $teamId = (int) $this->getState('filter.team_id');
        $seasonId = (int) $this->getState('filter.season_id');
        $personType = (int) $this->getState('filter.persontype');
        if (!$teamId || !$seasonId) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('c.country'))
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON t.club_id = c.id')
            ->where($db->quoteName('t.id') . ' = :teamId')
            ->bind(':teamId', $teamId, ParameterType::INTEGER);
        $country = (string) $db->setQuery($query, 0, 1)->loadResult();
        if ($country === '') {
            return false;
        }

        $query = $db->createQuery()
            ->select($db->quoteName('person_id'))
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id'))
            ->where($db->quoteName('team_id') . ' = :relationTeamId')
            ->where($db->quoteName('season_id') . ' = :seasonId')
            ->where($db->quoteName('persontype') . ' = :personType')
            ->bind(':relationTeamId', $teamId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER)
            ->bind(':personType', $personType, ParameterType::INTEGER);
        $personIds = $this->normaliseIds($db->setQuery($query)->loadColumn());
        $db->transactionStart();
        try {
            foreach ($personIds as $personId) {
                $db->updateObject('#__sportsmanagement_person', (object) ['id' => $personId, 'country' => $country], 'id');
            }
            $db->transactionCommit();
            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());
            return false;
        }
    }

    public function deleteRelations(): bool
    {
        $app = $this->administratorApplication();
        $ids = $this->normaliseIds((array) $app->getInput()->post->get('cid', [], 'array'));
        if (!$ids) {
            return false;
        }

        $db = $this->getDatabase();
        $teamId = (int) $this->getState('filter.team_id');
        $seasonId = (int) $this->getState('filter.season_id');
        $personType = (int) $this->getState('filter.persontype');
        $projectId = (int) $this->getState('filter.pid');
        $valid = [];
        foreach ($ids as $id) {
            $row = $this->loadValidatedRelation($id, $teamId, $seasonId, $personType);
            if ($row) {
                $valid[$id] = (int) $row->person_id;
            }
        }
        if (!$valid) {
            return false;
        }

        $relationIds = array_map('intval', array_keys($valid));
        $personIds = array_values(array_map('intval', $valid));
        $db->transactionStart();
        try {
            foreach ([
                ['#__sportsmanagement_match_player', 'teamplayer_id'],
                ['#__sportsmanagement_match_player', 'in_for'],
                ['#__sportsmanagement_match_staff', 'team_staff_id'],
                ['#__sportsmanagement_match_statistic', 'teamplayer_id'],
                ['#__sportsmanagement_match_staff_statistic', 'team_staff_id'],
                ['#__sportsmanagement_match_event', 'teamplayer_id'],
                ['#__sportsmanagement_match_event', 'teamplayer_id2'],
            ] as [$table, $column]) {
                $query = $db->createQuery()
                    ->delete($db->quoteName($table))
                    ->whereIn($db->quoteName($column), $relationIds, ParameterType::INTEGER);
                $db->setQuery($query)->execute();
            }

            if ($projectId > 0) {
                $query = $db->createQuery()
                    ->delete($db->quoteName('#__sportsmanagement_person_project_position'))
                    ->whereIn($db->quoteName('person_id'), $personIds, ParameterType::INTEGER)
                    ->where($db->quoteName('project_id') . ' = :projectId')
                    ->where($db->quoteName('persontype') . ' = :personType')
                    ->bind(':projectId', $projectId, ParameterType::INTEGER)
                    ->bind(':personType', $personType, ParameterType::INTEGER);
                $db->setQuery($query)->execute();
            }

            $query = $db->createQuery()
                ->delete($db->quoteName('#__sportsmanagement_season_team_person_id'))
                ->whereIn($db->quoteName('id'), $relationIds, ParameterType::INTEGER);
            $db->setQuery($query)->execute();
            $db->transactionCommit();
            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());
            return false;
        }
    }

    public function getTeamplayersMatch($teamId = 0, $seasonId = 0, $projectTeamId = 0, $projectId = 0, $matchId = 0): array
    {
        $seasonId = max(0, (int) $seasonId);
        $projectTeamId = max(0, (int) $projectTeamId);
        $matchId = max(0, (int) $matchId);
        if (!$seasonId || !$projectTeamId || !$matchId) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('tp.id'))
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.team_id=tp.team_id AND st.season_id=tp.season_id')
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON pt.team_id=st.id')
            ->where($db->quoteName('pt.id') . ' = :projectTeamId')
            ->where($db->quoteName('tp.season_id') . ' = :seasonId')
            ->where($db->quoteName('tp.persontype') . ' = 1')
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER);
        $ids = $this->normaliseIds($db->setQuery($query)->loadColumn());
        if (!$ids) {
            return [];
        }

        $query = $db->createQuery()
            ->select('mp.teamplayer_id, mp.project_position_id, pos.name AS project_position_name')
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON pos.id=mp.project_position_id')
            ->where($db->quoteName('mp.match_id') . ' = :matchId')
            ->where('(mp.came_in=0 OR mp.came_in=1)')
            ->whereIn($db->quoteName('mp.teamplayer_id'), $ids, ParameterType::INTEGER)
            ->bind(':matchId', $matchId, ParameterType::INTEGER);
        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    public function getProjectTeamplayers($teamId = 0, $seasonId = 0, $projectTeamId = 0, $generate = 0, $projectId = 0): array
    {
        $teamId = max(0, (int) $teamId);
        $seasonId = max(0, (int) $seasonId);
        $projectTeamId = max(0, (int) $projectTeamId);
        $projectId = max(0, (int) $projectId);
        if (!$seasonId) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('ppl.*, tp.id AS season_team_person_id')
            ->from($db->quoteName('#__sportsmanagement_person', 'ppl'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON tp.person_id=ppl.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.team_id=tp.team_id AND st.season_id=tp.season_id')
            ->where($db->quoteName('st.season_id') . ' = :seasonTeamSeasonId')
            ->where($db->quoteName('tp.season_id') . ' = :personSeasonId')
            ->bind(':seasonTeamSeasonId', $seasonId, ParameterType::INTEGER)
            ->bind(':personSeasonId', $seasonId, ParameterType::INTEGER);

        if ($teamId) {
            $query->where($db->quoteName('st.team_id') . ' = :teamId')
                ->bind(':teamId', $teamId, ParameterType::INTEGER);
        }
        if ($projectTeamId) {
            $query->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON pt.team_id=st.id')
                ->where($db->quoteName('pt.id') . ' = :projectTeamId')
                ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER);
        }
        if ((int) $generate && $projectId) {
            $sub = $db->createQuery()
                ->select($db->quoteName('ppp.project_position_id'))
                ->from($db->quoteName('#__sportsmanagement_person_project_position', 'ppp'))
                ->where('ppp.person_id=ppl.id')
                ->where($db->quoteName('ppp.project_id') . ' = :generatedProjectId')
                ->where($db->quoteName('ppp.persontype') . ' = 1');
            $query->select('(' . $sub . ') AS project_position_id')
                ->bind(':generatedProjectId', $projectId, ParameterType::INTEGER);
        }

        return $db->setQuery($query)->loadObjectList() ?: [];
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

    private function replaceProjectPosition(int $personId, int $projectId, int $projectPositionId, int $personType, int $published, string $now, int $userId): void
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->delete($db->quoteName('#__sportsmanagement_person_project_position'))
            ->where($db->quoteName('person_id') . ' = :personId')
            ->where($db->quoteName('project_id') . ' = :projectId')
            ->where($db->quoteName('persontype') . ' = :personType')
            ->bind(':personId', $personId, ParameterType::INTEGER)
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':personType', $personType, ParameterType::INTEGER);
        $db->setQuery($query)->execute();

        if ($projectPositionId > 0) {
            $db->insertObject('#__sportsmanagement_person_project_position', (object) [
                'person_id' => $personId,
                'project_id' => $projectId,
                'project_position_id' => $projectPositionId,
                'persontype' => $personType,
                'published' => $published === 0 ? 0 : 1,
                'modified' => $now,
                'modified_by' => $userId,
            ]);
        }
    }

    private function loadValidatedRelation(int $relationId, int $teamId, int $seasonId, int $personType): ?object
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('id, person_id')
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id'))
            ->where($db->quoteName('id') . ' = :relationId')
            ->where($db->quoteName('team_id') . ' = :teamId')
            ->where($db->quoteName('season_id') . ' = :seasonId')
            ->where($db->quoteName('persontype') . ' = :personType')
            ->bind(':relationId', $relationId, ParameterType::INTEGER)
            ->bind(':teamId', $teamId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER)
            ->bind(':personType', $personType, ParameterType::INTEGER);
        return $db->setQuery($query, 0, 1)->loadObject() ?: null;
    }

    private function normaliseIds(array $ids): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn (int $id): bool => $id > 0
        )));
    }

    private function relationService(): ProjectRelationService
    {
        return $this->relations ??= new ProjectRelationService($this->getDatabase());
    }
}
