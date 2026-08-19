<?php
/** Administrator project teams view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewprojectteams extends sportsmanagementView
{
    public function init()
    {
        $input = $this->app->getInput();
        $this->state = $this->get('State');
        $this->sortDirection = (string) $this->state->get('list.direction', 'ASC');
        $this->sortColumn = (string) $this->state->get('list.ordering', 't.name');
        $this->division = $input->getInt('division', 0);
        $this->project_id = $input->getInt('pid', 0)
            ?: (int) $this->app->getUserState($this->option . '.pid', 0);

        $this->project = $this->model->getProjectContext();

        if (!$this->project) {
            $this->app->enqueueMessage(Text::_('JGLOBAL_NO_MATCHING_RESULTS'), 'warning');
            $this->items = [];
            $this->projectteam = [];
            $this->lists = [];

            return;
        }

        $this->project_art_id = (int) $this->project->project_art_id;
        $this->season_id = (int) $this->project->season_id;
        $this->league_id = (int) $this->project->league_id;
        $this->sports_type_id = (int) $this->project->sports_type_id;
        $this->projectsbyleagueseason = $this->model->getProjectsByLeagueSeason(
            $this->season_id,
            $this->league_id
        );

        $this->app->setUserState($this->option . '.pid', $this->project_id);
        $this->app->setUserState($this->option . '.season_id', $this->season_id);
        $this->app->setUserState($this->option . '.project_art_id', $this->project_art_id);
        $this->app->setUserState($this->option . '.sports_type_id', $this->sports_type_id);

        $this->items = $this->get('Items') ?: [];
        $this->projectteam = $this->items;
        $this->pagination = $this->get('Pagination');
        $this->total = $this->get('Total');

        // Existing row layouts only need this one checkout predicate from the old table object.
        $this->table = new class {
            public function isCheckedOut($userId, $checkedOut): bool
            {
                return (int) $checkedOut > 0 && (int) $checkedOut !== (int) $userId;
            }
        };

        $lists = [];

        if (!empty($this->project->fast_projektteam)) {
            $lists['country_teams'] = $this->model->getCountryTeams();
            $lists['country_teams_picture'] = $this->model->getCountryTeamsPicture();
            $this->tips[] = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_QUICKADD_DESCR');
        }

        $divisionOptions = [
            HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DIVISION')),
        ];
        $divisionRows = $this->model->getDivisionOptions();
        $this->divisions = $divisionRows ? array_merge($divisionOptions, $divisionRows) : $divisionOptions;
        $lists['divisions'] = $this->divisions;

        $assignedRows = $this->model->getProjectTeams($this->project_id, false);
        $assignedRelationIds = [];
        $assignedOptions = [];
        $assignedNameOptions = [];

        foreach ($assignedRows as $row) {
            $relationId = (int) ($row->season_team_id ?? 0);
            $assignedRelationIds[$relationId] = true;
            $label = (string) ($row->text ?? '');

            if (!empty($row->info)) {
                $label .= ' (' . $row->info . ')';
            }

            $assignedOptions[] = HTMLHelper::_('select.option', $relationId, $label);
            $assignedNameOptions[] = HTMLHelper::_('select.option', $relationId, (string) ($row->text ?? ''));
        }

        $lists['project_teams'] = HTMLHelper::_(
            'select.genericlist',
            $assignedOptions,
            'project_teamslist[]',
            'style="width:250px; height:300px;" class="inputbox" multiple size="' . max(10, min(30, count($assignedOptions))) . '"',
            'value',
            'text'
        );
        $lists['project_teamslist_name'] = HTMLHelper::_(
            'select.genericlist',
            $assignedNameOptions,
            'project_teamslist_name[]',
            'id="project_teamslist_name" style="width:250px; height:300px;" class="inputbox" multiple size="' . max(10, min(30, count($assignedNameOptions))) . '"',
            'value',
            'text'
        );
        $lists['project_new_season_teams'] = '<select name="project_new_season_teams[]" id="project_new_season_teams" style="width:250px; height:300px; display:none" class="inputbox" multiple size="10"></select>';

        $post = $input->post->getArray();
        $editSearchNation = isset($post['edit_search_nation'])
            ? trim((string) $post['edit_search_nation'])
            : (string) $this->state->get('filter.search_nation', '');
        $filterNation = (string) $this->state->get('filter.search_nation', '');

        if ($filterNation === '') {
            $filterNation = (string) ($this->project->country ?? '');
        }

        $availableOptions = [];
        foreach ($this->model->getTeams($filterNation) as $row) {
            $relationId = (int) ($row->value ?? 0);

            if ($relationId <= 0 || isset($assignedRelationIds[$relationId])) {
                continue;
            }

            $label = (string) ($row->text ?? '');
            if (!empty($row->info)) {
                $label .= ' (' . $row->info . ')';
            }
            $availableOptions[] = HTMLHelper::_('select.option', $relationId, $label);
        }

        if (!$availableOptions) {
            $this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ADD_TEAM'), 'notice');
        }

        $lists['teams'] = HTMLHelper::_(
            'select.genericlist',
            $availableOptions,
            'teamslist[]',
            'style="width:250px; height:300px;" class="inputbox" multiple size="' . max(10, min(30, count($availableOptions))) . '"',
            'value',
            'text'
        );

        $nation = [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'))];
        if (class_exists('JSMCountries')) {
            $countryOptions = (array) JSMCountries::getCountryOptions();
            $nation = array_merge($nation, $countryOptions);
            $this->search_nation = $countryOptions;
        }

        $lists['nation'] = $nation;
        $lists['nationpt'] = HTMLHelper::_(
            'select.genericlist',
            $nation,
            'filter_search_nation',
            'class="inputbox" style="width:140px" onchange="this.form.submit();"',
            'value',
            'text',
            (string) $this->state->get('filter.search_nation', '')
        );
        $lists['countrylist'] = HTMLHelper::_(
            'select.genericlist',
            $nation,
            'edit_search_nation',
            'class="inputbox" style="width:140px" onchange="this.form.submit();"',
            'value',
            'text',
            $editSearchNation
        );

        $lists['finaltablerank'] = [];
        for ($rank = 0; $rank <= 40; $rank++) {
            $lists['finaltablerank'][] = HTMLHelper::_('select.option', $rank, $rank);
        }

        $allTeams = [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM'))];
        $otherTeams = $this->model->getAllTeams($this->project_id);
        if ($otherTeams) {
            $allTeams = array_merge($allTeams, $otherTeams);
        } else {
            $this->notes[] = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_NO_CHANGE_TEAMS');
        }
        $lists['all_teams'] = $allTeams;

        $yesNo = [
            HTMLHelper::_('select.option', 0, Text::_('JNO')),
            HTMLHelper::_('select.option', 1, Text::_('JYES')),
        ];
        $lists['is_in_score'] = $yesNo;
        $lists['use_finally'] = $yesNo;
        $lists['search_mode'] = '';

        // Compatibility for the existing row template; match counts are native now.
        $this->modelmatches = $this->model;
        $this->lists = $lists;

        if ((string) $this->project->project_type === 'DIVISIONS_LEAGUE') {
            foreach ($this->projectteam as $team) {
                $this->model->checkProjectTeamDivision(
                    (int) $team->projectteamid,
                    (int) $team->id,
                    (int) $team->project_id,
                    (int) $team->team_id
                );
            }
        }

        switch ($this->getLayout()) {
            case 'editlist':
            case 'editlist_3':
            case 'editlist_4':
                $this->setLayout('editlist');
                break;

            case 'changeteams':
            case 'changeteams_3':
            case 'changeteams_4':
                foreach ($this->projectteam as $team) {
                    $team->name = (string) $team->name . ' (' . (string) $team->seasonname . ')';
                }
                $this->setLayout('changeteams');
                break;
        }
    }

    protected function addToolbar()
    {
        if (!$this->project) {
            return;
        }

        $this->app->setUserState($this->option . '.pid', $this->project_id);
        $this->app->setUserState($this->option . '.season_id', $this->season_id);
        $this->app->setUserState($this->option . '.project_art_id', $this->project_art_id);
        $this->app->setUserState($this->option . '.sports_type_id', $this->sports_type_id);

        $this->title = $this->project_art_id !== 3
            ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_TITLE')
            : Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTPERSONS_TITLE');

        ToolbarHelper::back(
            'JPREV',
            'index.php?option=com_sportsmanagement&view=project&layout=panel&id=' . $this->project_id
        );
        ToolbarHelper::apply('projectteams.saveshort');
        ToolbarHelper::custom(
            'projectteams.setseasonid',
            'purge.png',
            'purge_f2.png',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_SEASON_ID'),
            true
        );
        ToolbarHelper::custom(
            'projectteams.matchgroups',
            'purge.png',
            'purge_f2.png',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_CHANGE_MATCH_GROUPS'),
            true
        );
        ToolbarHelper::deleteList('', 'projectteams.delete');

        $layout = new FileLayout('changeteams', JPATH_ROOT . '/components/com_sportsmanagement/layouts');
        Toolbar::getInstance('toolbar')->appendButton('Custom', $layout->render(), 'batch');
        echo HTMLHelper::_('bootstrap.renderModal', 'collapseModalchangeTeams', [
            'url' => 'index.php?option=com_sportsmanagement&view=projectteams&layout=changeteams&tmpl=component&pid=' . $this->project_id,
            'height' => $this->modalheight,
            'width' => $this->modalwidth,
            'modalWidth' => '60',
        ]);

        $layout = new FileLayout('assignteams', JPATH_ROOT . '/components/com_sportsmanagement/layouts');
        Toolbar::getInstance('toolbar')->appendButton('Custom', $layout->render(), 'batch');
        echo HTMLHelper::_('bootstrap.renderModal', 'collapseModalassignTeams', [
            'url' => 'index.php?option=com_sportsmanagement&view=projectteams&layout=editlist&tmpl=component&pid=' . $this->project_id,
            'height' => $this->modalheight,
            'width' => $this->modalwidth,
            'modalWidth' => '60',
        ]);

        ToolbarHelper::custom('projectteam.copy', 'copy', 'copy', Text::_('JTOOLBAR_DUPLICATE'), true);
        ToolbarHelper::checkin('projectteams.checkin');
        ToolbarHelper::publish(
            'projectteams.use_table_yes',
            'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_USE_TABLE_YES',
            true
        );
        ToolbarHelper::unpublish(
            'projectteams.use_table_no',
            'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_USE_TABLE_NO',
            true
        );
        ToolbarHelper::publish(
            'projectteams.use_table_points_yes',
            'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_USE_TABLE_POINTS_YES',
            true
        );
        ToolbarHelper::unpublish(
            'projectteams.use_table_points_no',
            'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_USE_TABLE_POINTS_NO',
            true
        );
        ToolbarHelper::unpublish(
            'projectteams.set_playground',
            'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_PLAYGROUND',
            true
        );
        ToolbarHelper::unpublish(
            'projectteams.set_playground_match',
            'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_PLAYGROUND_MATCH',
            true
        );

        parent::addToolbar();
    }
}
