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
    public static $change_training_date = false;

    public function getForm($data = [], $loadData = true)
    {
        $form = parent::getForm($data, $loadData);

        if (!$form) {
            return false;
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');
        $mediaTool = trim((string) $params->get('cfg_which_media_tool', 'media')) ?: 'media';

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

    public static function getTeamLogo($team_id, $club_logo = 'small')
    {
        $teamId = (int) $team_id;
        $logoSize = strtolower((string) $club_logo);
        $logoSize = in_array($logoSize, ['small', 'middle', 'big'], true) ? $logoSize : 'small';
        $app = self::backendApplication();
        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
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

    public function DeleteTrainigData($id)
    {
        $trainingId = (int) $id;

        if ($trainingId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->delete($db->quoteName('#__sportsmanagement_team_trainingdata'))
            ->where($db->quoteName('id') . ' = :trainingId')
            ->bind(':trainingId', $trainingId, ParameterType::INTEGER);

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

    public function UpdateTrainigData($post)
    {
        $ids = array_values(array_filter(array_map('intval', (array) ($post['tdids'] ?? []))));

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
                    ->bind(':trainingUpdateId', $id, ParameterType::INTEGER);
                $db->setQuery($query);
                $current = $db->loadObject();

                if (!$current) {
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

    public function addNewTrainigData($team_id = 0)
    {
        $teamId = (int) $team_id;

        if ($teamId <= 0) {
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

    protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
    {
        $app = $this->administratorApplication();
        $post = $app->getInput()->post->getArray();

        $this->syncSeasons($data, $id);

        if (!empty($post['delete']) && is_array($post['delete'])) {
            $this->DeleteTrainigData((int) reset($post['delete']));
        }

        if (!empty($post['tdids']) && is_array($post['tdids'])) {
            $this->UpdateTrainigData($post);
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

    private function syncSeasons(array $data, int $teamId): void
    {
        if (!isset($data['season_ids']) || !is_array($data['season_ids']) || $teamId <= 0) {
            return;
        }

        $seasonIds = array_values(array_unique(array_filter(array_map('intval', $data['season_ids']))));
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

    private function rememberTrainingChange(): void
    {
        $this->administratorApplication()->setUserState(
            'com_sportsmanagement.change_training_date',
            (bool) self::$change_training_date
        );
    }

    private static function backendApplication(): CMSApplicationInterface
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

    private static function timeToSeconds(string $time): int
    {
        $parts = array_map('intval', explode(':', trim($time)));

        if (count($parts) === 2) {
            $parts[] = 0;
        }

        [$hours, $minutes, $seconds] = array_pad($parts, 3, 0);

        return max(0, $hours) * 3600 + max(0, $minutes) * 60 + max(0, $seconds);
    }
}