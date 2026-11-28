<?php
/**
 * Joomla 5/6 administrator model for SportsManagement teams.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraFieldsSaveHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class TeamModel extends SportsManagementAdminModel
{
    /**
     * Resolve the native team table on this model's active database connection.
     * Other table types continue through Joomla's MVC table factory.
     * @param string $type Requested table type.
     * @param string $prefix Table class prefix.
     * @param array $config Optional factory configuration.
     * @return mixed Native table or parent factory result.
     */
    public function getTable($type = 'Team', $prefix = 'Administrator', $config = [])
    {
        if (is_string($type) && strcasecmp($type, 'Team') === 0
            && in_array(strtolower((string) $prefix), ['administrator', 'sportsmanagementtable'], true)
            && $config === []) {
            return new \Diddipoeler\Component\SportsManagement\Administrator\Table\TeamTable($this->getDatabase(), true);
        }

        return parent::getTable($type, $prefix, $config);
    }

    public static $change_training_date = false;

    /**
     * Load the team form with a validated Joomla image field type.
     *
     * Applies component settings for optional club merging and switches
     * picture to either the native Joomla Media control or the supported
     * SportsManagement imageselect control. Rejects stale or unexpected
     * configuration values by falling back to Media; the XML field retains
     * its validated targetfolder="teams" for the component image handler.
     * Loads optional input sizes from the team schema without failing the
     * whole editor on unavailable column metadata.
     *
     * @param array<string,mixed> $data Existing team values.
     * @param bool $loadData Whether to populate saved team details.
     * @return mixed Team form or false when Joomla cannot load it.
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = parent::getForm($data, $loadData);

        if (!$form) {
            return false;
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');
        $mediaTool = trim((string) $params->get('cfg_which_media_tool', 'media'));

        if (!in_array($mediaTool, ['media', 'imageselect'], true)) {
            $mediaTool = 'media';
        }

        if (!(bool) $params->get('show_team_community', 0)) {
            $form->setFieldAttribute('merge_clubs', 'type', 'hidden');
        }

        $form->setFieldAttribute('picture', 'type', $mediaTool);

        try {
            foreach ($this->getDatabase()->getTableColumns($this->getDatabase()->getPrefix() . 'sportsmanagement_team', true) as $fieldName => $type) {
                if (!$form->getField((string) $fieldName)) {
                    continue;
                }

                if (preg_match('/varchar\s*\(\s*(\d+)\s*\)/i', (string) $type, $match)) {
                    $form->setFieldAttribute((string) $fieldName, 'size', (string) (int) $match[1]);
                }
            }
        } catch (\Throwable) {
            // Dynamic input sizes are only a UI enhancement; form loading must still succeed.
        }

        return $form;
    }

    /**
     * Save selected team values in the administrator quick edit form.
     * @return bool
     */
    public function saveshort(): bool
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $ids = array_values(array_filter(array_map('intval', (array) $input->post->get('cid', [], 'array'))));
        $post = $input->post->getArray();
        $result = true;
        $modified = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

        if (!$ids) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_SAVE_NO_SELECT'));
            return false;
        }

        foreach ($ids as $id) {
            $table = $this->getTable();

            if (!$table->load($id)) {
                $result = false;
                continue;
            }

            $table->sports_type_id = (int) ($post['sportstype' . $id] ?? $table->sports_type_id);
            $table->agegroup_id = (int) ($post['agegroup' . $id] ?? $table->agegroup_id);
            $table->modified = $modified;
            $table->modified_by = (int) $app->getIdentity()->id;

            if (!$table->check() || !$table->store()) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Copy the selected team records and related values.
     * @return bool
     */
    public function copySelected(): bool
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $ids = array_values(array_filter(array_map('intval', (array) $input->post->get('cid', [], 'array'))));
        $result = true;
        $modified = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

        if (!$ids) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_SAVE_NO_SELECT'));
            return false;
        }

        foreach ($ids as $id) {
            $source = $this->getTable();

            if (!$source->load($id)) {
                $result = false;
                continue;
            }

            $data = get_object_vars($source);
            unset($data['_db'], $data['_tbl'], $data['_tbl_key'], $data['_trackAssets'], $data['_rules'], $data['_errors']);
            $data['id'] = 0;
            $data['name'] = trim((string) $source->name) . ' (' . Text::_('JGLOBAL_COPY') . ')';
            $data['alias'] = '';
            $data['checked_out'] = 0;
            $data['checked_out_time'] = $this->getDatabase()->getNullDate();
            $data['modified'] = $modified;
            $data['modified_by'] = (int) $app->getIdentity()->id;

            $copy = $this->getTable();

            if (!$copy->bind($data) || !$copy->check() || !$copy->store()) {
                $result = false;
            }
        }

        return $result;
    }

    /** Legacy compatibility wrapper used by older controllers. */
    public function copysave()
    {
        return $this->copySelected()
            ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_SAVE')
            : ($this->getError() ?: Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_SAVE_NO_SELECT'));
    }

    /**
     * Load logo data for a team and selected logo size.
     *
     * @param mixed $team_id team id value.
     * @param string $club_logo club logo value.
     * @return array|false
     */
    public static function getTeamLogo($team_id, $club_logo = 'small')
    {
        $teamId = (int) $team_id;
        $logoSize = strtolower((string) $club_logo);
        $logoSize = in_array($logoSize, ['small', 'middle', 'big'], true) ? $logoSize : 'small';
        $app = self::backendApplication();
        $db = self::joomlaDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('c.logo_' . $logoSize, 'logo_small'),
                $db->quoteName('c.country'),
                $db->quoteName('t.name'),
                $db->quoteName('t.id', 'team_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->where($db->quoteName('t.id') . ' = :teamLogoId')
            ->bind(':teamLogoId', $teamId, ParameterType::INTEGER);

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $app->enqueueMessage(__METHOD__ . ' ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Read a team by its ID or project-team membership.
     *
     * @param int $team_id team id value.
     * @param int $pro_team_id pro team id value.
     * @return object|false|null
     */
    public function getTeam($team_id = 0, $pro_team_id = 0)
    {
        $teamId = (int) $team_id;
        $projectTeamId = (int) $pro_team_id;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('t.*')
            ->from($db->quoteName('#__sportsmanagement_team', 't'));

        if ($teamId > 0) {
            $query->where($db->quoteName('t.id') . ' = :teamId')
                ->bind(':teamId', $teamId, ParameterType::INTEGER);
        } elseif ($projectTeamId > 0) {
            $query->select($db->quoteName('st.logo_big'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id'))
                ->where($db->quoteName('st.id') . ' = :projectTeamId')
                ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER);
        } else {
            return null;
        }

        try {
            $db->setQuery($query);
            return $db->loadObject();
        } catch (\Throwable $e) {
            $this->administratorApplication()->enqueueMessage(__METHOD__ . ' ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Delete one stored team training entry after validating its identifier.
     *
     * @param mixed $id Training record identifier.
     * @param int|null $teamId Persisted owning team; required for writes.
     * @return bool
     */
    public function DeleteTrainigData($id, ?int $teamId = null)
    {
        $ids = self::normaliseSeasonIds([$id]);
        $trainingId = $ids[0] ?? 0;

        if ($trainingId <= 0 || !$this->canEditTrainingTeam($teamId)) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->delete($db->quoteName('#__sportsmanagement_team_trainingdata'))
            ->where($db->quoteName('id') . ' = :trainingId')
            ->bind(':trainingId', $trainingId, ParameterType::INTEGER)
            ->where($db->quoteName('team_id') . ' = :trainingOwner')
            ->bind(':trainingOwner', $teamId, ParameterType::INTEGER);

        try {
            $db->setQuery($query)->execute();
            self::$change_training_date = true;
            $this->rememberTrainingChange();
            return true;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * Apply submitted changes to existing training entries.
     * Rejects malformed or oversized identifier lists before writing any rows.
     *
     * @param mixed $post Submitted training rows.
     * @param int|null $teamId Persisted owning team; required for writes.
     * @return bool
     */
    public function UpdateTrainigData($post, ?int $teamId = null)
    {
        if (!is_array($post) || !$this->canEditTrainingTeam($teamId)) {
            return false;
        }
        $ids = self::normaliseSeasonIds($post['tdids'] ?? []);
        if ($ids === null) {
            return false;
        }

        if (!$ids) {
            return true;
        }

        $db = $this->getDatabase();

        try {
            foreach ($ids as $id) {
                $query = $db->createQuery()
                    ->select([
                        $db->quoteName('time_start'),
                        $db->quoteName('time_end'),
                        $db->quoteName('place'),
                        $db->quoteName('notes'),
                        $db->quoteName('dayofweek'),
                    ])
                    ->from($db->quoteName('#__sportsmanagement_team_trainingdata'))
                    ->where($db->quoteName('id') . ' = :trainingUpdateId')
                    ->bind(':trainingUpdateId', $id, ParameterType::INTEGER)
                    ->where($db->quoteName('team_id') . ' = :trainingOwner')
                    ->bind(':trainingOwner', $teamId, ParameterType::INTEGER);
                $db->setQuery($query);
                $current = $db->loadObject();

                if (!is_object($current)) {
                    continue;
                }

                $object = new \stdClass();
                $object->id = $id;
                $object->time_start = self::timeToSeconds((string) (($post['time_start'][$id] ?? '00:00')));
                $object->time_end = self::timeToSeconds((string) (($post['time_end'][$id] ?? '00:00')));
                $object->place = (string) ($post['place'][$id] ?? '');
                $object->notes = (string) ($post['notes'][$id] ?? '');
                $object->dayofweek = (int) ($post['dayofweek'][$id] ?? 0);

                $db->updateObject('#__sportsmanagement_team_trainingdata', $object, 'id', true);

                if (
                    (int) $current->time_start !== $object->time_start
                    || (int) $current->time_end !== $object->time_end
                    || (string) $current->place !== $object->place
                    || (string) $current->notes !== $object->notes
                    || (int) $current->dayofweek !== $object->dayofweek
                ) {
                    self::$change_training_date = true;
                }
            }
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return false;
        }

        $this->rememberTrainingChange();
        return true;
    }

    /**
     * List team training entries for a team or project team.
     *
     * @param int $team_id team id value.
     * @param int $pro_team_id pro team id value.
     * @return array|false
     */
    public function getTrainigData($team_id = 0, $pro_team_id = 0)
    {
        $teamId = (int) $team_id;
        $projectTeamId = (int) $pro_team_id;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('tt.*')
            ->from($db->quoteName('#__sportsmanagement_team_trainingdata', 'tt'));

        if ($teamId > 0) {
            $query->where($db->quoteName('tt.team_id') . ' = :trainingTeamId')
                ->bind(':trainingTeamId', $teamId, ParameterType::INTEGER);
        } elseif ($projectTeamId > 0) {
            $query->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tt.team_id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
                ->where($db->quoteName('pt.id') . ' = :trainingProjectTeamId')
                ->bind(':trainingProjectTeamId', $projectTeamId, ParameterType::INTEGER);
        }

        $query->order($db->quoteName('tt.dayofweek') . ' ASC');

        try {
            $db->setQuery($query);
            $result = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->administratorApplication()->enqueueMessage(__METHOD__ . ' ' . $e->getMessage(), 'error');
            return false;
        }

        if (!$result) {
            $this->administratorApplication()->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_TITLE_NO_TRAINING'), 'notice');
        }

        return $result;
    }

    /**
     * Create a new training entry for a validated positive team identifier.
     *
     * @param int $team_id team id value.
     * @return bool
     */
    public function addNewTrainigData($team_id = 0)
    {
        $ids = self::normaliseSeasonIds([$team_id]);
        $teamId = $ids[0] ?? 0;

        if (!$this->canEditTrainingTeam($teamId)) {
            return false;
        }

        $db = $this->getDatabase();

        try {
            $db->insertObject(
                '#__sportsmanagement_team_trainingdata',
                (object) ['team_id' => $teamId, 'notes' => '-']
            );
            self::$change_training_date = true;
            $this->rememberTrainingChange();
            $this->administratorApplication()->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_TITLE_INSERT_TRAINING'), 'notice');
            return true;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * Normalise submitted team fields before saving.
     *
     * @param array $data data value.
     * @return array
     */
    protected function prepareSportsManagementData(array $data): array
    {
        $app = $this->administratorApplication();
        $post = $app->getInput()->post->getArray();

        if (isset($post['extended']) && is_array($post['extended'])) {
            $registry = new Registry();
            $registry->loadArray($post['extended']);
            $data['extended'] = (string) $registry;
        }

        if (isset($post['copy_jform']['picture'])) {
            $data['picture'] = (string) $post['copy_jform']['picture'];
        }

        if (!empty($data['picture'])) {
            $data['picture'] = MediaHelper::getCleanMediaFieldValue((string) $data['picture']);
        }

        return parent::prepareSportsManagementData($data);
    }

    /**
     * Reject malformed season lists before Joomla saves the team record.
     *
     * A corrupted multiselect value must not cause an unrelated season link
     * to be created or accidentally remove all existing assignments during
     * the post-save cleanup. A deliberately empty selection still represents
     * a request to clear the team's season assignments.
     *
     * @param mixed $data Submitted Joomla team form values.
     * @return bool Whether the parent Joomla model saved the team.
     */
    public function save($data)
    {
        if (is_array($data) && isset($data['season_ids'])) {
            $seasonIds = self::normaliseSeasonIds($data['season_ids']);

            if ($seasonIds === null) {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));

                return false;
            }

            $data['season_ids'] = $seasonIds;
        }

        return parent::save($data);
    }

    /**
     * Validate selected team-season IDs without coercing partial numbers.
     *
     * Accepts positive decimal IDs, including zero-padded legacy checkbox
     * values, and de-duplicates the selection. Empty or zero placeholders
     * are ignored. Rejects arrays nested inside a choice, booleans, negative
     * values, overflow and oversized lists before any team data is written.
     *
     * @param mixed $values Raw jform[season_ids] checkbox selection.
     * @return array<int,int>|null Valid unique season IDs, or null if malformed.
     */
    private static function normaliseSeasonIds(mixed $values): ?array
    {
        if (!is_array($values) || count($values) > 500) {
            return null;
        }

        $ids = [];

        foreach ($values as $raw) {
            if ($raw === null || $raw === '' || $raw === 0 || $raw === '0') {
                continue;
            }

            if (is_int($raw)) {
                if ($raw <= 0) {
                    return null;
                }

                $id = $raw;
            } elseif (is_string($raw) && strlen($raw) <= 64
                && preg_match('/^[0-9]+$/D', $raw) === 1) {
                $digits = ltrim($raw, '0');

                if ($digits === '') {
                    continue;
                }

                $id = filter_var($digits, FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1],
                ]);

                if ($id === false) {
                    return null;
                }
            } else {
                return null;
            }

            $ids[$id] = $id;
        }

        return array_values($ids);
    }

    /**
     * Synchronise seasons and training data after a team save.
     *
     * @param array $data data value.
     * @param int $id id value.
     * @param bool $isNew isNew value.
     * @return void
     */
    protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
    {
        $app = $this->administratorApplication();
        $post = $app->getInput()->post->getArray();

        $this->syncSeasons($data, $id);

        if (!empty($post['delete']) && is_array($post['delete'])) {
            $this->DeleteTrainigData(reset($post['delete']), $id);
        }

        if (!empty($post['tdids']) && is_array($post['tdids'])) {
            $this->UpdateTrainigData($post, $id);
        }

        if (!empty($post['add_trainingData'])) {
            $this->addNewTrainigData($id);
        }

        try {
            (new ExtraFieldsSaveHelper())->save($post, $id, $this->getDatabase());
        } catch (\Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'warning');
        }

        $app->setUserState('com_sportsmanagement.team_id', $id);
    }

    /**
     * Synchronise validated season assignments for a saved team.
     *
     * Normalises the checkbox values before issuing SQL. Invalid selections
     * never enter the insert/update loop or the relationship cleanup query.
     *
     * @param array $data Saved team data including season_ids.
     * @param int $teamId Persisted positive team identifier.
     * @return void
     * @throws \InvalidArgumentException When the season list is malformed.
     */
    private function syncSeasons(array $data, int $teamId): void
    {
        if (!isset($data['season_ids']) || !is_array($data['season_ids']) || $teamId <= 0) {
            return;
        }

        $seasonIds = self::normaliseSeasonIds($data['season_ids']);

        if ($seasonIds === null) {
            // This also protects direct callers of the post-save hook.
            // Never delete season links because a request was malformed.
            throw new \InvalidArgumentException('Invalid team season selection.', 400);
        }

        $db = $this->getDatabase();
        $modified = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $modifiedBy = (int) $this->administratorApplication()->getIdentity()->id;

        foreach ($seasonIds as $seasonId) {
            $query = $db->createQuery()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__sportsmanagement_season_team_id'))
                ->where($db->quoteName('team_id') . ' = :seasonTeamId')
                ->bind(':seasonTeamId', $teamId, ParameterType::INTEGER)
                ->where($db->quoteName('season_id') . ' = :seasonId')
                ->bind(':seasonId', $seasonId, ParameterType::INTEGER);
            $db->setQuery($query);
            $linkId = (int) $db->loadResult();

            if ($linkId <= 0) {
                $db->insertObject(
                    '#__sportsmanagement_season_team_id',
                    (object) [
                        'team_id' => $teamId,
                        'season_id' => $seasonId,
                        'modified' => $modified,
                        'modified_by' => $modifiedBy,
                    ]
                );
                continue;
            }

            $link = (object) [
                'id' => $linkId,
                'modified' => $modified,
                'modified_by' => $modifiedBy,
            ];

            if (isset($data['teamvalue'][$seasonId])) {
                $link->teamname = (string) $data['teamvalue'][$seasonId];
            }

            if (isset($data['season_teamname'][$seasonId])) {
                $link->season_teamname = (string) $data['season_teamname'][$seasonId];
            }

            $db->updateObject('#__sportsmanagement_season_team_id', $link, 'id', true);
        }

        $query = $db->createQuery()
            ->delete($db->quoteName('#__sportsmanagement_season_team_id'))
            ->where($db->quoteName('team_id') . ' = :deleteSeasonTeamId')
            ->bind(':deleteSeasonTeamId', $teamId, ParameterType::INTEGER);

        if ($seasonIds) {
            $query->whereNotIn($db->quoteName('season_id'), $seasonIds, ParameterType::INTEGER);
        }

        $db->setQuery($query)->execute();
    }

    /**
     * Require an authenticated administrator with edit access to the team.
     * The owner must be supplied by the saved team context, not a training ID.
     * @param int|null $teamId Persisted team identifier.
     * @return bool Whether training records may be changed.
     */
    private function canEditTrainingTeam(?int $teamId): bool
    {
        if ($teamId === null || $teamId <= 0) {
            return false;
        }

        $identity = $this->administratorApplication()->getIdentity();

        return $identity !== null && (int) $identity->id > 0
            && $identity->authorise('core.edit', 'com_sportsmanagement');
    }

    /**
     * Preserve the training-change flag in Joomla user state.
     * @return void
     */
    private function rememberTrainingChange(): void
    {
        $this->administratorApplication()->setUserState(
            'com_sportsmanagement.change_training_date',
            (bool) self::$change_training_date
        );
    }

    /**
     * Resolve Joomla's primary database connection.
     * @return DatabaseInterface
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement team database service is unavailable.', 500);
        }

        return $database;
    }

    /**
     * Resolve the active Joomla administrator application.
     * @return CMSApplicationInterface
     */
    private static function backendApplication(): CMSApplicationInterface
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

    /**
     * Convert a valid 24-hour clock value to seconds since midnight.
     * Supports H:MM, HH:MM and optional seconds; invalid values return zero.
     *
     * @param string $time time value.
     * @return int
     */
    private static function timeToSeconds(string $time): int
    {
        if (preg_match('/^([01]?[0-9]|2[0-3]):([0-5][0-9])(?::([0-5][0-9]))?$/D', trim($time), $parts) !== 1) {
            return 0;
        }

        return (int) $parts[1] * 3600 + (int) $parts[2] * 60 + (int) ($parts[3] ?? 0);
    }
}