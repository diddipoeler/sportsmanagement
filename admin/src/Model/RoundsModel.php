<?php
/**
 * Native Joomla 5/6 administrator list model for project rounds.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

final class RoundsModel extends SportsManagementListModel
{
    public static int $_project_id = 0;

    /**
     * Initialise the Joomla administrator round list and selected project.
     * Registers supported round sort fields, calls the native list model
     * and restores the project ID from Joomla input or administrator state.
     * @param array $config Native list-model configuration.
     * @param MVCFactoryInterface|null $factory Joomla MVC factory instance.
     * @return void
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'r.name', 'name',
            'r.alias', 'alias',
            'r.roundcode', 'roundcode',
            'r.round_date_first', 'round_date_first',
            'r.round_date_last', 'round_date_last',
            'r.id', 'id',
            'r.ordering', 'ordering',
            'r.published', 'published', 'state',
            'r.tournement', 'tournement',
        ];

        parent::__construct($config, $factory);

        $app = $this->administratorApplication();
        $projectId = $app->getInput()->getInt('pid');

        if ($projectId <= 0) {
            $projectId = (int) $app->getUserState('com_sportsmanagement.pid', 0);
        }

        self::$_project_id = max(0, $projectId);
        $app->setUserState('com_sportsmanagement.pid', self::$_project_id);
    }

    /**
     * Return the first round available for a competition project.
     * Uses the selected SportsManagement database and ordered round
     * navigation records, returning false if none are available.
     * @param mixed $projectid Competition project identifier.
     * @param mixed $cfg_which_database Database selection for round records.
     * @return mixed First round navigation row or false.
     */
    public static function getFirstRound($projectid, $cfg_which_database = 0)
    {
        $rows = self::getNavigationRounds((int) $projectid, (int) $cfg_which_database);

        return $rows[0] ?? false;
    }

    /**
     * Return the final round in the project's navigation sequence.
     * Queries the ordered project rounds and selects the final row
     * without relying on a fixed number of rounds.
     * @param mixed $projectid Project whose last round is requested.
     * @param mixed $cfg_which_database Configured database selector.
     * @return mixed Final round navigation record or false.
     */
    public static function getLastRound($projectid, $cfg_which_database = 0)
    {
        $rows = self::getNavigationRounds((int) $projectid, (int) $cfg_which_database);

        return $rows ? $rows[array_key_last($rows)] : false;
    }

    /**
     * Find the preceding round for a selected competition round.
     * Locates the numeric or Joomla ID:alias round entry and moves
     * one position backward, clamping to the first record.
     * @param mixed $roundid Current round identifier or slug.
     * @param mixed $projectid Project whose rounds are navigated.
     * @param mixed $cfg_which_database Selected SportsManagement database.
     * @return mixed Previous round navigation record or false.
     */
    public static function getPreviousRound($roundid, $projectid, $cfg_which_database = 0)
    {
        $rows = self::getNavigationRounds((int) $projectid, (int) $cfg_which_database);
        $index = self::findRoundIndex($rows, $roundid);

        if ($index === null) {
            return $rows[0] ?? false;
        }

        return $rows[max(0, $index - 1)] ?? false;
    }

    /**
     * Find the following round in the current project.
     * Resolves the supplied numeric or Joomla slug identifier against
     * the sorted rounds and clamps navigation at the last round.
     * @param mixed $roundid Current round ID or Joomla ID:alias.
     * @param mixed $projectid Competition project identifier.
     * @param mixed $cfg_which_database Selected database configuration.
     * @return mixed Next round navigation record or false.
     */
    public static function getNextRound($roundid, $projectid, $cfg_which_database = 0)
    {
        $rows = self::getNavigationRounds((int) $projectid, (int) $cfg_which_database);
        $index = self::findRoundIndex($rows, $roundid);

        if ($index === null) {
            return $rows[0] ?? false;
        }

        return $rows[min(count($rows) - 1, $index + 1)] ?? false;
    }

    /**
     * Count all rounds assigned to a selected project.
     * Uses the project identifier as a bound integer parameter and
     * reports database errors through Joomla's administrator messages.
     * @param mixed $project_id Selected project ID.
     * @return int Number of project rounds or zero on failure.
     */
    public function getRoundsCount($project_id): int
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = :roundCountProjectId')
            ->bind(':roundCountProjectId', $projectId, ParameterType::INTEGER);

        try {
            $db->setQuery($query);

            return (int) $db->loadResult();
        } catch (\Throwable $e) {
            $this->administratorApplication()->enqueueMessage($e->getMessage(), 'error');

            return 0;
        }
    }

    /**
     * Return ordered round identifiers for a selected project.
     * Queries the project's rounds using a bound ID and sorts by
     * roundcode and identifier for consistent scheduling.
     * @param mixed $project_id Project owning the rounds.
     * @return array<int> Sorted round IDs.
     */
    public function getRoundsIds($project_id): array
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = :roundIdsProjectId')
            ->bind(':roundIdsProjectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('roundcode') . ' ASC')
            ->order($db->quoteName('id') . ' ASC');

        try {
            $db->setQuery($query);

            return array_map('intval', $db->loadColumn() ?: []);
        } catch (\Throwable $e) {
            $this->administratorApplication()->enqueueMessage($e->getMessage(), 'error');

            return [];
        }
    }

    /**
     * Find the project round relevant to the current UTC date.
     * Uses Joomla Date, the project round start/end fields and
     * a bound project ID to locate the next scheduled round.
     * @param mixed $projectid Project to inspect for upcoming rounds.
     * @return array Matching round information or an empty array.
     */
    public function getNextRoundByToday($projectid): array
    {
        $projectId = (int) $projectid;

        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $today = (new Date('now', new \DateTimeZone('UTC')))->format('Y-m-d');
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('roundcode'),
                $db->quoteName('round_date_first'),
                $db->quoteName('round_date_last'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = :todayProjectId')
            ->bind(':todayProjectId', $projectId, ParameterType::INTEGER)
            ->where($db->quoteName('round_date_first') . ' > :todayDate')
            ->bind(':todayDate', $today, ParameterType::STRING)
            ->order($db->quoteName('round_date_first') . ' ASC');

        try {
            $db->setQuery($query);

            return $db->loadAssocList() ?: [];
        } catch (\Throwable $e) {
            $this->administratorApplication()->enqueueMessage($e->getMessage(), 'error');

            return [];
        }
    }

    /**
     * Create missing rounds and matches using the legacy RRobin scheduler.
     */
    public function populate($project_id, $scheduling, $time, $interval, $start, $roundname, $teamsorder = null)
    {
        $projectId = (int) $project_id;
        $scheduling = (int) $scheduling;
        $interval = (int) $interval;
        $time = (string) $time;
        $start = (string) $start;
        $roundname = (string) $roundname;
        $teamsorder = is_array($teamsorder) ? array_values(array_map('intval', $teamsorder)) : [];

        if ($projectId <= 0) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUND_FAILED'));

            return false;
        }

        if (!strtotime($start)) {
            $start = (new Date('now', new \DateTimeZone('UTC')))->format('Y-m-d');
        }

        if (!preg_match('/^[0-9]+:[0-9]+$/', $time)) {
            $time = '20:00';
        }

        $teams = $this->getProjectTeamsOptions($projectId);

        if ($teamsorder) {
            $byId = [];

            foreach ($teams as $team) {
                $byId[(int) $team->value] = $team;
            }

            $ordered = [];

            foreach ($teamsorder as $projectTeamId) {
                if (isset($byId[$projectTeamId])) {
                    $ordered[] = $byId[$projectTeamId];
                }
            }

            if ($ordered) {
                $teams = $ordered;
            }
        }

        if (!$teams) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_ERROR_NO_TEAM'));

            return false;
        }

        if ($scheduling < 0 || $scheduling >= 2) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_ERROR_UNDEFINED_SCHEDULING'));

            return false;
        }

        require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/RRobin.class.php';
        $helper = new \RRobin();
        $helper->create($teams);
        $schedule = $helper->getSchedule($scheduling + 1);
        $rounds = self::getRoundsOptions($projectId) ?: [];
        $db = $this->getDatabase();
        $date = new Date('now', new \DateTimeZone('UTC'));
        $user = $this->administratorApplication()->getIdentity();
        $currentDate = null;
        $currentCode = 0;
        $transactionStarted = false;

        try {
            $db->transactionStart();
            $transactionStarted = true;

            foreach ($schedule as $index => $games) {
                if (isset($rounds[$index])) {
                    $roundId = (int) $rounds[$index]->id;
                    $currentDate = (string) $rounds[$index]->round_date_first;
                    $currentCode = (int) $rounds[$index]->roundcode;
                } else {
                    $round = (object) [
                        'project_id' => $projectId,
                        'round_date_first' => strtotime((string) $currentDate)
                            ? date('Y-m-d', strtotime((string) $currentDate) + $interval * 86400)
                            : $start,
                        'roundcode' => $currentCode > 0 ? $currentCode + 1 : 1,
                        'modified' => $date->toSql(),
                        'modified_by' => (int) $user->id,
                    ];
                    $round->round_date_last = $round->round_date_first;
                    $round->name = sprintf($roundname, $round->roundcode);

                    $db->insertObject('#__sportsmanagement_round', $round, 'id');
                    $roundId = (int) ($round->id ?? 0);
                    $currentDate = $round->round_date_first;
                    $currentCode = (int) $round->roundcode;
                }

                foreach ($games as $gamePair) {
                    if (!isset($gamePair[0], $gamePair[1])) {
                        continue;
                    }

                    $game = (object) [
                        'round_id' => $roundId,
                        'division_id' => 0,
                        'projectteam1_id' => (int) $gamePair[0]->value,
                        'projectteam2_id' => (int) $gamePair[1]->value,
                        'published' => 1,
                        'match_date' => $currentDate . ' ' . $time,
                        'modified' => $date->toSql(),
                        'modified_by' => (int) $user->id,
                    ];
                    $db->insertObject('#__sportsmanagement_match', $game);
                }
            }

            $db->transactionCommit();
        } catch (\Throwable $e) {
            if ($transactionStarted) {
                try {
                    $db->transactionRollback();
                } catch (\Throwable) {
                    // Preserve the original scheduling error.
                }
            }

            $this->administratorApplication()->enqueueMessage($e->getMessage(), 'error');
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUND_FAILED'));

            return false;
        }

        return true;
    }

    /**
     * Build selectable round options for the given project and database.
     * Applies the project ID as a bound SQL parameter and accepts only
     * ASC/DESC ordering to produce legacy round selection records.
     * @param mixed $project_id Project identifier.
     * @param mixed $ordering Requested sort direction.
     * @param mixed $cfg_which_database SportsManagement database selector.
     * @return mixed Project round dropdown options.
     */
    public static function getRoundsOptions($project_id, $ordering = 'ASC', $cfg_which_database = 0)
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return [];
        }

        $direction = strtoupper((string) $ordering) === 'DESC' ? 'DESC' : 'ASC';
        $db = self::resolveSportsManagementDatabase((int) $cfg_which_database);
        $query = $db->createQuery()
            ->select([
                $db->quoteName('alias'),
                $db->quoteName('name', 'text'),
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('round_date_first'),
                $db->quoteName('round_date_last'),
                $db->quoteName('roundcode'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = :roundOptionsProjectId')
            ->bind(':roundOptionsProjectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('roundcode') . ' ' . $direction)
            ->order($db->quoteName('id') . ' ' . $direction);

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];

            foreach ($rows as $row) {
                $row->value = (int) ($row->id ?? 0) . ':' . (string) ($row->alias ?? '');
            }

            return $rows;
        } catch (\Throwable $e) {
            self::resolveAdministratorApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    /**
     * Read the project used by the rounds administrator views.
     */
    public function getProject($project_id)
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
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
            ->where($db->quoteName('p.id') . ' = :roundProjectId')
            ->bind(':roundProjectId', $projectId, ParameterType::INTEGER);

        try {
            $db->setQuery($query, 0, 1);

            return $db->loadObject() ?: false;
        } catch (\Throwable $e) {
            $this->administratorApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    /**
     * Return the project-team options used by the scheduling form.
     */
    public function getProjectTeamsOptions($project_id, $division_id = 0): array
    {
        $projectId = (int) $project_id;
        $divisionId = (int) $division_id;

        if ($projectId <= 0) {
            return [];
        }

        $app = $this->administratorApplication();
        $projectType = (int) $app->getUserState('com_sportsmanagement.project_art_id', 0);
        $db = $this->getDatabase();
        $query = $db->createQuery();

        if ($projectType === 3) {
            $query->select([
                    $db->quoteName('pt.id', 'value'),
                    "CONCAT(" . $db->quoteName('t.lastname') . ", ' - ', " . $db->quoteName('t.firstname') . ') AS ' . $db->quoteName('text'),
                    $db->quoteName('t.notes'),
                ])
                ->from($db->quoteName('#__sportsmanagement_person', 't'))
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_season_person_id', 'st')
                    . ' ON ' . $db->quoteName('st.person_id') . ' = ' . $db->quoteName('t.id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_project_team', 'pt')
                    . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
                );
        } else {
            $query->select([
                    $db->quoteName('pt.id', 'value'),
                    $db->quoteName('t.name', 'text'),
                ])
                ->from($db->quoteName('#__sportsmanagement_team', 't'))
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                    . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_project_team', 'pt')
                    . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
                );
        }

        $query->where($db->quoteName('pt.project_id') . ' = :teamOptionsProjectId')
            ->bind(':teamOptionsProjectId', $projectId, ParameterType::INTEGER);

        if ($divisionId > 0) {
            $query->where($db->quoteName('pt.division_id') . ' = :teamOptionsDivisionId')
                ->bind(':teamOptionsDivisionId', $divisionId, ParameterType::INTEGER);
        }

        $query->order($db->quoteName('text') . ' ASC');

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'error');

            return [];
        }
    }

    /**
     * Restore the administrator round-list filters and project context.
     * Reads project selection from Joomla input/user state, sets
     * search, publication and tournament state, and prepares sorting.
     * @param string $ordering Default roundcode ordering.
     * @param string $direction Default sort direction.
     * @return void
     */
    protected function populateState($ordering = 'r.roundcode', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = $this->administratorApplication();
        $input = $app->getInput();
        $projectId = $input->getInt('pid');

        if ($projectId <= 0) {
            $projectId = (int) $app->getUserState('com_sportsmanagement.pid', self::$_project_id);
        }

        self::$_project_id = max(0, $projectId);
        $app->setUserState('com_sportsmanagement.pid', self::$_project_id);
        $this->setState(
            'filter.search',
            $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string')
        );
        $this->setState(
            'filter.state',
            $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string')
        );
        $this->setState(
            'filter.tournement',
            $app->getUserStateFromRequest(
                $this->context . '.filter.tournement',
                'filter_tournement',
                '',
                'string'
            )
        );
    }

    /**
     * Build the filtered administrator round list for a project.
     * Loads project and season context, joins scheduled round metadata
     * and applies safe sort and publication filtering to the list query.
     * @return \Joomla\Database\DatabaseQuery Native rounds-list SQL query.
     */
    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $projectId = (int) self::$_project_id;

        if ($projectId > 0) {
            $seasonQuery = $db->createQuery()
                ->select($db->quoteName('season_id'))
                ->from($db->quoteName('#__sportsmanagement_project'))
                ->where($db->quoteName('id') . ' = :seasonProjectId')
                ->bind(':seasonProjectId', $projectId, ParameterType::INTEGER);
            $db->setQuery($seasonQuery);
            $this->administratorApplication()->setUserState(
                'com_sportsmanagement.season_id',
                (int) $db->loadResult()
            );
        }

        $unpublished = $db->createQuery()
            ->select('COUNT(' . $db->quoteName('published') . ')')
            ->from($db->quoteName('#__sportsmanagement_match'))
            ->where($db->quoteName('round_id') . ' = ' . $db->quoteName('r.id'))
            ->where($db->quoteName('published') . ' = 0');
        $withoutResults = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_match'))
            ->where($db->quoteName('round_id') . ' = ' . $db->quoteName('r.id'))
            ->where($db->quoteName('cancel') . ' = 0')
            ->where(
                '(' . $db->quoteName('team1_result') . ' IS NULL OR '
                . $db->quoteName('team2_result') . ' IS NULL)'
            );
        $matches = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_match'))
            ->where($db->quoteName('round_id') . ' = ' . $db->quoteName('r.id'));
        $query = $db->createQuery()
            ->select([
                $db->quoteName('r') . '.*',
                '(' . $unpublished . ') AS ' . $db->quoteName('countUnPublished'),
                '(' . $withoutResults . ') AS ' . $db->quoteName('countNoResults'),
                '(' . $matches . ') AS ' . $db->quoteName('countMatches'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = :listProjectId')
            ->bind(':listProjectId', $projectId, ParameterType::INTEGER);

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = '%' . $db->escape($search, true) . '%';
            $query->where('LOWER(' . $db->quoteName('r.name') . ') LIKE LOWER(:roundSearch)')
                ->bind(':roundSearch', $token, ParameterType::STRING);
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $publishedState = (int) $state;
            $query->where($db->quoteName('r.published') . ' = :roundPublished')
                ->bind(':roundPublished', $publishedState, ParameterType::INTEGER);
        }

        $tournement = $this->getState('filter.tournement');

        if ($tournement !== '' && is_numeric($tournement)) {
            $tournementState = (int) $tournement;
            $query->where($db->quoteName('r.tournement') . ' = :roundTournement')
                ->bind(':roundTournement', $tournementState, ParameterType::INTEGER);
        }

        $orderMap = [
            'r.name' => $db->quoteName('r.name'),
            'name' => $db->quoteName('r.name'),
            'r.alias' => $db->quoteName('r.alias'),
            'alias' => $db->quoteName('r.alias'),
            'r.roundcode' => $db->quoteName('r.roundcode'),
            'roundcode' => $db->quoteName('r.roundcode'),
            'r.round_date_first' => $db->quoteName('r.round_date_first'),
            'round_date_first' => $db->quoteName('r.round_date_first'),
            'r.round_date_last' => $db->quoteName('r.round_date_last'),
            'round_date_last' => $db->quoteName('r.round_date_last'),
            'r.id' => $db->quoteName('r.id'),
            'id' => $db->quoteName('r.id'),
            'r.ordering' => $db->quoteName('r.ordering'),
            'ordering' => $db->quoteName('r.ordering'),
            'r.published' => $db->quoteName('r.published'),
            'published' => $db->quoteName('r.published'),
            'state' => $db->quoteName('r.published'),
            'r.tournement' => $db->quoteName('r.tournement'),
            'tournement' => $db->quoteName('r.tournement'),
        ];
        $ordering = (string) $this->getState('list.ordering', 'r.roundcode');
        $listDirection = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($orderMap[$ordering] ?? $orderMap['r.roundcode']) . ' ' . $listDirection);

        return $query;
    }

    /**
     * Include project and list filters in the Joomla list-cache key.
     * Prevents stale round lists from being reused across projects,
     * search results, tournaments or publication states.
     * @param string $id Base Joomla store identifier.
     * @return string Project and filter specific cache key.
     */
    protected function getStoreId($id = '')
    {
        $id .= ':' . self::$_project_id;
        $id .= ':' . (string) $this->getState('filter.search');
        $id .= ':' . (string) $this->getState('filter.state');
        $id .= ':' . (string) $this->getState('filter.tournement');

        return parent::getStoreId($id);
    }

    /**
     * Load ordered navigation records for a competition project.
     * Queries round IDs, aliases and round codes from the chosen
     * SportsManagement connection and constructs Joomla ID:alias
     * navigation slugs; database failures are reported to administrators.
     * @param int $projectId Competition project identifier.
     * @param int $databaseConfig Database connection selector.
     * @return array<int,array<string,mixed>> Ordered round navigation rows.
     */
    private static function getNavigationRounds(int $projectId, int $databaseConfig): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = self::resolveSportsManagementDatabase($databaseConfig);
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'round_id'),
                $db->quoteName('alias'),
                $db->quoteName('roundcode'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = :navigationProjectId')
            ->bind(':navigationProjectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('roundcode') . ' ASC')
            ->order($db->quoteName('id') . ' ASC');

        try {
            $db->setQuery($query);
            $rows = $db->loadAssocList() ?: [];

            foreach ($rows as &$row) {
                $row['id'] = (int) ($row['round_id'] ?? 0) . ':' . (string) ($row['alias'] ?? '');
            }
            unset($row);

            return $rows;
        } catch (\Throwable $e) {
            self::resolveAdministratorApplication()->enqueueMessage($e->getMessage(), 'error');

            return [];
        }
    }

    /**
     * Locate a round within an ordered navigation collection.
     * Accepts only positive numeric IDs or Joomla ID:alias slugs,
     * rejecting partially numeric and oversized values before
     * comparing the result to project round records.
     * @param array<int,array<string,mixed>> $rows Ordered round navigation rows.
     * @param mixed $roundId Raw round identifier or Joomla slug.
     * @return int|null Matching list index or null.
     */
    private static function findRoundIndex(array $rows, $roundId): ?int
    {
        if (!is_int($roundId) && !is_string($roundId)) {
            return null;
        }

        $needle = trim((string) $roundId);

        if (!preg_match('/^([0-9]+)(?::[^:\\s,;]+)?$/D', $needle, $matches)) {
            return null;
        }

        $digits = ltrim($matches[1], '0');
        $numericNeedle = $digits === '' ? false : filter_var(
            $digits,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($numericNeedle === false) {
            return null;
        }

        foreach ($rows as $index => $row) {
            if ((string) ($row['id'] ?? '') === $needle || (int) ($row['round_id'] ?? 0) === $numericNeedle) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Resolve the database configured for administrator round navigation.
     * Delegates database selection to the component's administrator
     * database resolver, preserving the legacy external DB choice.
     * @param int $databaseConfig Primary or external database selection.
     * @return DatabaseInterface Effective SportsManagement database.
     */
    private static function resolveSportsManagementDatabase(int $databaseConfig = 0): DatabaseInterface
    {
        return (new SportsManagementDatabaseResolver())->resolve($databaseConfig);
    }
    /**
     * Load and validate the Joomla administrator application service.
     * Retrieves AdministratorApplication through Joomla's container
     * and rejects a mismatched application client.
     * @return AdministratorApplication Current administrator application.
     * @throws \RuntimeException If the service is not the administrator client.
     */
    private static function resolveAdministratorApplication(): AdministratorApplication
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement rounds model requires the Joomla administrator application.', 500);
        }

        return $app;
    }

}
