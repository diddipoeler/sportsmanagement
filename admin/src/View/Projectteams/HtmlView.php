<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Projectteams;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectteamsModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 administrator project-team list and modal workflows. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public array $projectteam = [];
    public $pagination;
    public $state;
    public $filterForm;
    public array $activeFilters = [];
    public ?object $project = null;
    public array $divisionOptions = [];
    public array $playgroundOptions = [];
    public array $projectsbyleagueseason = [];
    public array $quickAddTeams = [];
    public array $assignedTeamOptions = [];
    public array $availableTeamOptions = [];
    public array $replacementTeamOptions = [];
    public array $copyProjectOptions = [];
    public array $ptids = [];
    public bool $individualProject = false;
    public $user;
    public $app;
    public $document;
    public ProjectteamsModel $model;
    public string $view = 'projectteams';
    public string $request_url = '';
    public string $sortDirection = 'ASC';
    public string $sortColumn = 't.name';
    public int $project_id = 0;
    public int $project_art_id = 0;
    public int $season_id = 0;
    public int $league_id = 0;
    public int $sports_type_id = 0;
    public int $modalwidth = 900;
    public int $modalheight = 600;
    public string $assignModal = '';
    public string $changeTeamsModal = '';

    public function display($tpl = null)
    {
        $this->app = Factory::getApplication();
        $this->document = $this->getDocument();
        $this->user = $this->app->getIdentity();
        $model = $this->getModel();

        if (!$model instanceof ProjectteamsModel) {
            throw new \RuntimeException('Projectteams model could not be loaded.', 500);
        }

        $this->model = $model;
        $this->state = $this->get('State');
        $this->project = $model->getProjectContext();
        $this->request_url = Uri::getInstance()->toString();
        $this->sortDirection = (string) $this->state->get('list.direction', 'ASC');
        $this->sortColumn = (string) $this->state->get('list.ordering', 't.name');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->project) {
            $this->app->enqueueMessage(Text::_('JGLOBAL_NO_MATCHING_RESULTS'), 'warning');
            $this->items = [];
            $this->projectteam = [];
            $this->pagination = $this->get('Pagination');
            $this->filterForm = null;
            $this->activeFilters = [];
            ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_TITLE'), 'users');
            ToolbarHelper::back('JPREV', Route::_('index.php?option=com_sportsmanagement&view=projects', false));
            parent::display($tpl);
            return;
        }

        $this->project_id = (int) $this->project->id;
        $this->project_art_id = (int) $this->project->project_art_id;
        $this->season_id = (int) $this->project->season_id;
        $this->league_id = (int) $this->project->league_id;
        $this->sports_type_id = (int) $this->project->sports_type_id;
        $this->individualProject = $this->project_art_id === 3;
        $this->projectsbyleagueseason = $model->getProjectsByLeagueSeason(
            $this->season_id,
            $this->league_id
        );

        $this->app->setUserState($this->option . '.pid', $this->project_id);
        $this->app->setUserState($this->option . '.season_id', $this->season_id);
        $this->app->setUserState($this->option . '.project_art_id', $this->project_art_id);
        $this->app->setUserState($this->option . '.sports_type_id', $this->sports_type_id);

        $params = ComponentHelper::getParams($this->option);
        $this->modalheight = (int) $params->get('modal_popup_height', 600);
        $this->modalwidth = (int) $params->get('modal_popup_width', 900);

        $layout = preg_replace('/_[34]$/', '', strtolower((string) $this->getLayout())) ?: 'default';
        $this->setLayout($layout);

        if ($layout === 'copy') {
            $this->prepareCopy();
            parent::display($tpl);
            return;
        }

        $this->items = $this->get('Items') ?: [];
        $this->projectteam = $this->items;
        $this->pagination = $this->get('Pagination');
        $this->divisionOptions = $model->getDivisionOptions();
        $this->playgroundOptions = $model->getPlaygroundOptions();

        if ((string) $this->project->project_type === 'DIVISIONS_LEAGUE') {
            foreach ($this->projectteam as $team) {
                $model->checkProjectTeamDivision(
                    (int) ($team->projectteamid ?? $team->id ?? 0),
                    (int) ($team->id ?? 0),
                    $this->project_id,
                    (int) ($team->team_id ?? 0)
                );
            }
        }

        if ($layout === 'editlist') {
            $this->prepareAssignmentOptions();
            parent::display($tpl);
            return;
        }

        if ($layout === 'changeteams') {
            $this->prepareReplacementOptions();
            parent::display($tpl);
            return;
        }

        try {
            $this->filterForm = $this->get('FilterForm');
            $this->activeFilters = $this->get('ActiveFilters') ?: [];
        } catch (\Throwable $e) {
            $this->filterForm = null;
            $this->activeFilters = [];
            $this->app->enqueueMessage($e->getMessage(), 'warning');
        }

        if (!$this->individualProject && !empty($this->project->fast_projektteam)) {
            $this->quickAddTeams = $model->getCountryTeams();
        }

        $this->document->getWebAssetManager()->useScript('multiselect');
        $this->addToolbar();
        $this->prepareModals();

        parent::display($tpl);
    }

    private function prepareCopy(): void
    {
        $this->ptids = array_values(array_filter(array_map(
            'intval',
            (array) $this->app->getUserState('com_sportsmanagement.projectteams.copy.ids', [])
        )));
        $this->copyProjectOptions = (array) $this->app->getUserState(
            'com_sportsmanagement.projectteams.copy.projects',
            []
        );
    }

    private function prepareAssignmentOptions(): void
    {
        $assignedRows = $this->model->getProjectTeams($this->project_id, false);
        $assignedIds = [];

        foreach ($assignedRows as $row) {
            $relationId = (int) ($row->season_team_id ?? $row->value ?? 0);
            if ($relationId <= 0) {
                continue;
            }

            $assignedIds[$relationId] = true;
            $label = (string) ($row->text ?? $row->name ?? '');
            if (!empty($row->info)) {
                $label .= ' (' . $row->info . ')';
            }
            $this->assignedTeamOptions[] = (object) ['value' => $relationId, 'text' => $label];
        }

        foreach ($this->model->getTeams('') as $row) {
            $relationId = (int) ($row->value ?? 0);
            if ($relationId <= 0 || isset($assignedIds[$relationId])) {
                continue;
            }

            $label = (string) ($row->text ?? '');
            if (!empty($row->info)) {
                $label .= ' (' . $row->info . ')';
            }
            $this->availableTeamOptions[] = (object) ['value' => $relationId, 'text' => $label];
        }
    }

    private function prepareReplacementOptions(): void
    {
        $this->replacementTeamOptions = $this->model->getAllTeams($this->project_id) ?: [];
    }

    private function addToolbar(): void
    {
        $title = $this->individualProject
            ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTPERSONS_TITLE')
            : Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_TITLE');
        ToolbarHelper::title($title . ' - ' . (string) $this->project->name, 'users');
        ToolbarHelper::back(
            'JPREV',
            Route::_('index.php?option=com_sportsmanagement&view=project&layout=panel&id=' . $this->project_id, false)
        );
        ToolbarHelper::apply('projectteams.saveshort');
        ToolbarHelper::editList('projectteam.edit');
        ToolbarHelper::publish('projectteams.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('projectteams.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::archiveList('projectteams.archive');
        ToolbarHelper::trash('projectteams.trash');
        ToolbarHelper::checkin('projectteams.checkin');
        ToolbarHelper::deleteList('', 'projectteams.delete');
        ToolbarHelper::custom(
            'projectteams.setseasonid',
            'refresh',
            'refresh',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_SEASON_ID'),
            true
        );
        ToolbarHelper::custom(
            'projectteams.matchgroups',
            'shuffle',
            'shuffle',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_CHANGE_MATCH_GROUPS'),
            true
        );
        ToolbarHelper::custom('projectteams.copy', 'copy', 'copy', Text::_('JTOOLBAR_DUPLICATE'), true);
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
        ToolbarHelper::custom(
            'projectteams.set_playground',
            'location',
            'location',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_PLAYGROUND'),
            true
        );
        ToolbarHelper::custom(
            'projectteams.set_playground_match',
            'calendar',
            'calendar',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_PLAYGROUND_MATCH'),
            true
        );

        $toolbar = Toolbar::getInstance('toolbar');
        $toolbar->appendButton(
            'Custom',
            (new FileLayout('assignteams', JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/layouts'))->render(),
            'batch'
        );
        $toolbar->appendButton(
            'Custom',
            (new FileLayout('changeteams', JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/layouts'))->render(),
            'batch'
        );
    }

    private function prepareModals(): void
    {
        $this->assignModal = HTMLHelper::_('bootstrap.renderModal', 'collapseModalassignTeams', [
            'url' => 'index.php?option=com_sportsmanagement&view=projectteams&layout=editlist&tmpl=component&pid=' . $this->project_id,
            'height' => $this->modalheight,
            'width' => $this->modalwidth,
            'modalWidth' => '70',
        ]);
        $this->changeTeamsModal = HTMLHelper::_('bootstrap.renderModal', 'collapseModalchangeTeams', [
            'url' => 'index.php?option=com_sportsmanagement&view=projectteams&layout=changeteams&tmpl=component&pid=' . $this->project_id,
            'height' => $this->modalheight,
            'width' => $this->modalwidth,
            'modalWidth' => '70',
        ]);
    }
}
