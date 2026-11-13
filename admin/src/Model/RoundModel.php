<?php
/**
 * Native Joomla 5/6 administrator form model for project rounds.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Table\RoundTable;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

final class RoundModel extends SportsManagementAdminModel
{
    public static int $db_num_rows = 0;

    /**
     * Load the Joomla round edit form.
     *
     * @param mixed $data data value.
     * @param mixed $loadData loadData value.
     * @return mixed
     */
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

    /**
     * Resolve the native round database table.
     *
     * @param mixed $type type value.
     * @param mixed $prefix prefix value.
     * @param mixed $config config value.
     * @return mixed
     */
    public function getTable($type = 'Round', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'Round') === 0) {
            return new RoundTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Read a round's numeric round code.
     *
     * @param mixed $round_id round id value.
     * @param mixed $cfg_which_database cfg which database value.
     * @return mixed
     */
    public static function getRoundcode($round_id = 0, $cfg_which_database = 0)
    {
        $roundId = (int) $round_id;

        if ($roundId <= 0) {
            return '';
        }

        $db = self::getSportsManagementDatabase((int) $cfg_which_database);
        $query = $db->createQuery()
            ->select($db->quoteName('roundcode'))
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = :roundCodeId')
            ->bind(':roundCodeId', $roundId, ParameterType::INTEGER);

        try {
            $db->setQuery($query);

            return $db->loadResult() ?: '';
        } catch (\Throwable $e) {
            self::backendApplication()->enqueueMessage($e->getMessage(), 'error');

            return '';
        }
    }

    /**
     * Resolve a round identifier by round code and project.
     *
     * @param mixed $roundcode roundcode value.
     * @param mixed $project_id project id value.
     * @param mixed $cfg_which_database cfg which database value.
     * @return mixed
     */
    public static function getRoundId($roundcode, $project_id, $cfg_which_database = 0)
    {
        $roundCode = (int) $roundcode;
        $projectId = (int) $project_id;
        $db = self::getSportsManagementDatabase((int) $cfg_which_database);
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('alias'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('roundcode') . ' = :roundLookupCode')
            ->bind(':roundLookupCode', $roundCode, ParameterType::INTEGER)
            ->where($db->quoteName('project_id') . ' = :roundLookupProjectId')
            ->bind(':roundLookupProjectId', $projectId, ParameterType::INTEGER);

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

    /**
     * Load a round by its identifier.
     *
     * @param mixed $round_id round id value.
     * @param mixed $cfg_which_database cfg which database value.
     * @return mixed
     */
    public static function getRound($round_id, $cfg_which_database = 0)
    {
        $roundId = (int) $round_id;

        if ($roundId <= 0) {
            return false;
        }

        $db = self::getSportsManagementDatabase((int) $cfg_which_database);
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = :roundId')
            ->bind(':roundId', $roundId, ParameterType::INTEGER);

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
     *
     * Dates are validated and normalised for the nullable SQL DATE columns
     * introduced in 4.24.02; malformed entries cannot write zero dates.
     * Validates every selected row before updating MyISAM records.
     *
     * @param array<int,mixed> $pks Selected round identifiers.
     * @param array<string,mixed> $post Submitted list fields.
     * @return string|false Localised result or false when saving fails.
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
            // Validate all submitted rows before the first write: legacy
            // MyISAM tables cannot roll back earlier updates on bad input.
            $records = [];

            foreach ($pks as $roundId) {
                $name = $post['name' . $roundId] ?? '';
                $first = $post['round_date_first' . $roundId] ?? '';
                $last = $post['round_date_last' . $roundId] ?? '';
                $roundcode = self::normaliseShortRoundNumber($post['roundcode' . $roundId] ?? 0, 2147483647);
                $tournement = self::normaliseShortRoundNumber($post['tournementround' . $roundId] ?? 0, 1);

                if (!is_string($name) || trim($name) === ''
                    || !is_string($first) || !is_string($last)
                    || $roundcode === false || $tournement === false) {
                    throw new \InvalidArgumentException('Invalid round list-edit fields.', 400);
                }

                $firstDate = self::normaliseShortRoundDate($first);
                $lastDate = self::normaliseShortRoundDate($last);

                if ($firstDate === false || $lastDate === false) {
                    throw new \InvalidArgumentException('Invalid round date supplied in list editing.', 400);
                }

                $name = trim($name);
                $record = (object) [
                    'id' => $roundId,
                    'roundcode' => $roundcode,
                    'tournement' => $tournement,
                    'name' => $name,
                    'alias' => OutputFilter::stringURLSafe($name),
                    'modified' => $date->toSql(),
                    'modified_by' => (int) $user->id,
                ];

                // Retain the historical list-edit convention: an omitted end
                // date inherits the start, while a missing start clears both.
                $record->round_date_first = $firstDate;
                $record->round_date_last = $firstDate === null ? null : ($lastDate ?? $firstDate);
                $record->rdatefirst_timestamp = $record->round_date_first === null
                    ? 0 : SportsManagementDateHelper::getTimestamp($record->round_date_first);
                $record->rdatelast_timestamp = $record->round_date_last === null
                    ? 0 : SportsManagementDateHelper::getTimestamp($record->round_date_last);
                $records[] = $record;
            }

            foreach ($records as $record) {
                $db->updateObject('#__sportsmanagement_round', $record, 'id', true);
            }
        } catch (\Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        return Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_SAVE');
    }

    /**
     * Parse a bounded, nonnegative round list-edit integer without PHP casts.
     *
     * @param mixed $value Submitted round code or tournament checkbox.
     * @param int $maximum Highest supported value.
     * @return int|false Parsed value or false for malformed input.
     */
    private static function normaliseShortRoundNumber(mixed $value, int $maximum): int|false
    {
        if (!is_int($value) && (!is_string($value) || strlen($value) > 32
            || preg_match('/^[0-9]+$/D', $value) !== 1)) {
            return false;
        }

        $number = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => $maximum],
        ]);

        return $number === false ? false : $number;
    }

    /**
     * Validate a compact round-list date before writing a SQL DATE column.
     *
     * Accepts Joomla calendar display dates and canonical ISO dates.
     * Blank or legacy zero-date values become null; malformed dates are
     * rejected rather than silently passing through to strict SQL mode.
     *
     * @param string $date Date submitted by the rounds list editor.
     * @return string|false|null Canonical SQL date, null, or false if invalid.
     */
    private static function normaliseShortRoundDate(string $date): string|false|null
    {
        $normalised = SportsManagementDateHelper::toSqlDate($date);

        if ($normalised === null) {
            return null;
        }

        if (preg_match('/^(\\d{4})-(\\d{2})-(\\d{2})$/D', $normalised, $parts) !== 1
            || !checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) {
            return false;
        }

        return $normalised;
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

    /**
     * Count the configured rounds in a project.
     *
     * @param mixed $project_id project id value.
     * @return int
     */
    public function getMaxRound($project_id): int
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(' . $db->quoteName('roundcode') . ')')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = :maxRoundProjectId')
            ->bind(':maxRoundProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Delete rounds after removing dependent match rows.
     *
     * @param mixed $pks pks value.
     * @return mixed
     */
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
            $query = $db->createQuery()
                ->select($db->quoteName('m.id'))
                ->from($db->quoteName('#__sportsmanagement_match', 'm'))
                ->whereIn($db->quoteName('m.round_id'), $roundIds, ParameterType::INTEGER);
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
                $query = $db->createQuery()
                    ->delete($db->quoteName('#__sportsmanagement' . $tableSuffix))
                    ->whereIn($db->quoteName($field), $ids, ParameterType::INTEGER);
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

    /**
     * Normalise selected round IDs for internal list operations.
     *
     * @param array $ids ids value.
     * @return array
     */
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

    /**
     * Resolve the Joomla administrator application.
     * @return CMSApplicationInterface
     */
    private static function backendApplication(): CMSApplicationInterface
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

    /**
     * Resolve the configured SportsManagement database connection.
     *
     * @param int $databaseConfig databaseConfig value.
     * @return DatabaseInterface
     */
    private static function getSportsManagementDatabase(int $databaseConfig = 0): DatabaseInterface
    {
        return (new SportsManagementDatabaseResolver())->resolve($databaseConfig);
    }
}
