<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class TeamsModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            't.name', 'name',
            't.sports_type_id', 'sports_type',
            't.website', 'website',
            't.email', 'email',
            't.middle_name', 'middle_name',
            't.short_name', 'short_name',
            't.info', 'info',
            't.alias', 'alias',
            't.picture', 'picture',
            't.id', 'id',
            't.ordering', 'ordering',
            't.checked_out', 'checked_out',
            't.checked_out_time', 'checked_out_time',
            't.agegroup_id', 'agegroup_id',
            't.published', 'published', 'state',
            'c.name', 'clubname',
            'c.country', 'country',
            'ag.name', 'agename',
            'st.name', 'sportstype',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 't.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = $this->administratorApplication();
        $input = $app->getInput();

        $legacy = [
            'search_nation' => 'filter_search_nation',
            'search_agegroup' => 'filter_search_agegroup',
            'sports_type' => 'filter_sports_type',
        ];

        foreach ($legacy as $stateName => $inputName) {
            if ((string) $this->state->get('filter.' . $stateName, '') === '') {
                $value = $input->getString($inputName, '');

                if ($value !== '') {
                    $this->setState('filter.' . $stateName, $value);
                }
            }
        }

        $clubId = $input->post->getInt('club_id', 0);

        if ($clubId <= 0) {
            $clubId = $input->getInt('club_id', 0);
        }

        $layout = $input->getCmd('layout', '');
        $seasonId = $layout === 'assignteams' ? $input->getInt('season_id', 0) : 0;

        $this->setState('filter.club_id', max(0, $clubId));
        $this->setState('layout', $layout);
        $this->setState('season.id', max(0, $seasonId));
        $app->setUserState('com_sportsmanagement.club_id', max(0, $clubId));

        $globalLimit = (int) $app->getUserStateFromRequest('com_sportsmanagement.limit', 'limit', 0, 'int');

        if ($globalLimit > 0) {
            $this->setState('list.limit', $globalLimit);
        }
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('t') . '.*',
                $db->quoteName('st.name', 'sportstype'),
                $db->quoteName('ag.name', 'agename'),
                $db->quoteName('c.name', 'clubname'),
                $db->quoteName('c.country'),
                $db->quoteName('uc.name', 'editor'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_sports_type', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('t.sports_type_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_agegroup', 'ag')
                . ' ON ' . $db->quoteName('ag.id') . ' = ' . $db->quoteName('t.agegroup_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_club', 'c')
                . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'uc')
                . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('t.checked_out')
            );

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where(
                '('
                . 'LOWER(' . $db->quoteName('t.name') . ') LIKE LOWER(' . $token . ')'
                . ' OR LOWER(' . $db->quoteName('t.short_name') . ') LIKE LOWER(' . $token . ')'
                . ' OR LOWER(' . $db->quoteName('t.middle_name') . ') LIKE LOWER(' . $token . ')'
                . ')'
            );
        }

        $country = trim((string) $this->getState('filter.search_nation'));

        if ($country !== '') {
            $query->where($db->quoteName('c.country') . ' = ' . $db->quote($country));
        }

        $sportsType = (int) $this->getState('filter.sports_type');

        if ($sportsType > 0) {
            $query->where($db->quoteName('t.sports_type_id') . ' = ' . $sportsType);
        }

        $ageGroup = (int) $this->getState('filter.search_agegroup');

        if ($ageGroup > 0) {
            $query->where($db->quoteName('t.agegroup_id') . ' = ' . $ageGroup);
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('t.published') . ' = ' . (int) $state);
        }

        $clubId = (int) $this->getState('filter.club_id');

        if ($clubId > 0) {
            $query->where($db->quoteName('t.club_id') . ' = ' . $clubId);
        }

        if ((string) $this->getState('layout') === 'assignteams') {
            $seasonId = (int) $this->getState('season.id');

            if ($seasonId > 0) {
                $subQuery = $db->getQuery(true)
                    ->select($db->quoteName('stp.team_id'))
                    ->from($db->quoteName('#__sportsmanagement_season_team_id', 'stp'))
                    ->where($db->quoteName('stp.season_id') . ' = ' . $seasonId);

                $query->where($db->quoteName('t.id') . ' NOT IN (' . $subQuery . ')');
            }
        }

        $map = [
            't.name' => $db->quoteName('t.name'),
            'name' => $db->quoteName('t.name'),
            't.sports_type_id' => $db->quoteName('t.sports_type_id'),
            'sports_type' => $db->quoteName('t.sports_type_id'),
            't.website' => $db->quoteName('t.website'),
            'website' => $db->quoteName('t.website'),
            't.email' => $db->quoteName('t.email'),
            'email' => $db->quoteName('t.email'),
            't.middle_name' => $db->quoteName('t.middle_name'),
            'middle_name' => $db->quoteName('t.middle_name'),
            't.short_name' => $db->quoteName('t.short_name'),
            'short_name' => $db->quoteName('t.short_name'),
            't.info' => $db->quoteName('t.info'),
            'info' => $db->quoteName('t.info'),
            't.alias' => $db->quoteName('t.alias'),
            'alias' => $db->quoteName('t.alias'),
            't.picture' => $db->quoteName('t.picture'),
            'picture' => $db->quoteName('t.picture'),
            't.id' => $db->quoteName('t.id'),
            'id' => $db->quoteName('t.id'),
            't.ordering' => $db->quoteName('t.ordering'),
            'ordering' => $db->quoteName('t.ordering'),
            't.checked_out' => $db->quoteName('t.checked_out'),
            'checked_out' => $db->quoteName('t.checked_out'),
            't.checked_out_time' => $db->quoteName('t.checked_out_time'),
            'checked_out_time' => $db->quoteName('t.checked_out_time'),
            't.agegroup_id' => $db->quoteName('t.agegroup_id'),
            'agegroup_id' => $db->quoteName('t.agegroup_id'),
            't.published' => $db->quoteName('t.published'),
            'published' => $db->quoteName('t.published'),
            'state' => $db->quoteName('t.published'),
            'c.name' => $db->quoteName('c.name'),
            'clubname' => $db->quoteName('c.name'),
            'c.country' => $db->quoteName('c.country'),
            'country' => $db->quoteName('c.country'),
            'ag.name' => $db->quoteName('ag.name'),
            'agename' => $db->quoteName('ag.name'),
            'st.name' => $db->quoteName('st.name'),
            'sportstype' => $db->quoteName('st.name'),
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
                ->select([
                    $db->quoteName('id', 'value'),
                    $db->quoteName('name', 'text'),
                ])
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
            ->select([
                $db->quoteName('id'),
                $db->quoteName('id', 'value'),
                $db->quoteName('name'),
                $db->quoteName('club_id'),
                $db->quoteName('short_name'),
                $db->quoteName('middle_name'),
                $db->quoteName('info'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team'))
            ->order($db->quoteName('name') . ' ASC');

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        foreach ($rows as $team) {
            $team->text = $team->name . ' - (' . $team->info . ')';
        }

        return $rows;
    }

    public function getTeams($playgroundId): array
    {
        $playgroundId = (int) $playgroundId;

        if ($playgroundId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pt.id'),
                $db->quoteName('st.team_id'),
                $db->quoteName('pt.project_id'),
                $db->quoteName('p.name', 'project'),
                $db->quoteName('p.alias', 'project_alias'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('t.short_name'),
                $db->quoteName('t.notes'),
                $db->quoteName('t.alias', 'team_alias'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
            )
            ->where($db->quoteName('pt.standard_playground') . ' = ' . $playgroundId);

        $db->setQuery($query);
        $result = [];

        foreach ($db->loadObjectList() ?: [] as $row) {
            $result[$row->id] = (object) [
                'project_team' => [
                    (object) [
                        'id' => $row->id,
                        'team_id' => $row->team_id,
                        'project_id' => $row->project_id,
                        'project_slug' => $row->project_id . ':' . $row->project_alias,
                    ],
                ],
                'teaminfo' => [
                    [
                        (object) [
                            'name' => $row->team_name,
                            'short_name' => $row->short_name,
                            'notes' => $row->notes,
                            'team_slug' => $row->team_id . ':' . $row->team_alias,
                        ],
                    ],
                ],
                'project' => $row->project,
            ];
        }

        return $result;
    }

    public function getTeamsFromMatches(&$games): array
    {
        $ids = [];

        foreach ((array) $games as $game) {
            $ids[] = (int) ($game->team1 ?? 0);
            $ids[] = (int) ($game->team2 ?? 0);
        }

        $ids = array_values(array_unique(array_filter($ids)));

        if (!$ids) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team'))
            ->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');

        $db->setQuery($query);
        $result = [];

        foreach ($db->loadObjectList() ?: [] as $row) {
            $result[$row->id] = $row;
        }

        return $result;
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . (string) $this->getState('layout');
        $id .= ':' . (int) $this->getState('season.id');
        $id .= ':' . (int) $this->getState('filter.club_id');
        $id .= ':' . (string) $this->getState('filter.search');
        $id .= ':' . (string) $this->getState('filter.search_nation');
        $id .= ':' . (string) $this->getState('filter.search_agegroup');
        $id .= ':' . (string) $this->getState('filter.sports_type');
        $id .= ':' . (string) $this->getState('filter.state');

        return parent::getStoreId($id);
    }
}
