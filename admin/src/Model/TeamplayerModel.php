<?php
/**
 * Native Joomla 5/6 administrator form model for team-person assignments.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 administrator form model for team-person assignments. */
final class TeamplayerModel extends SportsManagementAdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/forms');

        $form = $this->loadForm(
            'com_sportsmanagement.teamplayer',
            'teamplayer',
            ['control' => 'jform', 'load_data' => $loadData]
        );

        if (!$form) {
            return false;
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');
        if ($form->getField('picture')) {
            $form->setFieldAttribute('picture', 'default', (string) $params->get('ph_player', ''));
            $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/teamplayers');
            $form->setFieldAttribute('picture', 'type', (string) $params->get('cfg_which_media_tool', 0));
        }

        return $form;
    }

    public function getTable($type = 'teamplayer', $prefix = 'sportsmanagementTable', $config = [])
    {
        $config['dbo'] = $this->getDatabase();
        return Table::getInstance($type, $prefix, $config);
    }

    /** Copy the owning club country to all selected team persons for a season. */
    public function assignplayerscountry($persontype = 1, $projectTeamId = 0, $teamId = 0, $projectId = 0, $seasonId = 0): bool
    {
        $teamId = max(0, (int) $teamId);
        $seasonId = max(0, (int) $seasonId);
        $personType = max(0, (int) $persontype);

        if ($teamId === 0 || $seasonId === 0) {
            return true;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('c.country'))
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON t.club_id = c.id')
            ->where($db->quoteName('t.id') . ' = :teamId')
            ->bind(':teamId', $teamId, ParameterType::INTEGER);
        $country = (string) $db->setQuery($query, 0, 1)->loadResult();

        if ($country === '') {
            return true;
        }

        $query = $db->getQuery(true)
            ->select($db->quoteName('person_id'))
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id'))
            ->where($db->quoteName('team_id') . ' = :relationTeamId')
            ->where($db->quoteName('season_id') . ' = :seasonId')
            ->where($db->quoteName('persontype') . ' = :personType')
            ->bind(':relationTeamId', $teamId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER)
            ->bind(':personType', $personType, ParameterType::INTEGER);
        $personIds = array_values(array_unique(array_filter(array_map('intval', $db->setQuery($query)->loadColumn()))));

        if (!$personIds) {
            return true;
        }

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

    /** Publish/unpublish both season-team and project-position person relations. */
    public function set_state($ids, $tpids, $state, $pid = 0): bool
    {
        $personIds = $this->normaliseIds((array) $ids);
        $mapping = (array) $tpids;
        $state = (int) $state;
        $projectId = max(0, (int) $pid);
        $db = $this->getDatabase();
        $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $userId = (int) $this->administratorApplication()->getIdentity()->id;
        $db->transactionStart();

        try {
            foreach ($personIds as $personId) {
                $assignmentId = max(0, (int) ($mapping[$personId] ?? 0));
                if ($assignmentId > 0) {
                    $db->updateObject(
                        '#__sportsmanagement_season_team_person_id',
                        (object) ['id' => $assignmentId, 'published' => $state, 'modified' => $now, 'modified_by' => $userId],
                        'id'
                    );
                }

                if ($projectId > 0) {
                    $query = $db->getQuery(true)
                        ->update($db->quoteName('#__sportsmanagement_person_project_position'))
                        ->set($db->quoteName('published') . ' = :published')
                        ->set($db->quoteName('modified') . ' = :modified')
                        ->set($db->quoteName('modified_by') . ' = :modifiedBy')
                        ->where($db->quoteName('person_id') . ' = :personId')
                        ->where($db->quoteName('project_id') . ' = :projectId')
                        ->bind(':published', $state, ParameterType::INTEGER)
                        ->bind(':modified', $now, ParameterType::STRING)
                        ->bind(':modifiedBy', $userId, ParameterType::INTEGER)
                        ->bind(':personId', $personId, ParameterType::INTEGER)
                        ->bind(':projectId', $projectId, ParameterType::INTEGER);
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

    /** Update selected roster rows and their project-position relations. */
    public function saveshort(): bool
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $post = $input->post->getArray();
        $personIds = $this->normaliseIds((array) ($post['cid'] ?? []));
        $projectId = max(0, (int) ($post['pid'] ?? 0));
        $personType = max(0, (int) ($post['persontype'] ?? 0));

        if (!$personIds) {
            return true;
        }

        $db = $this->getDatabase();
        $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $userId = (int) $app->getIdentity()->id;
        $projectPositionMap = $this->getProjectPositionMap($projectId);
        $db->transactionStart();

        try {
            foreach ($personIds as $personId) {
                $assignmentId = max(0, (int) (($post['tpid'][$personId] ?? 0) ?: ($post['person_id' . $personId] ?? 0)));
                $projectPositionId = max(0, (int) ($post['project_position_id' . $personId] ?? 0));
                $positionId = (int) ($projectPositionMap[$projectPositionId] ?? 0);

                if ($assignmentId <= 0) {
                    continue;
                }

                if ($positionId > 0 && in_array($personType, [1, 2], true)) {
                    $table = $personType === 1 ? '#__sportsmanagement_match_player' : '#__sportsmanagement_match_staff';
                    $foreignKey = $personType === 1 ? 'teamplayer_id' : 'team_staff_id';
                    $query = $db->getQuery(true)
                        ->update($db->quoteName($table))
                        ->set($db->quoteName('project_position_id') . ' = :positionId')
                        ->where($db->quoteName('project_position_id') . ' = 0')
                        ->where($db->quoteName($foreignKey) . ' = :assignmentId')
                        ->bind(':positionId', $positionId, ParameterType::INTEGER)
                        ->bind(':assignmentId', $assignmentId, ParameterType::INTEGER);
                    $db->setQuery($query)->execute();
                }

                $row = (object) [
                    'id' => $assignmentId,
                    'project_position_id' => $projectPositionId,
                    'jerseynumber' => (int) ($post['jerseynumber' . $personId] ?? 0),
                    'market_value' => (int) ($post['market_value' . $personId] ?? 0),
                    'market_text' => trim((string) ($post['market_text' . $personId] ?? '')),
                    'tt_startpoints' => (int) ($post['tt_startpoints' . $personId] ?? 0),
                    'modified' => $now,
                    'modified_by' => $userId,
                ];
                $db->updateObject('#__sportsmanagement_season_team_person_id', $row, 'id');

                if ($projectId > 0) {
                    $published = ($post['project_published' . $personId] ?? '') === ''
                        ? 1
                        : (int) $post['project_published' . $personId];
                    $this->replaceProjectPosition($personId, $projectId, $projectPositionId, $personType, $published, $now, $userId);
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

    /** Delete roster assignments and their dependent match/project rows. */
    public function delete(&$pks)
    {
        $input = $this->administratorApplication()->getInput();
        $post = $input->post->getArray();
        $personIds = $this->normaliseIds((array) $pks);
        $mapping = (array) ($post['tpid'] ?? []);
        $assignmentIds = [];

        foreach ($personIds as $personId) {
            $assignmentId = max(0, (int) ($mapping[$personId] ?? 0));
            if ($assignmentId > 0) {
                $assignmentIds[] = $assignmentId;
            }
        }
        $assignmentIds = array_values(array_unique($assignmentIds));

        if (!$assignmentIds) {
            return true;
        }

        $db = $this->getDatabase();
        $projectId = max(0, (int) ($post['pid'] ?? 0));
        $db->transactionStart();

        try {
            $deleteSpecs = [
                ['#__sportsmanagement_match_player', 'teamplayer_id'],
                ['#__sportsmanagement_match_player', 'in_for'],
                ['#__sportsmanagement_match_staff', 'team_staff_id'],
                ['#__sportsmanagement_match_statistic', 'teamplayer_id'],
                ['#__sportsmanagement_match_staff_statistic', 'team_staff_id'],
                ['#__sportsmanagement_match_event', 'teamplayer_id'],
                ['#__sportsmanagement_match_event', 'teamplayer_id2'],
            ];

            foreach ($deleteSpecs as [$table, $column]) {
                $query = $db->getQuery(true)
                    ->delete($db->quoteName($table))
                    ->whereIn($db->quoteName($column), $assignmentIds, ParameterType::INTEGER);
                $db->setQuery($query)->execute();
            }

            if ($projectId > 0 && $personIds) {
                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__sportsmanagement_person_project_position'))
                    ->whereIn($db->quoteName('person_id'), $personIds, ParameterType::INTEGER)
                    ->where($db->quoteName('project_id') . ' = :projectId')
                    ->bind(':projectId', $projectId, ParameterType::INTEGER);
                $db->setQuery($query)->execute();
            }

            if (!parent::delete($assignmentIds)) {
                throw new \RuntimeException($this->getError() ?: 'Unable to remove team persons.');
            }

            $db->transactionCommit();
            $pks = $personIds;
            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());
            return false;
        }
    }

    /** Persist roster data plus the person-level status fields shown in this form. */
    public function save($data)
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $post = $input->post->getArray();
        $seasonId = max(0, (int) ($data['season_id'] ?? $app->getUserState('com_sportsmanagement.season_id', 0)));
        $projectId = max(0, (int) ($post['pid'] ?? $app->getUserState('com_sportsmanagement.pid', 0)));
        $personId = max(0, (int) ($data['person_id'] ?? 0));
        $personType = max(0, (int) ($data['persontype'] ?? $app->getUserState('com_sportsmanagement.persontype', 0)));
        $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $userId = (int) $app->getIdentity()->id;

        $extended = $input->post->get('extended', [], 'array');
        if ($extended) {
            $registry = new Registry();
            $registry->loadArray($extended);
            $data['extended'] = $registry->toString();
        }

        foreach (['contract_from', 'contract_to'] as $field) {
            $data[$field] = $this->normaliseDate((string) ($data[$field] ?? ''));
        }

        $db = $this->getDatabase();
        $db->transactionStart();

        try {
            if ($personId > 0) {
                $personFields = [
                    'injury', 'injury_date', 'injury_end', 'injury_detail', 'injury_date_start', 'injury_date_end',
                    'suspension', 'suspension_date', 'suspension_end', 'suspension_detail', 'susp_date_start', 'susp_date_end',
                    'away', 'away_date', 'away_end', 'away_detail', 'away_date_start', 'away_date_end',
                ];
                $person = (object) ['id' => $personId, 'modified' => $now, 'modified_by' => $userId];

                foreach ($personFields as $field) {
                    if (!array_key_exists($field, $data)) {
                        continue;
                    }
                    $value = $data[$field];
                    if (str_ends_with($field, '_start') || str_ends_with($field, '_end')) {
                        $value = $this->normaliseDate((string) $value);
                    } elseif (in_array($field, ['injury_date', 'injury_end', 'suspension_date', 'suspension_end', 'away_date', 'away_end'], true) && $value === '') {
                        $value = 0;
                    }
                    $person->{$field} = $value;
                    unset($data[$field]);
                }
                $db->updateObject('#__sportsmanagement_person', $person, 'id');

                if ($seasonId > 0 && array_key_exists('picture', $data)) {
                    $picture = (string) $data['picture'];
                    $query = $db->getQuery(true)
                        ->update($db->quoteName('#__sportsmanagement_season_person_id'))
                        ->set($db->quoteName('picture') . ' = :picture')
                        ->set($db->quoteName('modified') . ' = :modified')
                        ->set($db->quoteName('modified_by') . ' = :modifiedBy')
                        ->where($db->quoteName('person_id') . ' = :personId')
                        ->where($db->quoteName('season_id') . ' = :seasonId')
                        ->bind(':picture', $picture, ParameterType::STRING)
                        ->bind(':modified', $now, ParameterType::STRING)
                        ->bind(':modifiedBy', $userId, ParameterType::INTEGER)
                        ->bind(':personId', $personId, ParameterType::INTEGER)
                        ->bind(':seasonId', $seasonId, ParameterType::INTEGER);
                    $db->setQuery($query)->execute();
                }
            }

            if (!parent::save($data)) {
                throw new \RuntimeException($this->getError() ?: 'Unable to save team person.');
            }

            if ($personId > 0 && $projectId > 0) {
                $this->replaceProjectPosition(
                    $personId,
                    $projectId,
                    max(0, (int) ($data['project_position_id'] ?? 0)),
                    $personType,
                    1,
                    $now,
                    $userId
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

    public function getPerson(int $personId): ?object
    {
        if ($personId <= 0) {
            return null;
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('p') . '.*')
            ->from($db->quoteName('#__sportsmanagement_person', 'p'))
            ->where($db->quoteName('p.id') . ' = :personId')
            ->bind(':personId', $personId, ParameterType::INTEGER);
        return $db->setQuery($query, 0, 1)->loadObject() ?: null;
    }

    public function getProject(int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('p') . '.*')
            ->select($db->quoteName('st.name', 'sport_type_name'))
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_sports_type', 'st') . ' ON st.id = p.sports_type_id')
            ->where($db->quoteName('p.id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        return $db->setQuery($query, 0, 1)->loadObject() ?: null;
    }

    public function getProjectPositions(int $projectId, int $personType): array
    {
        $projectId = max(0, $projectId);
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('pp.id AS value, pp.position_id, pos.name AS text')
            ->from($db->quoteName('#__sportsmanagement_project_position', 'pp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON pos.id = pp.position_id')
            ->where($db->quoteName('pp.project_id') . ' = :projectId')
            ->where($db->quoteName('pp.published') . ' = 1')
            ->order($db->quoteName('pos.name') . ' ASC')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    private function replaceProjectPosition(
        int $personId,
        int $projectId,
        int $projectPositionId,
        int $personType,
        int $published,
        string $now,
        int $userId
    ): void {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__sportsmanagement_person_project_position'))
            ->where($db->quoteName('person_id') . ' = :personId')
            ->where($db->quoteName('project_id') . ' = :projectId')
            ->where($db->quoteName('persontype') . ' = :personType')
            ->bind(':personId', $personId, ParameterType::INTEGER)
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':personType', $personType, ParameterType::INTEGER);
        $db->setQuery($query)->execute();

        $db->insertObject('#__sportsmanagement_person_project_position', (object) [
            'person_id' => $personId,
            'project_id' => $projectId,
            'project_position_id' => $projectPositionId,
            'persontype' => $personType,
            'published' => $published,
            'modified' => $now,
            'modified_by' => $userId,
        ]);
    }

    private function getProjectPositionMap(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('id, position_id')
            ->from($db->quoteName('#__sportsmanagement_project_position'))
            ->where($db->quoteName('project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $map = [];
        foreach ($db->setQuery($query)->loadObjectList() as $row) {
            $map[(int) $row->id] = (int) $row->position_id;
        }
        return $map;
    }

    private function normaliseDate(string $value): string
    {
        $value = trim($value);
        if ($value === '' || $value === '0000-00-00') {
            return '0000-00-00';
        }
        foreach (['!Y-m-d', '!d-m-Y', '!d.m.Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);
            if ($date instanceof \DateTimeImmutable) {
                return $date->format('Y-m-d');
            }
        }
        $timestamp = strtotime($value);
        return $timestamp === false ? '0000-00-00' : date('Y-m-d', $timestamp);
    }

    private function normaliseIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
    }
}
