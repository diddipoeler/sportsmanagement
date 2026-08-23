<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/** Native Joomla 5/6 administrator list model for persons/players. */
final class PlayersModel extends SportsManagementListModel
{
    public function __construct($config = [])
    {
        $config['filter_fields'] = [
            'pl.lastname', 'pl.firstname', 'pl.nickname', 'pl.birthday', 'pl.country',
            'pl.position_id', 'pl.id', 'pl.picture', 'pl.ordering', 'pl.knvbnr',
            'pl.published', 'pl.modified', 'pl.modified_by', 'pl.checked_out',
            'pl.checked_out_time', 'pl.agegroup_id', 'ag.name', 'state',
            'sports_type', 'search_agegroup', 'search_nation',
        ];
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $whichView = $input->getCmd('whichview');
        $layout = preg_replace('/_[34]$/', '', strtolower($input->getCmd('layout', 'default')));
        $assignLayout = in_array($layout, ['assignpersons', 'assignpersonsclub'], true);
        $assignClub = $input->getBool('assignclub') || $layout === 'assignpersonsclub';
        $option = 'com_sportsmanagement';

        $personType = $whichView === 'seasons' ? 0 : (int) $app->getUserState($option . '.persontype', 0);
        $projectId = $whichView === 'seasons' ? 0 : (int) $app->getUserState($option . '.pid', 0);
        $teamId = $whichView === 'seasons' ? 0 : (int) $app->getUserState($option . '.team_id', 0);
        $seasonId = $whichView === 'seasons' ? 0 : (int) $app->getUserState($option . '.season_id', 0);

        if ($assignLayout) {
            $seasonId = max(0, $input->getInt('season_id', $seasonId));
            $teamId = max(0, $input->getInt('team_id', $teamId));
            $personType = max(0, $input->getInt('persontype', $personType));
            $projectId = max(0, $input->getInt('project_id', $input->getInt('pid', $projectId)));
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('pl.*')
            ->select('pl.id AS id2')
            ->select('ag.name AS agegroup_name')
            ->select('uc.name AS editor')
            ->from($db->quoteName('#__sportsmanagement_person', 'pl'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_agegroup', 'ag') . ' ON ag.id = pl.agegroup_id')
            ->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON uc.id = pl.checked_out');

        if ($assignLayout && $assignClub) {
            $query->join('INNER', $db->quoteName('#__sportsmanagement_season_person_id', 'sp') . ' ON sp.person_id = pl.id')
                ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON c.id = sp.club_id')
                ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON t.club_id = c.id');

            if ($seasonId > 0) {
                $query->where('sp.season_id = ' . $seasonId);
            }

            if ($teamId > 0) {
                $query->where('t.id = ' . $teamId);
            }
        } elseif ($assignLayout && $whichView !== 'seasons' && $seasonId > 0) {
            $query->join('INNER', $db->quoteName('#__sportsmanagement_season_person_id', 'sp') . ' ON sp.person_id = pl.id')
                ->where('sp.season_id = ' . $seasonId);
        }

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $needle = $db->quote('%' . $db->escape(mb_strtolower($search), true) . '%', false);
            $query->where(
                '(LOWER(pl.lastname) LIKE ' . $needle
                . ' OR LOWER(pl.firstname) LIKE ' . $needle
                . ' OR LOWER(pl.nickname) LIKE ' . $needle
                . ' OR LOWER(pl.info) LIKE ' . $needle
                . ' OR LOWER(pl.knvbnr) LIKE ' . $needle . ')'
            );
        }

        $nation = trim((string) $this->getState('filter.search_nation'));

        if ($nation !== '' && $nation !== '0') {
            $query->where('pl.country = ' . $db->quote($nation));
        }

        $agegroup = (int) $this->getState('filter.search_agegroup');
        if ($agegroup > 0) {
            $query->where('pl.agegroup_id = ' . $agegroup);
        }

        $sportType = (int) $this->getState('filter.sports_type');
        if ($sportType > 0) {
            $query->where('pl.sports_type_id = ' . $sportType);
        }

        $state = $this->getState('filter.state');
        if ($state !== '' && is_numeric($state)) {
            $query->where('pl.published = ' . (int) $state);
        }

        if ($assignLayout && !$assignClub && $seasonId > 0) {
            $sub = $db->getQuery(true)->select('stp.person_id');

            switch ($personType) {
                case 1:
                case 2:
                    $sub->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'stp'))
                        ->where('stp.team_id = ' . $teamId)
                        ->where('stp.season_id = ' . $seasonId)
                        ->where('stp.persontype = ' . $personType);
                    break;
                case 3:
                    $sub->from($db->quoteName('#__sportsmanagement_season_person_id', 'stp'))
                        ->join('INNER', $db->quoteName('#__sportsmanagement_project_referee', 'pr') . ' ON pr.person_id = stp.id')
                        ->where('stp.season_id = ' . $seasonId)
                        ->where('stp.persontype = 3')
                        ->where('pr.project_id = ' . $projectId);
                    break;
                default:
                    $sub->from($db->quoteName('#__sportsmanagement_season_person_id', 'stp'))
                        ->where('stp.season_id = ' . $seasonId);
                    break;
            }

            $query->where('pl.id NOT IN (' . $sub . ')');
        }

        if ($assignLayout && $assignClub && $seasonId > 0 && in_array($personType, [1, 2, 3], true)) {
            $sub = $db->getQuery(true)->select('stp.person_id');

            if ($personType === 3) {
                $sub->from($db->quoteName('#__sportsmanagement_season_person_id', 'stp'))
                    ->join('INNER', $db->quoteName('#__sportsmanagement_project_referee', 'pr') . ' ON pr.person_id = stp.id')
                    ->where('stp.season_id = ' . $seasonId)
                    ->where('stp.persontype = 3')
                    ->where('pr.project_id = ' . $projectId);
            } else {
                $sub->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'stp'))
                    ->where('stp.team_id = ' . $teamId)
                    ->where('stp.season_id = ' . $seasonId)
                    ->where('stp.persontype = ' . $personType);
            }

