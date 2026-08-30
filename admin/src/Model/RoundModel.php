<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Table\RoundTable;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Native Joomla 5/6 administrator form model for project rounds.
 */
final class RoundModel extends SportsManagementAdminModel
{
    public static int $db_num_rows = 0;

    public function getForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/forms');

        return $this->loadForm(
            'com_sportsmanagement.round',
            'round',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

    public function getTable($type = 'Round', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'Round') === 0) {
            return new RoundTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    public static function getRoundcode($round_id = 0, $cfg_which_database = 0)
    {
        $roundId = (int) $round_id;

        if ($roundId <= 0) {
            return '';
        }

        $db = self::getSportsManagementDatabase((int) $cfg_which_database);
        $query = $db->getQuery(true)
            ->select($db->quoteName('roundcode'))
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = ' . $roundId);

        try {
            $db->setQuery($query);

            return $db->loadResult() ?: '';
        } catch (\Throwable $e) {
            self::backendApplication()->enqueueMessage($e->getMessage(), 'error');

            return '';
        }
    }

    public static function getRoundId($roundcode, $project_id, $cfg_which_database = 0)
    {
        $db = self::getSportsManagementDatabase((int) $cfg_which_database);
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('alias'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('roundcode') . ' = ' . (int) $roundcode)
            ->where($db->quoteName('project_id') . ' = ' . (int) $project_id);

        try {
            $db->setQuery($query, 0, 1);
            $round = $db->loadObject();

            if (!$round) {
                return false;
            }

            $slug = [(string) $round->id];

            if ($round->alias !== null) {
                $slug[] = (string) $round->alias;
            }

            return implode(':', $slug);
        } catch (\Throwable $e) {
            self::backendApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    public static function getRound($round_id, $cfg_which_database = 0)
    {
        $roundId = (int) $round_id;

        if ($roundId <= 0) {
            return false;
        }

        $db = self::getSportsManagementDatabase((int) $cfg_which_database);
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = ' . $roundId);

        try {
            $db->setQuery($query, 0, 1);

            return $db->loadObject() ?: false;
        } catch (\Throwable $e) {
            self::backendApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    /**
     * Update the compact round fields edited directly in the rounds list.
     */
    public function saveshort(array $pks = [], array $post = [])
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();

        if (!$pks) {
            $pks = $input->get('cid', [], 'array');
        }

        $pks = $this->normaliseIds($pks);

        if (!$pks) {
            return Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_SAVE_NO_SELECT');
        }

        if (!$post) {
            $post = $input->post->getArray();
        }

        $db = $this->getDatabase();
        $date = Factory::getDate();
        $user = $app->getIdentity();

        try {
            foreach ($pks as $roundId) {
                $name = (string) ($post['name' . $roundId] ?? '');
                $first = (string) ($post['round_date_first' . $roundId] ?? '');
                $last = (string) ($post['round_date_last' . $roundId] ?? '');
                $record = (object) [
                    'id' => $roundId,
                    'roundcode' => (int) ($post['roundcode' . $roundId] ?? 0),
                    'tournement' => (int) ($post['tournementround' . $roundId] ?? 0),
                    'name' => $name,
                    'alias' => OutputFilter::stringURLSafe($name),
                    'modified' => $date->toSql(),
                    'modified_by' => (int) $user->id,
                ];

                if ($first === '') {
                    $record->round_date_first = '0000-00-00';
                    $record->round_date_last = '0000-00-00';
                    $record->rdatefirst_timestamp = 0;
                    $record->rdatelast_timestamp = 0;
                } elseif ($last === '') {
                    $record->round_date_first = SportsManagementDateHelper::convertDate($first, 0);
                    $record->round_date_last = $record->round_date_first;
                    $record->rdatefirst_timestamp = SportsManagementDateHelper::getTimestamp($record->round_date_first);
                    $record->rdatelast_timestamp = $record->rdatefirst_timestamp;
                } else {
                    $record->round_date_first = SportsManagementDateHelper::convertDate($first, 0);
                    $record->round_date_last = SportsManagementDateHelper::convertDate($last, 0);
                    $record->rdatefirst_timestamp = SportsManagementDateHelper::getTimestamp($record->round_date_first);
                    $record->rdatelast_timestamp = SportsManagementDateHelper::getTimestamp($record->round_date_last);
                }

                $db->updateObject('#__sportsmanagement_round', $record, 'id', true);
            }
        } catch (\Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        return Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_SAVE');
    }

    /**
     * Add a batch of empty rounds to the selected project.
     */
    public function massadd(array $post = [])
    {
        $app = $this->administratorApplication();

        if (!$post) {
            $post = $app->getInput()->post->getArray();
        }

        $projectId = (int) $app->getUserState('com_sportsmanagement.pid', 0);
        $addRoundCount = (int) ($post['add_round_count'] ?? 0);
        $projectType = (string) ($post['project_type'] ?? '');

        if ($projectId <= 0 || $addRoundCount <= 0) {
            return '';
        }

        $divisionNames = [
            4 => [1 => 'Gruppenspiele', 2 => 'Halbfinale', 3 => '3.Platz', 4 => 'Finale'],
            5 => [1 => 'Gruppenspiele', 2 => 'Viertelfinale', 3 => 'Halbfinale', 4 => '3.Platz', 5 => 'Finale'],
        ];
        $nextRoundCode = $this->getMaxRound($projectId) + 1;
        $db = $this->getDatabase();
        $user = $app->getIdentity();
        $modified = Factory::getDate()->toSql();
        $message = '';

        try {
            for ($index = 1; $index <= $addRoundCount; $index++, $nextRoundCode++) {
                $name = $projectType === 'DIVISIONS_LEAGUE' && isset($divisionNames[$addRoundCount][$index])
                    ? $divisionNames[$addRoundCount][$index]
                    : Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_CTRL_ROUND_NAME', $nextRoundCode);
                $record = (object) [
                    'project_id' => $projectId,
                    'roundcode' => $nextRoundCode,
                    'name' => $name,
                    'alias' => OutputFilter::stringURLSafe($name),
                    'modified' => $modified,
                    'modified_by' => (int) $user->id,
                ];
                $db->insertObject('#__sportsmanagement_round', $record);
                $message = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_CTRL_ROUNDS_ADDED', $index);
            }
        } catch (\Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'error');

            return Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_CTRL_ERROR_ADD');
        }

        return $message;
    }

    public function getMaxRound($project_id): int
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(' . $db->quoteName('roundcode') . ')')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    public function delete(&$pks)
    {
        if (!$this->deleteRoundMatches((array) $pks)) {
            return false;
        }

        return parent::delete($pks);
    }

    /**
     * Delete dependent match rows before deleting their rounds.
     */
    public function deleteRoundMatches($pks = [])
    {
        $roundIds = $this->normaliseIds((array) $pks);

        if (!$roundIds) {
            return true;
        }

        $db = $this->getDatabase();
        $app = $this->administratorApplication();

        try {
            $query = $db->getQuery(true)
                ->select($db->quoteName('m.id'))
                ->from($db->quoteName('#__sportsmanagement_match', 'm'))
                ->where($db->quoteName('m.round_id') . ' IN (' . implode(',', $roundIds) . ')');
            $db->setQuery($query);
            $matchIds = $this->normaliseIds($db->loadColumn() ?: []);

            $deletePlan = [];

            if ($matchIds) {
                foreach ([
                    '_match_statistic',
                    '_match_commentary',
                    '_match_staff_statistic',
                    '_match_staff',
                    '_match_event',
                    '_match_referee',
                    '_match_player',
                ] as $tableSuffix) {
                    $deletePlan[] = [$tableSuffix, 'match_id', $matchIds];
                }
            }

            $deletePlan[] = ['_match', 'round_id', $roundIds];

            foreach ($deletePlan as [$tableSuffix, $field, $ids]) {
                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__sportsmanagement' . $tableSuffix))
                    ->where($db->quoteName($field) . ' IN (' . implode(',', $ids) . ')');
                $db->setQuery($query)->execute();
                self::$db_num_rows = (int) $db->getAffectedRows();

                if (self::$db_num_rows > 0) {
                    $app->enqueueMessage(
                        Text::sprintf(
                            'COM_SPORTSMANAGEMENT' . strtoupper($tableSuffix) . '_ITEMS_DELETED',
                            self::$db_num_rows
                        ),
                        'message'
                    );
                }
            }
        } catch (\Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        return true;
    }

    private function normaliseIds(array $ids): array
    {
        return array_values(
            array_unique(
                array_filter(
                    array_map('intval', $ids),
                    static fn (int $id): bool => $id > 0
                )
            )
        );
    }

    private static function backendApplication(): AdministratorApplication
    {
        return Factory::getContainer()->get(AdministratorApplication::class);
    }

    private static function getSportsManagementDatabase(int $databaseConfig = 0): DatabaseInterface
    {
        return (new SportsManagementDatabaseResolver())->resolve($databaseConfig);
    }
}
