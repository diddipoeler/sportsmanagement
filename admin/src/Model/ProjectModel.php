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
use Diddipoeler\Component\SportsManagement\Administrator\Table\ProjectTable;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 administrator form model for projects. */
final class ProjectModel extends SportsManagementAdminModel
{
    public static int $db_num_rows = 0;

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

    public function getTable($type = 'Project', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'Project') === 0) {
            return new ProjectTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    protected function prepareSportsManagementData(array $data): array
    {
        if (isset($data['name'])) {
            $data['name'] = trim((string) $data['name']);
            $data['alias'] = OutputFilter::stringURLSafe($data['name']);
        }

        $data['modified_timestamp'] = Factory::getDate()->toUnix();

        return $data;
    }

    public function setleaguechampion()
    {
        $app = $this->administratorApplication();
        $ids = $this->normaliseIds($app->getInput()->post->get('cid', [], 'array'));

        if (!$ids) {
            return Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SAVE_NO_SELECT');
        }

        $post = $app->getInput()->post->getArray();
        $db = $this->getDatabase();
        $now = Factory::getDate();
        $userId = (int) $app->getIdentity()->id;

        try {
            foreach ($ids as $id) {
                $current = (int) ($post['use_leaguechampion' . $id] ?? 0);
                $db->updateObject(
                    '#__sportsmanagement_project',
                    (object) [
                        'id' => $id,
                        'use_leaguechampion' => $current ? 0 : 1,
                        'modified' => $now->toSql(),
                        'modified_timestamp' => $now->toUnix(),
                        'modified_by' => $userId,
                    ],
                    'id'
                );
            }

            return Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SAVE');
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return false;
        }
    }

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

    public static function getProjectsbyCurrentProjectLeagueSeason($season_id, $league_id): array
    {
        $seasonId = (int) $season_id;
        $leagueId = (int) $league_id;

        if ($seasonId <= 0 || $leagueId <= 0) {
            return [];
        }

        $db = self::sportsDatabase();
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
            self::backendApplication()->enqueueMessage($e->getMessage(), 'warning');

            return [];
        }
    }

    public static function getProject($project_id): ?object
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return null;
        }

        $db = self::sportsDatabase();
        $query = $db->getQuery(true)
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
            ->where($db->quoteName('p.id') . ' = ' . $projectId);

        try {
            $db->setQuery($query, 0, 1);

            return $db->loadObject() ?: null;
        } catch (\Throwable $e) {
            self::backendApplication()->enqueueMessage($e->getMessage(), 'warning');

            return null;
        }
    }

    public function getProjectTeam($projectteam_id): ?object
    {
        $projectTeamId = (int) $projectteam_id;

        if ($projectTeamId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
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
            ->where($db->quoteName('pt.id') . ' = ' . $projectTeamId);

        try {
            $db->setQuery($query, 0, 1);

            return $db->loadObject() ?: null;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return null;
        }
    }

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
            $query = $db->getQuery(true)
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
            $query = $db->getQuery(true)
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

        $query->where($db->quoteName('pt.project_id') . ' = ' . $projectId);

        if ((int) $iDivisionId > 0) {
            $query->where($db->quoteName('pt.division_id') . ' = ' . (int) $iDivisionId);
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

    public function delete(&$pks)
    {
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

    public function deleteProjectsData($pk = []): bool
    {
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

    public function copy()
    {
        $app = $this->administratorApplication();
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
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from($db->quoteName('#__sportsmanagement_project'))
                    ->where($db->quoteName('id') . ' = ' . $id);
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
                $source->modified = Factory::getDate()->toSql();
                $source->modified_timestamp = Factory::getDate()->toUnix();
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

    public function saveshort()
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $ids = $this->normaliseIds($input->post->get('cid', [], 'array'));

        if (!$ids) {
            return Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SAVE_NO_SELECT');
        }

        $post = $input->post->getArray();
        $db = $this->getDatabase();
        $now = Factory::getDate();
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
                    $db->updateObject(
                        '#__sportsmanagement_user_extra_fields_values',
                        (object) [
                            'id' => $extraValueId,
                            'fieldvalue' => trim((string) ($post['user_field' . $id] ?? '')),
                        ],
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

    private function ensureExtraFieldRows(int $projectId): void
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_user_extra_fields'))
            ->where($db->quoteName('template_backend') . ' = ' . $db->quote('project'));
        $db->setQuery($query);

        foreach (array_map('intval', $db->loadColumn() ?: []) as $fieldId) {
            $check = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__sportsmanagement_user_extra_fields_values'))
                ->where($db->quoteName('field_id') . ' = ' . $fieldId)
                ->where($db->quoteName('jl_id') . ' = ' . $projectId);
            $db->setQuery($check);

            if ((int) $db->loadResult() === 0) {
                $db->insertObject(
                    '#__sportsmanagement_user_extra_fields_values',
                    (object) ['field_id' => $fieldId, 'jl_id' => $projectId, 'fieldvalue' => '']
                );
            }
        }
    }

    private function copyExtraFieldValues(int $sourceProjectId, int $destinationProjectId): void
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_user_extra_fields_values'))
            ->where($db->quoteName('jl_id') . ' = ' . $sourceProjectId);
        $db->setQuery($query);

        foreach ($db->loadObjectList() ?: [] as $row) {
            unset($row->id);
            $row->jl_id = $destinationProjectId;
            $db->insertObject('#__sportsmanagement_user_extra_fields_values', $row);
        }
    }

    private function getSeasonName(int $seasonId): string
    {
        if ($seasonId <= 0) {
            return '';
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('name'))
            ->from($db->quoteName('#__sportsmanagement_season'))
            ->where($db->quoteName('id') . ' = ' . $seasonId);
        $db->setQuery($query, 0, 1);

        return (string) $db->loadResult();
    }

    private function loadIdsForProjects(string $table, string $field, array $projectIds): array
    {
        return $this->loadIds($table, $field, $projectIds);
    }

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

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName($table))
            ->where($db->quoteName($field) . ' IN (' . implode(',', $ids) . ')');
        $db->setQuery($query);

        return $this->normaliseIds($db->loadColumn() ?: []);
    }

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

        $query = $db->getQuery(true)
            ->delete($db->quoteName($table))
            ->where($db->quoteName($field) . ' IN (' . implode(',', $ids) . ')');
        $db->setQuery($query);
        $db->execute();
        self::$db_num_rows += (int) $db->getAffectedRows();
    }

    private static function loadTemplateParams(DatabaseInterface $db, int $projectId, string $template): string
    {
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__sportsmanagement_template_config'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->where($db->quoteName('template') . ' = ' . $db->quote($template));
        $db->setQuery($query, 0, 1);

        return trim((string) $db->loadResult());
    }

    private static function backendApplication(): AdministratorApplication
    {
        return Factory::getContainer()->get(AdministratorApplication::class);
    }

    private static function sportsDatabase(int $whichDatabase = 0): DatabaseInterface
    {
        return (new SportsManagementDatabaseResolver())->resolve($whichDatabase);
    }

    private function normaliseIds($ids): array
    {
        if (is_string($ids)) {
            $ids = preg_split('/[\s,;]+/', $ids, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        return array_values(array_unique(array_filter(
            array_map('intval', (array) $ids),
            static fn (int $id): bool => $id > 0
        )));
    }
}