            $query->where('pl.id NOT IN (' . $sub . ')');
        }

        $orderMap = [
            'pl.lastname' => 'pl.lastname', 'pl.firstname' => 'pl.firstname',
            'pl.nickname' => 'pl.nickname', 'pl.birthday' => 'pl.birthday',
            'pl.country' => 'pl.country', 'pl.position_id' => 'pl.position_id',
            'pl.id' => 'pl.id', 'pl.knvbnr' => 'pl.knvbnr',
            'pl.published' => 'pl.published', 'ag.name' => 'ag.name',
        ];
        $ordering = (string) $this->getState('list.ordering', 'pl.lastname');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC'));
        $query->order(($orderMap[$ordering] ?? 'pl.lastname') . ' ' . ($direction === 'DESC' ? 'DESC' : 'ASC'));

        return $query;
    }

    public function getPersonsToAssign(): array
    {
        $ids = array_values(array_unique(array_filter(array_map(
            'intval',
            (array) Factory::getApplication()->getInput()->get('cid', [], 'array')
        ))));

        if (!$ids) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(['pl.id', 'pl.firstname', 'pl.nickname', 'pl.lastname'])
            ->from($db->quoteName('#__sportsmanagement_person', 'pl'))
            ->where('pl.id IN (' . implode(',', $ids) . ')')
            ->where('pl.published = 1');

        return $db->setQuery($query)->loadObjectList();
    }

    public function getProjectTeamList(): array
    {
        $projectId = (int) Factory::getApplication()->getUserState('com_sportsmanagement.pid', 0);
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('t.id AS value, t.name AS text')
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.team_id = t.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON pt.team_id = st.id')
            ->where('pt.project_id = ' . $projectId)
            ->order('t.name ASC');

        return $db->setQuery($query)->loadObjectList();
    }

    public function getTeamName($teamId): string
    {
        $teamId = max(0, (int) $teamId);
        if ($teamId === 0) {
            return '';
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)->select('name')->from($db->quoteName('#__sportsmanagement_team'))->where('id = ' . $teamId);
        return (string) $db->setQuery($query, 0, 1)->loadResult();
    }

    public function getProjectTeamName($projectTeamId): string
    {
        $projectTeamId = max(0, (int) $projectTeamId);
        if ($projectTeamId === 0) {
            return '';
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('t.name')
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.team_id = t.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON pt.team_id = st.id')
            ->where('pt.id = ' . $projectTeamId);
        return (string) $db->setQuery($query, 0, 1)->loadResult();
    }

    public function getPersons(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('id AS value, lastname, firstname, info, weight, height, picture, birthday, notes, nickname, knvbnr, country, phone, mobile, email')
            ->from($db->quoteName('#__sportsmanagement_person'))
            ->where('published = 1')
            ->order('lastname ASC');
        return $db->setQuery($query)->loadObjectList();
    }

    public function getPersonListSelect(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('id, id AS value, firstname, lastname, nickname, birthday, info')
            ->select('LOWER(lastname) AS low_lastname, LOWER(firstname) AS low_firstname, LOWER(nickname) AS low_nickname')
            ->from($db->quoteName('#__sportsmanagement_person'))
            ->where("firstname <> '!Unknown'")
            ->where("lastname <> '!Player'")
            ->where("nickname <> '!Ghost'")
            ->order('lastname ASC, firstname ASC');
        $results = $db->setQuery($query)->loadObjectList();

        foreach ($results as $person) {
            $text = $person->lastname . ',' . $person->firstname;
            if (!empty($person->nickname)) {
                $text .= " '" . $person->nickname . "'";
            }
            if (!empty($person->birthday) && $person->birthday !== '0000-00-00') {
                $text .= ' (' . $person->birthday . ')';
            }
            $person->text = $text;
        }

        return $results;
    }

    public function getPositionOptions(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('id AS value, name AS text')
            ->from($db->quoteName('#__sportsmanagement_position'))
            ->where('published = 1')
            ->order('name ASC');
        return $db->setQuery($query)->loadObjectList();
    }

    public function getAgeGroupOptions(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('id AS value, name AS text')
            ->from($db->quoteName('#__sportsmanagement_agegroup'))
            ->where('published = 1')
            ->order('name ASC');
        return $db->setQuery($query)->loadObjectList();
    }

    public function getProjectName(int $projectId): string
    {
        if ($projectId <= 0) {
            return '';
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)->select('name')->from($db->quoteName('#__sportsmanagement_project'))->where('id = ' . $projectId);
        return (string) $db->setQuery($query, 0, 1)->loadResult();
    }

    protected function populateState($ordering = 'pl.lastname', $direction = 'ASC')
    {
        $ordering = $ordering ?: 'pl.lastname';
        $direction = $direction ?: 'ASC';
        parent::populateState($ordering, $direction);

        $app = Factory::getApplication();
        $input = $app->getInput();
        $legacyFilters = [
            'search' => ['filter_search', 'string'],
            'state' => ['filter_state', 'string'],
            'sports_type' => ['filter_sports_type', 'int'],
            'search_nation' => ['filter_search_nation', 'string'],
            'search_agegroup' => ['filter_search_agegroup', 'int'],
        ];

        foreach ($legacyFilters as $state => [$request, $type]) {
            if (!$input->exists($request)) {
                continue;
            }

            $value = $type === 'int' ? $input->getInt($request, 0) : $input->getString($request, '');
            $this->setState('filter.' . $state, $value);
        }

        if ($input->exists('limit')) {
            $limit = max(0, $input->getInt('limit', (int) $app->get('list_limit', 20)));
            $this->setState('list.limit', $limit);
        }

        if ($input->exists('limitstart')) {
            $this->setState('list.start', max(0, $input->getInt('limitstart')));
        }

        if ($input->exists('filter_order')) {
            $order = $input->getCmd('filter_order', 'pl.lastname');
            $this->setState('list.ordering', in_array($order, $this->filter_fields, true) ? $order : 'pl.lastname');
        }

        if ($input->exists('filter_order_Dir')) {
            $dir = strtoupper($input->getCmd('filter_order_Dir', 'ASC'));
            $this->setState('list.direction', $dir === 'DESC' ? 'DESC' : 'ASC');
        }
    }
}
