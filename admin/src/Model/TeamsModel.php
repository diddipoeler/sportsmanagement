<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class TeamsModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            't.name', 'name', 't.website', 'website', 't.email', 'email',
            't.middle_name', 'middle_name', 't.short_name', 'short_name',
            't.info', 'info', 't.alias', 'alias', 't.picture', 'picture',
            't.id', 'id', 't.ordering', 'ordering', 't.published', 'published', 'state',
            'c.name', 'clubname', 'c.country', 'country', 'ag.name', 'agename',
            'st.name', 'sportstype',
        ];
        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 't.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);
        $app = Factory::getApplication();
        $input = $app->input;
        $legacy = [
            'search_nation' => 'filter_search_nation',
            'search_agegroup' => 'filter_search_agegroup',
            'sports_type' => 'filter_sports_type',
        ];

        foreach ($legacy as $stateName => $inputName) {
            if ((string) $this->getState('filter.' . $stateName) === '') {
                $value = $input->getString($inputName, '');
                if ($value !== '') {
                    $this->setState('filter.' . $stateName, $value);
                }
            }
        }

        $clubId = $input->getInt('club_id', 0);
        $this->setState('filter.club_id', $clubId);
        $app->setUserState('com_sportsmanagement.club_id', $clubId);
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('t.id'), $db->quoteName('t.name'), $db->quoteName('t.alias'),
                $db->quoteName('t.middle_name'), $db->quoteName('t.short_name'),
                $db->quoteName('t.website'), $db->quoteName('t.email'), $db->quoteName('t.info'),
                $db->quoteName('t.picture'), $db->quoteName('t.club_id'), $db->quoteName('t.sports_type_id'),
                $db->quoteName('t.agegroup_id'), $db->quoteName('t.published'), $db->quoteName('t.ordering'),
                $db->quoteName('t.checked_out'), $db->quoteName('t.checked_out_time'),
                $db->quoteName('st.name', 'sportstype'), $db->quoteName('ag.name', 'agename'),
                $db->quoteName('c.name', 'clubname'), $db->quoteName('c.country'), $db->quoteName('uc.name', 'editor'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_sports_type', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('t.sports_type_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_agegroup', 'ag') . ' ON ' . $db->quoteName('ag.id') . ' = ' . $db->quoteName('t.agegroup_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('t.checked_out'));

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('(' . $db->quoteName('t.name') . ' LIKE ' . $token . ' OR ' . $db->quoteName('t.short_name') . ' LIKE ' . $token . ' OR ' . $db->quoteName('t.middle_name') . ' LIKE ' . $token . ')');
        }

        $country = trim((string) $this->getState('filter.search_nation'));
        if ($country !== '') {
            $query->where($db->quoteName('c.country') . ' = ' . $db->quote($country));
        }

        $sportstype = (int) $this->getState('filter.sports_type');
        if ($sportstype > 0) {
            $query->where($db->quoteName('t.sports_type_id') . ' = ' . $sportstype);
        }

        $agegroup = (int) $this->getState('filter.search_agegroup');
        if ($agegroup > 0) {
            $query->where($db->quoteName('t.agegroup_id') . ' = ' . $agegroup);
        }

        $state = $this->getState('filter.state');
        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('t.published') . ' = ' . (int) $state);
        }

        $clubId = (int) $this->getState('filter.club_id');
        if ($clubId > 0) {
            $query->where($db->quoteName('t.club_id') . ' = ' . $clubId);
        }

        $map = [
            't.name' => $db->quoteName('t.name'), 'name' => $db->quoteName('t.name'),
            't.website' => $db->quoteName('t.website'), 'website' => $db->quoteName('t.website'),
            't.email' => $db->quoteName('t.email'), 'email' => $db->quoteName('t.email'),
            't.middle_name' => $db->quoteName('t.middle_name'), 'middle_name' => $db->quoteName('t.middle_name'),
            't.short_name' => $db->quoteName('t.short_name'), 'short_name' => $db->quoteName('t.short_name'),
            't.info' => $db->quoteName('t.info'), 'info' => $db->quoteName('t.info'),
            't.picture' => $db->quoteName('t.picture'), 'picture' => $db->quoteName('t.picture'),
            't.id' => $db->quoteName('t.id'), 'id' => $db->quoteName('t.id'),
            't.ordering' => $db->quoteName('t.ordering'), 'ordering' => $db->quoteName('t.ordering'),
            't.published' => $db->quoteName('t.published'), 'published' => $db->quoteName('t.published'), 'state' => $db->quoteName('t.published'),
            'c.name' => $db->quoteName('c.name'), 'clubname' => $db->quoteName('c.name'),
            'c.country' => $db->quoteName('c.country'), 'country' => $db->quoteName('c.country'),
            'ag.name' => $db->quoteName('ag.name'), 'agename' => $db->quoteName('ag.name'),
            'st.name' => $db->quoteName('st.name'), 'sportstype' => $db->quoteName('st.name'),
        ];

        $ordering = (string) $this->getState('list.ordering', 't.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($map[$ordering] ?? $map['t.name']) . ' ' . $direction);
        return $query;
    }

    public function getInlineOptions(): array
    {
        $db = $this->getDatabase();
        $load = static function ($db, string $table): array {
            $query = $db->getQuery(true)
                ->select([$db->quoteName('id', 'value'), $db->quoteName('name', 'text')])
                ->from($db->quoteName($table))
                ->order($db->quoteName('name') . ' ASC');
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        };
        return [
            'sportstypes' => $load($db, '#__sportsmanagement_sports_type'),
            'agegroups' => $load($db, '#__sportsmanagement_agegroup'),
        ];
    }

    public function getTeamListSelect(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('id', 'value'), $db->quoteName('name'), $db->quoteName('club_id'), $db->quoteName('short_name'), $db->quoteName('middle_name'), $db->quoteName('info')])
            ->from($db->quoteName('#__sportsmanagement_team'))
            ->order($db->quoteName('name') . ' ASC');
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        foreach ($rows as $team) {
            $team->text = $team->name . ' - (' . $team->info . ')';
        }
        return $rows;
    }

    public function getTeams(int $playgroundId): array
    {
        if ($playgroundId <= 0) {
            return [];
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pt.id'), $db->quoteName('st.team_id'), $db->quoteName('pt.project_id'),
                $db->quoteName('p.name', 'project'), $db->quoteName('p.alias', 'project_alias'),
                $db->quoteName('t.name', 'team_name'), $db->quoteName('t.short_name'), $db->quoteName('t.notes'), $db->quoteName('t.alias', 'team_alias'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->where($db->quoteName('pt.standard_playground') . ' = ' . $playgroundId);
        $db->setQuery($query);
        $result = [];
        foreach ($db->loadObjectList() ?: [] as $row) {
            $result[$row->id] = (object) [
                'project_team' => [(object) ['id' => $row->id, 'team_id' => $row->team_id, 'project_id' => $row->project_id, 'project_slug' => $row->project_id . ':' . $row->project_alias]],
                'teaminfo' => [[(object) ['name' => $row->team_name, 'short_name' => $row->short_name, 'notes' => $row->notes, 'team_slug' => $row->team_id . ':' . $row->team_alias]]],
                'project' => $row->project,
            ];
        }
        return $result;
    }

    public function getTeamsFromMatches(array &$games): array
    {
        $ids = [];
        foreach ($games as $game) {
            $ids[] = (int) $game->team1;
            $ids[] = (int) $game->team2;
        }
        $ids = array_values(array_unique(array_filter($ids)));
        if (!$ids) {
            return [];
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('name')])
            ->from($db->quoteName('#__sportsmanagement_team'))
            ->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
        $db->setQuery($query);
        $result = [];
        foreach ($db->loadObjectList() ?: [] as $row) {
            $result[$row->id] = $row;
        }
        return $result;
    }
}
