<?php
/** Joomla 5/6 administrator players view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewplayers extends sportsmanagementView
{
    public function init()
    {
        $input = $this->app->getInput();
        $this->state = $this->get('State');
        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->total = $this->get('Total');
        $this->sortDirection = (string) $this->state->get('list.direction', 'ASC');
        $this->sortColumn = (string) $this->state->get('list.ordering', 'pl.lastname');
        $this->assign = false;
        $this->season_id = 0;
        $this->team_id = 0;
        $this->project_id = 0;
        $this->persontype = 0;
        $this->whichview = $input->getCmd('whichview');
        $this->assignclub = $input->getBool('assignclub');

        $layout = preg_replace('/_(3|4)$/', '', (string) $this->getLayout());
        if ($layout !== $this->getLayout()) {
            $this->setLayout($layout);
        }

        if ($layout === 'assignpersons' || $layout === 'assignpersonsclub') {
            $this->season_id = $input->getInt('season_id');
            $this->team_id = $input->getInt('team_id');
            $this->persontype = $input->getInt('persontype');
            $this->project_id = $input->getInt('project_id')
                ?: (int) $this->app->getUserState($this->option . '.pid', 0);
            $this->assign = true;
        }

        if ($layout === 'players_upload') {
            $this->setLayout('players_upload');
        }

        $positions = [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION'))];
        $positions = array_merge($positions, $this->model->getPositionOptions());

        $agegroups = [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'))];
        $agegroups = array_merge($agegroups, $this->model->getAgeGroupOptions());

        $countries = [HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'))];
        $countryOptions = JSMCountries::getCountryOptions();
        if ($countryOptions) {
            $countries = array_merge($countries, $countryOptions);
        }

        $this->lists = [
            'positions' => $positions,
            'nation' => $countries,
            'agegroup' => $agegroups,
            'search_mode' => '',
        ];

        if ($this->assign) {
            $projectTeamId = (int) $this->app->getUserState($this->option . '.project_team_id', 0);
            $this->prjid = $this->project_id;
            $this->prj_name = $this->model->getProjectName($this->project_id);
            $this->project_team_id = $projectTeamId;
            $this->team_name = $this->model->getProjectTeamName($projectTeamId);
            $this->type = $input->getInt('type');
        }

        if ($layout === 'assignconfirm') {
            $this->project_id = $input->getInt('project_id')
                ?: (int) $this->app->getUserState($this->option . '.pid', 0);
            $this->season_id = $input->getInt('season_id')
                ?: (int) $this->app->getUserState($this->option . '.season_id', 0);
            $this->persons = $this->model->getPersonsToAssign();
            $this->projectname = $this->model->getProjectName($this->project_id);
            $this->lists['type'] = HTMLHelper::_(
                'select.genericlist',
                [
                    HTMLHelper::_('select.option', 1, Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_ASSIGN_PLAYERS')),
                    HTMLHelper::_('select.option', 2, Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_ASSIGN_STAFF')),
                    HTMLHelper::_('select.option', 3, Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_ASSIGN_REFEREES')),
                ],
                'persontype',
                'class="form-select"',
                'value',
                'text',
                1
            );
            $this->lists['teams'] = HTMLHelper::_(
                'select.genericlist',
                $this->model->getProjectTeamList(),
                'team_id',
                'class="form-select"',
                'value',
                'text',
                0
            );
        }
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_TITLE');

        if ($this->assign || in_array($this->getLayout(), ['players_upload', 'assignconfirm'], true)) {
            return;
        }

        ToolbarHelper::publish('players.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('players.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::divider();
        ToolbarHelper::apply('players.saveshort');
        ToolbarHelper::editList('player.edit');
        ToolbarHelper::addNew('player.add');
        ToolbarHelper::custom('player.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        parent::addToolbar();
    }
}
