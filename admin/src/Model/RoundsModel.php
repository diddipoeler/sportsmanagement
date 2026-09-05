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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;

final class RoundsModel extends SportsManagementListModel
{
    public static int $_project_id = 0;

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

        $app = Factory::getApplication();
        $projectId = $app->getInput()->getInt('pid');

        if ($projectId <= 0) {
            $projectId = (int) $app->getUserState('com_sportsmanagement.pid', 0);
        }

        self::$_project_id = max(0, $projectId);
        $app->setUserState('com_sportsmanagement.pid', self::$_project_id);
    }

    public static function getFirstRound($projectid, $cfg_which_database = 0)
    {
        $rows = self::getNavigationRounds((int) $projectid, (int) $cfg_which_database);

        return $rows[0] ?? false;
    }

    public static function getLastRound($projectid, $cfg_which_database = 0)
    {
        $rows = self::getNavigationRounds((int) $projectid, (int) $cfg_which_database);

        return $rows ? $rows[array_key_last($rows)] : false;
    }

    public static function getPreviousRound($roundid, $projectid, $cfg_which_database = 0)
    {
        $rows = self::getNavigationRounds((int) $projectid, (int) $cfg_which_database);
        $index = self::findRoundIndex($rows, $roundid);

        if ($index === null) {
            return $rows[0] ?? false;
        }

        return $rows[max(0, $index - 1)] ?? false;
    }

    public static function getNextRound($roundid, $projectid, $cfg_which_database = 0)
    {
        $rows = self::getNavigationRounds((int) $projectid, (int) $cfg_which_database);
        $index = self::findRoundIndex($rows, $roundid);

        if ($index === null) {
            return $rows[0] ?? false;
        }

        return $rows[min(count($rows) - 1, $index + 1)] ?? false;
    }

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
            ->where($db->quoteName('project_id') . ' = ' . $projectId);

        try {
            $db->setQuery($query);

            return (int) $db->loadResult();
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return 0;
        }
    }

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
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->order($db->quoteName('roundcode') . ' ASC')
            ->order($db->quoteName('id') . ' ASC');

        try {
            $db->setQuery($query);

            return array_map('intval', $db->loadColumn() ?: []);
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return [];
        }
    }

    public function getNextRoundByToday($projectid): array
    {
        $projectId = (int) $projectid;

        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $today = Factory::getDate()->format('Y-m-d');
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('roundcode'),
                $db->quoteName('round_date_first'),
                $db->quoteName('round_date_last'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->where($db->quoteName('round_date_first') . ' > ' . $db->quote($today))
            ->order($db->quoteName('round_date_first') . ' ASC');

        try {
            $db->setQuery($query);

            return $db->loadAssocList() ?: [];
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

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
            $start = Factory::getDate()->format('Y-m-d');
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
        $date = Factory::getDate();
        $user = Factory::getApplication()->getIdentity();
        $currentDate = null;
        $currentCode = 0;

        try {
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
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUND_FAILED'));

            return false;
        }

        return true;
    }

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
                "CONCAT_WS(':', " . $db->quoteName('id') . ', ' . $db->quoteName('alias') . ') AS ' . $db->quoteName('value'),
                $db->quoteName('name', 'text'),
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('round_date_first'),
                $db->quoteName('round_date_last'),
                $db->quoteName('roundcode'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->order($db->quoteName('roundcode') . ' ' . $direction)
            ->order($db->quoteName('id') . ' ' . $direction);

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

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
            ->where($db->quoteName('p.id') . ' = ' . $projectId);

        try {
            $db->setQuery($query, 0, 1);

            return $db->loadObject() ?: false;
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

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

        $app = Factory::getApplication();
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

        $query->where($db->quoteName('pt.project_id') . ' = ' . $projectId);

        if ($divisionId > 0) {
            $query->where($db->quoteName('pt.division_id') . ' = ' . $divisionId);
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

    protected function populateState($ordering = 'r.roundcode', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = Factory::getApplication();
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

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $projectId = (int) self::$_project_id;

        if ($projectId > 0) {
            $seasonQuery = $db->createQuery()
                ->select($db->quoteName('season_id'))
                ->from($db->quoteName('#__sportsmanagement_project'))
                ->where($db->quoteName('id') . ' = ' . $projectId);
            $db->setQuery($seasonQuery);
            Factory::getApplication()->setUserState(
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
            ->where($db->quoteName('r.project_id') . ' = ' . $projectId);

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('LOWER(' . $db->quoteName('r.name') . ') LIKE LOWER(' . $token . ')');
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('r.published') . ' = ' . (int) $state);
        }

        $tournement = $this->getState('filter.tournement');

        if ($tournement !== '' && is_numeric($tournement)) {
            $query->where($db->quoteName('r.tournement') . ' = ' . (int) $tournement);
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

    protected function getStoreId($id = '')
    {
        $id .= ':' . self::$_project_id;
        $id .= ':' . (string) $this->getState('filter.search');
        $id .= ':' . (string) $this->getState('filter.state');
        $id .= ':' . (string) $this->getState('filter.tournement');

        return parent::getStoreId($id);
    }

    private static function getNavigationRounds(int $projectId, int $databaseConfig): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = self::resolveSportsManagementDatabase($databaseConfig);
        $query = $db->createQuery()
            ->select([
                "CONCAT_WS(':', " . $db->quoteName('id') . ', ' . $db->quoteName('alias') . ') AS ' . $db->quoteName('id'),
                $db->quoteName('id', 'round_id'),
                $db->quoteName('roundcode'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->order($db->quoteName('roundcode') . ' ASC')
            ->order($db->quoteName('id') . ' ASC');

        try {
            $db->setQuery($query);

            return $db->loadAssocList() ?: [];
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return [];
        }
    }

    private static function findRoundIndex(array $rows, $roundId): ?int
    {
        $needle = (string) $roundId;
        $numericNeedle = (int) explode(':', $needle, 2)[0];

        foreach ($rows as $index => $row) {
            if ((string) ($row['id'] ?? '') === $needle || (int) ($row['round_id'] ?? 0) === $numericNeedle) {
                return $index;
            }
        }

        return null;
    }

    private static function resolveSportsManagementDatabase(int $databaseConfig = 0): DatabaseInterface
    {
        return (new SportsManagementDatabaseResolver())->resolve(
            $databaseConfig,
            Factory::getContainer()->get(DatabaseInterface::class)
        );
    }
}
