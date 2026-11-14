<?php
/**
 * Native Joomla 5/6 administrator form model for projects.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Table\ProjectTable;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 administrator form model for projects. */
final class ProjectModel extends SportsManagementAdminModel
{
    public static int $db_num_rows = 0;

    /**
     * Load the Joomla administrator form for project editing.
     *
     * @param array $data data value.
     * @param bool $loadData loadData value.
     * @return mixed
     */
    public function getForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/forms');

        return $this->loadForm(
            'com_sportsmanagement.project',
            'project',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

    /**
     * Resolve the native project table.
     *
     * @param string $type type value.
     * @param string $prefix prefix value.
     * @param array $config config value.
     * @return mixed
     */
    public function getTable($type = 'Project', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'Project') === 0) {
            return new ProjectTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Normalise submitted project fields and modification metadata.
     *
     * @param array $data data value.
     * @return array
     */
    protected function prepareSportsManagementData(array $data): array
    {
        if (isset($data['name'])) {
            $data['name'] = trim((string) $data['name']);
            $data['alias'] = OutputFilter::stringURLSafe($data['name']);
        }

        $data['modified_timestamp'] = (new Date('now', new \DateTimeZone('UTC')))->toUnix();

        return $data;
    }

    /**
     * Toggle league-champion settings for the selected projects.
     *
     * Requires component edit permission at the model boundary so direct model
     * calls cannot bypass the token- and ACL-protected list controller.
     * Persists the selected flags with modification metadata using validated IDs.
     *
     * @return string|false Translated result or false on denial or failure.
     */
    public function setleaguechampion()
    {
        $app = $this->administratorApplication();

        if (!$this->canWriteProjects('core.edit')) {
            return false;
        }

        $ids = $this->normaliseIds($app->getInput()->post->get('cid', [], 'array'));

        if (!$ids) {
            return Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SAVE_NO_SELECT');
        }

        $post = $app->getInput()->post->getArray();
        $db = $this->getDatabase();
        $now = new Date('now', new \DateTimeZone('UTC'));
        $userId = (int) $app->getIdentity()->id;

        try {
            foreach ($ids as $id) {
                $current = (int) ($post['use_leaguechampion' . $id] ?? 0);
                $projectUpdate = (object) [
                    'id' => $id,
                    'use_leaguechampion' => $current ? 0 : 1,
                    'modified' => $now->toSql(),
                    'modified_timestamp' => $now->toUnix(),
                    'modified_by' => $userId,
                ];

                $db->updateObject('#__sportsmanagement_project', $projectUpdate, 'id');
            }

            return Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SAVE');
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return false;
        }
    }

    /**
     * Load the template configuration for a selected project.
     *
     * @param mixed $project_id project_id value.
     * @param mixed $template template value.
     * @param int $cfg_which_database cfg_which_database value.
     * @param string $call_function call_function value.
     * @return array
     */
    public static function getTemplateConfig($project_id, $template, $cfg_which_database = 0, $call_function = ''): array
    {
        $projectId = (int) $project_id;
        $templateName = basename(trim((string) $template));

        if ($projectId <= 0 || $templateName === '' || $templateName !== trim((string) $template)) {
            return [];
        }

        $db = self::sportsDatabase((int) $cfg_which_database);
        $app = self::backendApplication();
        $view = $app->getInput()->getCmd('view', '');
        $skipMasterFallback = in_array(
            $view,
            ['editmatch', 'editprojectteam', 'editteam', 'editperson', 'editclub', 'jltournamenttree'],
            true
        );

        $params = self::loadTemplateParams($db, $projectId, $templateName);

        if ($params === '' && !$skipMasterFallback) {
            $project = self::getProject($projectId);
            $masterId = (int) ($project->master_template ?? 0);

            if ($masterId > 0) {
                $params = self::loadTemplateParams($db, $masterId, $templateName);
            }
        }

        if ($params === '') {
            return [];
        }

        $registry = new Registry();
        $registry->loadString($params);

        return $registry->toArray();
    }

    /**
     * Find projects for a selected league and season.
     *
     * @param mixed $season_id season_id value.
     * @param mixed $league_id league_id value.
     * @return array
     */
    public static function getProjectsbyCurrentProjectLeagueSeason($season_id, $league_id): array
    {
        $seasonId = (int) $season_id;
        $leagueId = (int) $league_id;

        if ($seasonId <= 0 || $leagueId <= 0) {
            return [];
        }

        $db = self::sportsDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
                $db->quoteName('name', 'info'),
                $db->quoteName('picture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('season_id') . ' = :projectsSeasonId')
            ->where($db->quoteName('league_id') . ' = :projectsLeagueId')
            ->order($db->quoteName('name') . ' ASC')
            ->bind(':projectsSeasonId', $seasonId, ParameterType::INTEGER)
            ->bind(':projectsLeagueId', $leagueId, ParameterType::INTEGER);

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            self::backendApplication()->enqueueMessage($e->getMessage(), 'warning');

            return [];
        }
    }

    /**
     * Load a project record by identifier.
     *
     * @param mixed $project_id project_id value.
     * @return object|null
     */
    public static function getProject($project_id): ?object
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return null;
        }

