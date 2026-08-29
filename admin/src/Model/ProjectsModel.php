<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/** Native Joomla 5/6 administrator projects list model. */
final class ProjectsModel extends SportsManagementListModel
{
    public string $_identifier = 'projects';

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'p.name', 'name',
            'l.name', 'league',
            'l.country', 'country',
            's.name', 'season',
            'st.name', 'sportstype',
            'p.project_type', 'project_type',
            'p.master_template', 'master_template',
            'p.cr_project', 'cr_project',
            'p.published', 'published', 'state',
            'p.id', 'id',
            'p.ordering', 'ordering',
            'p.picture', 'picture',
            'ag.name', 'agegroup',
            'p.agegroup_id', 'agegroup_id',
        ];

        parent::__construct($config, $factory);
    }

    public function getFilterForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/forms');

        return parent::getFilterForm($data, $loadData);
    }

    public function existcurrentseason($season_ids = [], $league_id = 0): ?int
    {
        $seasonIds = $this->normaliseIds($season_ids);
        $leagueId = (int) $league_id;

        if (!$seasonIds || $leagueId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('p.id'))
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->where($db->quoteName('p.league_id') . ' = ' . $leagueId)
            ->where($db->quoteName('p.season_id') . ' IN (' . implode(',', $seasonIds) . ')');

        try {
            $db->setQuery($query, 0, 1);
            $id = (int) $db->loadResult();

            return $id > 0 ? $id : null;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return null;
        }
    }

    protected function populateState($ordering = 'p.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = $this->administratorApplication();
        $input = $app->getInput();
        $filters = [
            'search' => ['filter_search', 'string'],
            'state' => ['filter_state', 'string'],
            'search_nation' => ['filter_search_nation', 'string'],
            'association' => ['filter_association', 'int'],
            'season' => ['filter_season', 'int'],
            'copytoseason' => ['filter_copytoseason', 'int'],
            'search_league' => ['filter_search_league', 'int'],
            'sports_type' => ['filter_sports_type', 'int'],
            'search_association' => ['filter_search_association', 'int'],
            'project_type' => ['filter_project_type', 'string'],
            'userfields' => ['filter_userfields', 'int'],
            'search_agegroup' => ['filter_search_agegroup', 'int'],
            'unique_id' => ['filter_unique_id', 'int'],
            'search_associations_leagues' => ['filter_search_associations_leagues', 'int'],
            'show_notassign' => ['filter_show_notassign', 'int'],
        ];

        foreach ($filters as $state => [$request, $type]) {
            $value = $type === 'int' ? $input->getInt($request, 0) : $input->getString($request, '');

            if ($type === 'int') {
                if ($value !== 0 || $input->exists($request)) {
                    $this->setState('filter.' . $state, $value);
                }
            } elseif ($value !== '') {
                $this->setState('filter.' . $state, $value);
            }
        }

        // populateState() is still running here. Read the registry directly so
        // Joomla's lazy getState() initialisation cannot re-enter this method.
        $nation = (string) $this->state->get('filter.search_nation', '');
        $league = (int) $this->state->get('filter.search_league', 0);
        $association = (int) $this->state->get('filter.search_associations_leagues', 0);
        $app->setUserState('com_sportsmanagement.projects_search_nation', $nation);
        $app->setUserState('com_sportsmanagement.projects_search_league', $league);
        $app->setUserState('com_sportsmanagement.projects_search_associations_leagues', $association);
        $app->setUserState('com_sportsmanagement.projectnation', $nation);
        $input->set('projects_search_nation', $nation);
        $input->set('projects_search_league', $league);
        $input->set('projects_search_associations_leagues', $association);
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $uniqueMode = (int) $this->getState('filter.unique_id', 0);

        $teamCount = $db->getQuery(true)
            ->select('COUNT(' . $db->quoteName('pt.id') . ')')
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->where($db->quoteName('pt.project_id') . ' = ' . $db->quoteName('p.id'));

        if ($uniqueMode === 1 || $uniqueMode === 2) {
            $teamCount
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_season_team_id', 'pst')
                    . ' ON ' . $db->quoteName('pst.id') . ' = ' . $db->quoteName('pt.team_id')
                )
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_team', 'ptm')
                    . ' ON ' . $db->quoteName('ptm.id') . ' = ' . $db->quoteName('pst.team_id')
                )
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_club', 'pc')
                    . ' ON ' . $db->quoteName('pc.id') . ' = ' . $db->quoteName('ptm.club_id')
                );

            if ($uniqueMode === 1) {
                $teamCount->where(
                    '(' . $db->quoteName('pc.unique_id') . ' IS NULL OR '
                    . $db->quoteName('pc.unique_id') . ' = ' . $db->quote('') . ')'
                );
            } else {
                $teamCount
                    ->where($db->quoteName('pc.unique_id') . ' IS NOT NULL')
                    ->where($db->quoteName('pc.unique_id') . ' <> ' . $db->quote(''));
            }
        }

        $notAssigned = $db->getQuery(true)
            ->select('COUNT(' . $db->quoteName('co.id') . ')')
            ->from($db->quoteName('#__sportsmanagement_confidential', 'co'))
            ->where($db->quoteName('co.project') . ' = ' . $db->quoteName('p.id'))
            ->where($db->quoteName('co.team_id') . ' = 0');

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.ordering'),
                $db->quoteName('p.published'),
                $db->quoteName('p.project_type'),
                $db->quoteName('p.name'),
                $db->quoteName('p.alias'),
                $db->quoteName('p.checked_out'),
                $db->quoteName('p.checked_out_time'),
                $db->quoteName('p.sports_type_id'),
                $db->quoteName('p.current_round'),
                $db->quoteName('p.picture'),
                $db->quoteName('p.agegroup_id'),
                $db->quoteName('p.master_template'),
                $db->quoteName('p.fast_projektteam'),
                $db->quoteName('p.league_id'),
                $db->quoteName('p.season_id'),
                $db->quoteName('p.use_leaguechampion'),
                $db->quoteName('p.cr_project'),
                $db->quoteName('p.project_live_update'),
                $db->quoteName('p.modified'),
                $db->quoteName('p.modified_by'),
                $db->quoteName('u1.username'),
                $db->quoteName('st.name', 'sportstype'),
                $db->quoteName('s.name', 'season'),
                $db->quoteName('l.name', 'league'),
                $db->quoteName('l.country'),
                $db->quoteName('u.name', 'editor'),
                $db->quoteName('ag.name', 'agegroup'),
                '(' . $teamCount . ') AS ' . $db->quoteName('proteams'),
                '(' . $notAssigned . ') AS ' . $db->quoteName('notassign'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season', 's')
                . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_league', 'l')
                . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_sports_type', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('p.sports_type_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_agegroup', 'ag')
                . ' ON ' . $db->quoteName('ag.id') . ' = ' . $db->quoteName('p.agegroup_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u')
                . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('p.checked_out')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u1')
                . ' ON ' . $db->quoteName('u1.id') . ' = ' . $db->quoteName('p.modified_by')
            );

        $userFieldId = (int) $this->getState('filter.userfields', 0);
        if ($userFieldId > 0) {
            $query
                ->select([
                    $db->quoteName('ev.fieldvalue', 'user_fieldvalue'),
                    $db->quoteName('ev.id', 'user_field_id'),
                ])
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_user_extra_fields_values', 'ev')
                    . ' ON ' . $db->quoteName('ev.jl_id') . ' = ' . $db->quoteName('p.id')
                )
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_user_extra_fields', 'ef')
                    . ' ON ' . $db->quoteName('ef.id') . ' = ' . $db->quoteName('ev.field_id')
                )
                ->where($db->quoteName('ef.id') . ' = ' . $userFieldId)
                ->where($db->quoteName('ef.template_backend') . ' = ' . $db->quote('project'));
        }

        $search = trim((string) $this->getState('filter.search', ''));
        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('LOWER(' . $db->quoteName('p.name') . ') LIKE LOWER(' . $token . ')');
        }

        foreach ([
            'search_league' => 'p.league_id',
            'sports_type' => 'p.sports_type_id',
            'season' => 'p.season_id',
            'search_agegroup' => 'p.agegroup_id',
            'search_association' => 'l.associations',
            'search_associations_leagues' => 'l.associations',
        ] as $state => $column) {
            $value = (int) $this->getState('filter.' . $state, 0);
            if ($value > 0) {
                $query->where($db->quoteName($column) . ' = ' . $value);
            }
        }

        $published = $this->getState('filter.state');
        if ($published !== '' && is_numeric($published)) {
            $query->where($db->quoteName('p.published') . ' = ' . (int) $published);
        }

        $nation = trim((string) $this->getState('filter.search_nation', ''));
        if ($nation !== '' && $nation !== '0') {
            $query->where($db->quoteName('l.country') . ' = ' . $db->quote($nation));
        }

        $projectType = trim((string) $this->getState('filter.project_type', ''));
        if ($projectType !== '' && $projectType !== '0') {
            $query->where($db->quoteName('p.project_type') . ' = ' . $db->quote($projectType));
        }

        if ((int) $this->getState('filter.show_notassign', 0) === 1) {
            $query->having($db->quoteName('notassign') . ' <> 0');
        }

        $map = [
            'p.name' => $db->quoteName('p.name'), 'name' => $db->quoteName('p.name'),
            'l.name' => $db->quoteName('l.name'), 'league' => $db->quoteName('l.name'),
            'l.country' => $db->quoteName('l.country'), 'country' => $db->quoteName('l.country'),
            's.name' => $db->quoteName('s.name'), 'season' => $db->quoteName('s.name'),
            'st.name' => $db->quoteName('st.name'), 'sportstype' => $db->quoteName('st.name'),
            'p.project_type' => $db->quoteName('p.project_type'), 'project_type' => $db->quoteName('p.project_type'),
            'p.master_template' => $db->quoteName('p.master_template'), 'master_template' => $db->quoteName('p.master_template'),
            'p.cr_project' => $db->quoteName('p.cr_project'), 'cr_project' => $db->quoteName('p.cr_project'),
            'p.published' => $db->quoteName('p.published'), 'published' => $db->quoteName('p.published'), 'state' => $db->quoteName('p.published'),
            'p.id' => $db->quoteName('p.id'), 'id' => $db->quoteName('p.id'),
            'p.ordering' => $db->quoteName('p.ordering'), 'ordering' => $db->quoteName('p.ordering'),
            'p.picture' => $db->quoteName('p.picture'), 'picture' => $db->quoteName('p.picture'),
            'ag.name' => $db->quoteName('ag.name'), 'agegroup' => $db->quoteName('ag.name'),
            'p.agegroup_id' => $db->quoteName('p.agegroup_id'), 'agegroup_id' => $db->quoteName('p.agegroup_id'),
        ];
        $ordering = (string) $this->getState('list.ordering', 'p.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($map[$ordering] ?? $map['p.name']) . ' ' . $direction);

        return $query;
    }

    private function normaliseIds($ids): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', (array) $ids),
            static fn (int $id): bool => $id > 0
        )));
    }
}
