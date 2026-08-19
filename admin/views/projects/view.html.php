<?php
/** Administrator projects list view. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Service\ProjectsViewDataService;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewProjects extends sportsmanagementView
{
    public $filterForm;
    public $activeFilters;

    public function init()
    {
        $service = new ProjectsViewDataService($this->model->getDatabase());
        $this->show_notassign = $this->state->get('filter.show_notassign');
        $this->sortDirection = (string) $this->state->get('list.direction', 'ASC');
        $this->sortColumn = (string) $this->state->get('list.ordering', 'p.name');

        $this->userfields = $service->getExtraFields('project');
        $this->league = $service->getLeagues();
        $this->sports_type = $service->getSportsTypes();
        $this->season = $service->getSeasons();
        $this->search_agegroup = $service->getAgeGroups();
        $this->search_association = $service->getAssociations();
        $this->search_nation = $service->getCountries();
        $this->projectData = $service;

        foreach ($this->items as $row) {
            $row->user_field = $service->getProjectExtraFieldNames((int) $row->id);
        }

        $lists = [];
        $lists['league'] = $this->league;
        $lists['sportstype'] = $this->sports_type;
        $lists['seasons'] = $this->season;
        $lists['nation'] = array_merge(
            [HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'))],
            $this->search_nation
        );
        $lists['project_type'] = [
            HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_PROJECTTYPE_FILTER')),
            HTMLHelper::_('select.option', 'SIMPLE_LEAGUE', Text::_('COM_SPORTSMANAGEMENT_SIMPLE_LEAGUE')),
            HTMLHelper::_('select.option', 'DIVISIONS_LEAGUE', Text::_('COM_SPORTSMANAGEMENT_DIVISIONS_LEAGUE')),
            HTMLHelper::_('select.option', 'TOURNAMENT_MODE', Text::_('COM_SPORTSMANAGEMENT_TOURNAMENT_MODE')),
            HTMLHelper::_('select.option', 'FRIENDLY_MATCHES', Text::_('COM_SPORTSMANAGEMENT_FRIENDLY_MATCHES')),
        ];
        $lists['mastertemplates'] = array_merge(
            [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_TEMPLATES'))],
            $service->getMasterTemplates()
        );
        $lists['agegroup'] = array_merge(
            [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'))],
            $this->search_agegroup
        );
        $lists['yesno'] = [
            HTMLHelper::_('select.option', 0, Text::_('JNO')),
            HTMLHelper::_('select.option', 1, Text::_('JYES')),
        ];
        $this->lists = $lists;
        $this->season_ids = ComponentHelper::getParams($this->option)->get('current_season', []);

        try {
            $this->filterForm = $this->model->getFilterForm();
            $this->activeFilters = $this->model->getActiveFilters();
        } catch (\Throwable $e) {
            $this->filterForm = null;
            $this->activeFilters = [];
            $this->app->enqueueMessage($e->getMessage(), 'warning');
        }
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_TITLE');
        $this->icon = 'projects';

        ToolbarHelper::publishList('projects.publish');
        ToolbarHelper::unpublishList('projects.unpublish');
        ToolbarHelper::divider();
        ToolbarHelper::apply('projects.saveshort');
        ToolbarHelper::addNew('project.add');
        ToolbarHelper::editList('project.edit');
        ToolbarHelper::archiveList(
            'projects.setleaguechampion',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_LEAGUECHAMPION')
        );
        ToolbarHelper::custom('projects.copy', 'copy', 'copy', Text::_('JTOOLBAR_DUPLICATE'), false);

        parent::addToolbar();
    }
}