        $db = self::sportsDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('p') . '.*',
                $db->quoteName('st.name', 'sport_type_name'),
                $db->quoteName('st.eventtime', 'useeventtime'),
                $db->quoteName('l.country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_sports_type', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('p.sports_type_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_league', 'l')
                . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id')
            )
            ->where($db->quoteName('p.id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);

        try {
            $db->setQuery($query, 0, 1);

            return $db->loadObject() ?: null;
        } catch (\Throwable $e) {
            self::backendApplication()->enqueueMessage($e->getMessage(), 'warning');

            return null;
        }
    }

    /**
     * Read the selected project-team details.
     *
     * @param mixed $projectteam_id projectteam_id value.
     * @return object|null
     */
    public function getProjectTeam($projectteam_id): ?object
    {
        $projectTeamId = (int) $projectteam_id;

        if ($projectTeamId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('t') . '.*')
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
            )
            ->where($db->quoteName('pt.id') . ' = :projectTeamId')
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER);

        try {
            $db->setQuery($query, 0, 1);

            return $db->loadObject() ?: null;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return null;
        }
    }

    /**
     * List available teams for a project and optional division.
     *
     * @param mixed $project_id project_id value.
     * @param int $iDivisionId iDivisionId value.
     * @return array
     */
    public function getProjectTeamsOptions($project_id, $iDivisionId = 0): array
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return [];
        }

        $project = self::getProject($projectId);
        $individual = (int) ($project->project_art_id ?? 0) === 3;
        $db = $this->getDatabase();

        if ($individual) {
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('pt.id', 'value'),
                    $db->quoteName('p.lastname'),
                    $db->quoteName('p.firstname'),
                    $db->quoteName('p.notes'),
                ])
                ->from($db->quoteName('#__sportsmanagement_person', 'p'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_season_person_id', 'sp')
                    . ' ON ' . $db->quoteName('sp.person_id') . ' = ' . $db->quoteName('p.id')
                )
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_project_team', 'pt')
                    . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('sp.id')
                );
        } else {
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('pt.id', 'value'),
                    $db->quoteName('t.name', 'text'),
                ])
                ->from($db->quoteName('#__sportsmanagement_team', 't'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                    . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id')
                )
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_project_team', 'pt')
                    . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
                );
        }

        $query->where($db->quoteName('pt.project_id') . ' = :projectTeamsProjectId')
            ->bind(':projectTeamsProjectId', $projectId, ParameterType::INTEGER);

        $divisionId = (int) $iDivisionId;
        if ($divisionId > 0) {
            $query->where($db->quoteName('pt.division_id') . ' = :projectTeamsDivisionId')
                ->bind(':projectTeamsDivisionId', $divisionId, ParameterType::INTEGER);
        }

        if ($individual) {
            $query->order([
                $db->quoteName('p.lastname') . ' ASC',
                $db->quoteName('p.firstname') . ' ASC',
            ]);
        } else {
            $query->order($db->quoteName('t.name') . ' ASC');
        }

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];

            if ($individual) {
                foreach ($rows as $row) {
                    $parts = [];

                    foreach (['lastname', 'firstname'] as $field) {
                        if ($row->{$field} !== null) {
                            $parts[] = (string) $row->{$field};
                        }
                    }

                    $row->text = implode(' - ', $parts);
                    unset($row->lastname, $row->firstname);
                }
            }

            return $rows;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return [];
        }
    }

    /**
     * Delete selected projects after checking the user's component ACL.
     *
     * Rejects missing or unauthorised identities before removing dependent rows.
     * Passes strictly validated project keys to the dependent-data cleanup and
     * then to Joomla's inherited project deletion.
     *
     * @param mixed $pks Project primary keys, passed by reference.
     * @return bool Whether the deletion was permitted and completed.
     */
    public function delete(&$pks)
    {
        if (!$this->canWriteProjects('core.delete')) {
            return false;
        }

        $ids = $this->normaliseIds($pks);

        if (!$ids) {
            return false;
        }

        if (!$this->deleteProjectsData($ids)) {
            return false;
        }

        $pks = $ids;
        $this->administratorApplication()->setUserState('com_sportsmanagement.pid', 0);

        return parent::delete($pks);
    }

    /**
     * Delete dependent round, match and project records before parent deletion.
     *
     * Checks component delete permission even for direct helper calls, resolves
     * dependent records and removes the related records transactionally.
     * A failed database operation rolls back the dependent-record cleanup.
     *
     * @param mixed $pk Project identifiers to clean up.
     * @return bool Whether the dependent-data cleanup completed.
     */
    public function deleteProjectsData($pk = []): bool
    {
        if (!$this->canWriteProjects('core.delete')) {
            return false;
        }

        $projectIds = $this->normaliseIds($pk);

        if (!$projectIds) {
            return true;
        }

        $db = $this->getDatabase();

        try {
            $roundIds = $this->loadIdsForProjects('#__sportsmanagement_round', 'project_id', $projectIds);
            $matchIds = $roundIds
                ? $this->loadIds('#__sportsmanagement_match', 'round_id', $roundIds)
                : [];

            $db->transactionStart();

            if ($matchIds) {
                foreach ([
                    '#__sportsmanagement_match_commentary',
                    '#__sportsmanagement_match_event',
                    '#__sportsmanagement_match_player',
                    '#__sportsmanagement_match_referee',
                    '#__sportsmanagement_match_single',
                    '#__sportsmanagement_match_staff',
                    '#__sportsmanagement_match_staff_statistic',
                    '#__sportsmanagement_match_statistic',
                ] as $table) {
                    $this->deleteFromExistingTable($table, 'match_id', $matchIds);
                }

                $this->deleteFromExistingTable('#__sportsmanagement_match', 'id', $matchIds);
            }

            foreach ([
                '#__sportsmanagement_person_project_position',
                '#__sportsmanagement_project_position',
                '#__sportsmanagement_project_referee',
                '#__sportsmanagement_project_team',
                '#__sportsmanagement_division',
            ] as $table) {
                $this->deleteFromExistingTable($table, 'project_id', $projectIds);
            }

            if ($roundIds) {
                $this->deleteFromExistingTable('#__sportsmanagement_round', 'id', $roundIds);
            }

            $db->transactionCommit();

            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());

            return false;
        }
    }

    /**
     * Duplicate selected projects for a requested target season.
     *
     * Requires component create permission before reading form data and opening
     * a transaction. Copies project records and extra field values while resetting
     * the new records' publication and checkout state.
     *
     * @return string|false Result message or false when denied or unsuccessful.
     */
    public function copy()
    {
        $app = $this->administratorApplication();

        if (!$this->canWriteProjects('core.create')) {
            return false;
        }

        $input = $app->getInput();
        $ids = $this->normaliseIds($input->post->get('cid', [], 'array'));

        if (!$ids) {
            return Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SAVE_NO_SELECT');
        }

        $filter = (array) $input->post->get('filter', [], 'array');
        $targetSeasonId = (int) ($filter['copytoseason'] ?? 0);
        $db = $this->getDatabase();

        try {
            $db->transactionStart();

            foreach ($ids as $id) {
                $query = $db->createQuery()
                    ->select('*')
                    ->from($db->quoteName('#__sportsmanagement_project'))
                    ->where($db->quoteName('id') . ' = :copyProjectId')
                    ->bind(':copyProjectId', $id, ParameterType::INTEGER);
                $db->setQuery($query, 0, 1);
                $source = $db->loadObject();

                if (!$source) {
                    continue;
                }

                unset($source->id);
                $source->extendeduser = null;

                if ($targetSeasonId > 0) {
                    $fromName = $this->getSeasonName((int) $source->season_id);
                    $toName = $this->getSeasonName($targetSeasonId);
                    $source->season_id = $targetSeasonId;

                    if ($fromName !== '' && $toName !== '') {
                        $source->name = str_replace($fromName, $toName, (string) $source->name);
                    }
                } else {
                    $source->name = trim((string) $source->name) . ' Kopie';
                }

                $source->alias = OutputFilter::stringURLSafe((string) $source->name);
                $source->published = 0;
                $source->checked_out = 0;
                $source->checked_out_time = $db->getNullDate();
                $source->modified = (new Date('now', new \DateTimeZone('UTC')))->toSql();
                $source->modified_timestamp = (new Date('now', new \DateTimeZone('UTC')))->toUnix();
                $source->modified_by = (int) $app->getIdentity()->id;
                $db->insertObject('#__sportsmanagement_project', $source);
                $newProjectId = (int) $db->insertid();
                $this->copyExtraFieldValues($id, $newProjectId);
            }

            $db->transactionCommit();

            return Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SAVE');
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());

            return false;
        }
    }

    /**
     * Persist selected project quick-edit fields from the administrator list.
     *
     * Requires an authenticated component editor even for direct model calls.
     * Normalises submitted project identifiers, updates project fields and
     * their extra values, and rolls back the transaction if a write fails.
     *
     * @return string|false Translated result or false on denial or failure.
     */
    public function saveshort()
    {
        $app = $this->administratorApplication();

        if (!$this->canWriteProjects('core.edit')) {
            return false;
        }

        $input = $app->getInput();
        $ids = $this->normaliseIds($input->post->get('cid', [], 'array'));

        if (!$ids) {
            return Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SAVE_NO_SELECT');
        }

        $post = $input->post->getArray();
        $db = $this->getDatabase();
        $now = new Date('now', new \DateTimeZone('UTC'));
        $userId = (int) $app->getIdentity()->id;

        try {
            $db->transactionStart();

            foreach ($ids as $id) {
                $this->ensureExtraFieldRows($id);
                $name = trim((string) ($post['new_project_name' . $id] ?? ''));
                $record = (object) [
                    'id' => $id,
                    'project_type' => (string) ($post['project_type' . $id] ?? ''),
                    'agegroup_id' => (int) ($post['agegroup' . $id] ?? 0),
                    'master_template' => (int) ($post['master_template' . $id] ?? 0),
                    'fast_projektteam' => (int) ($post['fast_projektteam' . $id] ?? 0),
                    'project_live_update' => (int) ($post['project_live_update' . $id] ?? 0),
                    'use_leaguechampion' => (int) ($post['use_leaguechampion' . $id] ?? 0),
                    'cr_project' => trim((string) ($post['cr_project' . $id] ?? '')),
                    'name' => $name,
                    'alias' => OutputFilter::stringURLSafe($name),
                    'modified' => $now->toSql(),
                    'modified_timestamp' => $now->toUnix(),
                    'modified_by' => $userId,
                ];

                $leagueId = (int) ($post['league' . $id] ?? 0);
                if ($leagueId > 0) {
                    $record->league_id = $leagueId;
                }

                $db->updateObject('#__sportsmanagement_project', $record, 'id');

                $extraValueId = (int) ($post['user_field_id' . $id] ?? 0);
                if ($extraValueId > 0) {
                    $extraFieldUpdate = (object) [
                        'id' => $extraValueId,
                        'fieldvalue' => trim((string) ($post['user_field' . $id] ?? '')),
                    ];

                    $db->updateObject(
                        '#__sportsmanagement_user_extra_fields_values',
                        $extraFieldUpdate,
                        'id'
                    );
                }
            }

            $db->transactionCommit();

            return Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SAVE');
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());

            return false;
        }
    }

    /**
     * Create missing project extra-field value rows.
     *
     * @param int $projectId projectId value.
     * @return void
     */
    private function ensureExtraFieldRows(int $projectId): void
    {
        $db = $this->getDatabase();
        $templateBackend = 'project';
        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_user_extra_fields'))
            ->where($db->quoteName('template_backend') . ' = :extraFieldTemplateBackend')
            ->bind(':extraFieldTemplateBackend', $templateBackend, ParameterType::STRING);
        $db->setQuery($query);

        foreach (array_map('intval', $db->loadColumn() ?: []) as $fieldId) {
            $check = $db->createQuery()
                ->select('COUNT(*)')
                ->from($db->quoteName('#__sportsmanagement_user_extra_fields_values'))
                ->where($db->quoteName('field_id') . ' = :extraFieldId')
                ->where($db->quoteName('jl_id') . ' = :extraFieldProjectId')
                ->bind(':extraFieldId', $fieldId, ParameterType::INTEGER)
                ->bind(':extraFieldProjectId', $projectId, ParameterType::INTEGER);
            $db->setQuery($check);

            if ((int) $db->loadResult() === 0) {
                $extraFieldValue = (object) [
                    'field_id' => $fieldId,
                    'jl_id' => $projectId,
                    'fieldvalue' => '',
                ];

                $db->insertObject('#__sportsmanagement_user_extra_fields_values', $extraFieldValue);
            }
        }
    }

    /**
     * Copy user extra field values between projects.
     *
     * @param int $sourceProjectId sourceProjectId value.
     * @param int $destinationProjectId destinationProjectId value.
     * @return void
     */
    private function copyExtraFieldValues(int $sourceProjectId, int $destinationProjectId): void
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_user_extra_fields_values'))
            ->where($db->quoteName('jl_id') . ' = :sourceExtraFieldProjectId')
            ->bind(':sourceExtraFieldProjectId', $sourceProjectId, ParameterType::INTEGER);
        $db->setQuery($query);

        foreach ($db->loadObjectList() ?: [] as $row) {
            unset($row->id);
            $row->jl_id = $destinationProjectId;
            $db->insertObject('#__sportsmanagement_user_extra_fields_values', $row);
        }
    }

    /**
     * Look up the display name of a season.
     *
     * @param int $seasonId seasonId value.
     * @return string
     */
    private function getSeasonName(int $seasonId): string
    {
        if ($seasonId <= 0) {
            return '';
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('name'))
            ->from($db->quoteName('#__sportsmanagement_season'))
            ->where($db->quoteName('id') . ' = :seasonNameId')
            ->bind(':seasonNameId', $seasonId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return (string) $db->loadResult();
    }

    /**
     * Read related database IDs for a list of projects.
     *
     * @param string $table table value.
     * @param string $field field value.
     * @param array $projectIds projectIds value.
     * @return array
     */
    private function loadIdsForProjects(string $table, string $field, array $projectIds): array
    {
        return $this->loadIds($table, $field, $projectIds);
    }

    /**
     * Read related IDs from an existing database table.
     *
     * @param string $table table value.
     * @param string $field field value.
     * @param array $ids ids value.
     * @return array
     */
    private function loadIds(string $table, string $field, array $ids): array
    {
        if (!$ids) {
            return [];
        }

        $db = $this->getDatabase();
        $physical = $db->replacePrefix($table);

        if (!in_array($physical, $db->getTableList(), true)) {
            return [];
        }

        $ids = $this->normaliseIds($ids);
        if (!$ids) {
            return [];
        }

        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName($table))
            ->whereIn($db->quoteName($field), $ids, ParameterType::INTEGER);
        $db->setQuery($query);

        return $this->normaliseIds($db->loadColumn() ?: []);
    }

    /**
     * Delete matching IDs only when the table exists.
     *
     * @param string $table table value.
     * @param string $field field value.
     * @param array $ids ids value.
     * @return void
     */
    private function deleteFromExistingTable(string $table, string $field, array $ids): void
    {
        if (!$ids) {
            return;
        }

        $db = $this->getDatabase();
        $physical = $db->replacePrefix($table);

        if (!in_array($physical, $db->getTableList(), true)) {
            return;
        }

        $ids = $this->normaliseIds($ids);
        if (!$ids) {
            return;
        }

        $query = $db->createQuery()
            ->delete($db->quoteName($table))
            ->whereIn($db->quoteName($field), $ids, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();
        self::$db_num_rows += (int) $db->getAffectedRows();
    }

    /**
     * Read stored project template parameter values.
     *
     * @param DatabaseInterface $db db value.
     * @param int $projectId projectId value.
     * @param string $template template value.
     * @return string
     */
    private static function loadTemplateParams(DatabaseInterface $db, int $projectId, string $template): string
    {
        $query = $db->createQuery()
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__sportsmanagement_template_config'))
            ->where($db->quoteName('project_id') . ' = :templateProjectId')
            ->where($db->quoteName('template') . ' = :templateName')
            ->bind(':templateProjectId', $projectId, ParameterType::INTEGER)
            ->bind(':templateName', $template, ParameterType::STRING);
        $db->setQuery($query, 0, 1);

        return trim((string) $db->loadResult());
    }

    /**
     * Resolve the Joomla administrator application.
     * @return AdministratorApplication
     */
    private static function backendApplication(): AdministratorApplication
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

    /**
     * Resolve the selected SportsManagement database service.
     *
     * @param int $whichDatabase whichDatabase value.
     * @return DatabaseInterface
     */
    private static function sportsDatabase(int $whichDatabase = 0): DatabaseInterface
    {
        return (new SportsManagementDatabaseResolver())->resolve(
            $whichDatabase,
            self::joomlaDatabase()
        );
    }

    /**
     * Resolve and validate the primary Joomla database service.
     * @return DatabaseInterface
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        $database = self::backendApplication()->getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement project database service is unavailable.', 500);
        }

        return $database;
    }

    /**
     * Convert bulk project selectors to distinct, positive integer IDs.
     *
     * Accepts integers and whitespace-trimmed decimal strings, including
     * historical zero-padded values. Rejects floats, nested arrays,
     * booleans, partial decimal strings and overflowing identifiers, so
     * malformed selections never write to a different project.
     *
     * @param mixed $ids Array of IDs or legacy delimited ID string.
     * @return array<int,int> Unique positive IDs in input order.
     */
    private function normaliseIds($ids): array
    {
        if (is_string($ids)) {
            $ids = preg_split('/[\s,;]+/', $ids, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        } elseif (is_int($ids)) {
            $ids = [$ids];
        }

        if (!is_array($ids)) {
            return [];
        }

        $result = [];

        foreach (array_slice($ids, 0, 5000) as $value) {
            if (!is_int($value) && !is_string($value)) {
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);

                if (strlen($value) > 64 || preg_match('/^[0-9]+$/D', $value) !== 1) {
                    continue;
                }

                $digits = ltrim($value, '0');
                $value = $digits === '' ? '0' : $digits;
            }

            $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($id !== false) {
                $result[$id] = $id;
            }
        }

        return array_values($result);
    }

    /**
     * Validate permissions for direct administrator project mutations.
     *
     * The controller still handles POST CSRF validation; this additional
     * guard requires a real authenticated Joomla identity with the requested
     * component permission before a public model method performs writes.
     *
     * @param string $permission Required Joomla component permission.
     * @return bool True only when an authenticated user is authorised.
     */
    private function canWriteProjects(string $permission): bool
    {
        $identity = $this->administratorApplication()->getIdentity();

        return is_object($identity)
            && (int) ($identity->id ?? 0) > 0
            && method_exists($identity, 'authorise')
            && $identity->authorise($permission, 'com_sportsmanagement');
    }
}
