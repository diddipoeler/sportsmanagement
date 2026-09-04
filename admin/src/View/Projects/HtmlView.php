<?php
/**
 * SportsManagement Joomla 5/6 migration.
 *
 * @version    5.6.0 sportsmanagement
 * @author     diddipoeler <diddipoeler@gmx.de>
 * @copyright  Copyright (C) diddipoeler. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Projects;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectsModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\ProjectsViewDataService;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Database\DatabaseInterface;

/**
 * Native Joomla 5/6 administrator projects list view.
 */
final class HtmlView extends BaseHtmlView
{
    public $items = [];
    public $pagination;
    public $state;
    public $filterForm;
    public $activeFilters = [];
    public $model;
    public $user;
    public $projectData;
    public $lists = [];
    public $userfields = [];
    public $league = [];
    public $sports_type = [];
    public $season = [];
    public $search_agegroup = [];
    public $search_association = [];
    public $search_nation = [];
    public $season_ids = [];
    public $show_notassign = 0;
    public string $sortDirection = 'ASC';
    public string $sortColumn = 'p.name';

    public function display($tpl = null)
    {
        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->model = $this->getModel();

        if (!$this->model instanceof ProjectsModel) {
            throw new \RuntimeException('ProjectsModel is unavailable.', 500);
        }

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $app = Factory::getApplication();
        $this->user = $app->getIdentity();
        $this->show_notassign = (int) $this->state->get('filter.show_notassign', 0);
        $this->sortDirection = (string) $this->state->get('list.direction', 'ASC');
        $this->sortColumn = (string) $this->state->get('list.ordering', 'p.name');

        /**
         * DatabaseAwareTrait::getDatabase() is protected in Joomla, so a view
         * must not call it on the model. Resolve the same SportsManagement
         * database connection here that the MVC factory injects into models.
         */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $sportsManagementDatabase = SportsManagementDatabaseResolver::resolve($joomlaDatabase, 0);
        $service = new ProjectsViewDataService($sportsManagementDatabase);
        $this->projectData = $service;
        $this->userfields = $service->getExtraFields('project');
        $this->league = $service->getLeagues();
        $this->sports_type = $service->getSportsTypes();
        $this->season = $service->getSeasons();
        $this->search_agegroup = $service->getAgeGroups();
        $this->search_association = $service->getAssociations();
        $this->search_nation = $service->getCountries();

        foreach ($this->items as $row) {
            $row->user_field = $service->getProjectExtraFieldNames((int) $row->id);
        }

        $this->lists = [
            'league' => $this->league,
            'sportstype' => $this->sports_type,
            'seasons' => $this->season,
            'nation' => array_merge(
                [HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'))],
                $this->search_nation
            ),
            'project_type' => [
                HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_PROJECTTYPE_FILTER')),
                HTMLHelper::_('select.option', 'SIMPLE_LEAGUE', Text::_('COM_SPORTSMANAGEMENT_SIMPLE_LEAGUE')),
                HTMLHelper::_('select.option', 'DIVISIONS_LEAGUE', Text::_('COM_SPORTSMANAGEMENT_DIVISIONS_LEAGUE')),
                HTMLHelper::_('select.option', 'TOURNAMENT_MODE', Text::_('COM_SPORTSMANAGEMENT_TOURNAMENT_MODE')),
                HTMLHelper::_('select.option', 'FRIENDLY_MATCHES', Text::_('COM_SPORTSMANAGEMENT_FRIENDLY_MATCHES')),
            ],
            'mastertemplates' => array_merge(
                [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_TEMPLATES'))],
                $service->getMasterTemplates()
            ),
            'agegroup' => array_merge(
                [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'))],
                $this->search_agegroup
            ),
            'yesno' => [
                HTMLHelper::_('select.option', 0, Text::_('JNO')),
                HTMLHelper::_('select.option', 1, Text::_('JYES')),
            ],
        ];

        $this->season_ids = (array) ComponentHelper::getParams('com_sportsmanagement')->get('current_season', []);

        try {
            $this->filterForm = $this->model->getFilterForm();
            $this->activeFilters = $this->model->getActiveFilters() ?: [];
        } catch (\Throwable $e) {
            $this->filterForm = null;
            $this->activeFilters = [];
            $app->enqueueMessage($e->getMessage(), 'warning');
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_TITLE'), 'projects');
        ToolbarHelper::publish('projects.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('projects.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::apply('projects.saveshort');
        ToolbarHelper::addNew('project.add');
        ToolbarHelper::editList('project.edit');
        ToolbarHelper::archiveList(
            'projects.setleaguechampion',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_LEAGUECHAMPION')
        );
        ToolbarHelper::custom('projects.copy', 'copy', 'copy', Text::_('JTOOLBAR_DUPLICATE'), false);
        ToolbarHelper::checkin('projects.checkin');
        ToolbarHelper::trash('projects.trash');

        if ($this->user && $this->user->authorise('core.admin', 'com_sportsmanagement')) {
            ToolbarHelper::preferences('com_sportsmanagement');
        }
    }
}
