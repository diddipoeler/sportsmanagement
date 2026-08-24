<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Players;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\CountryOptionsHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Model\PlayersModel;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 administrator view for persons and assignment workflows. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;
    public $filterForm;
    public array $activeFilters = [];
    public array $positionOptions = [];
    public array $agegroupOptions = [];
    public array $countryOptions = [];
    public array $persons = [];
    public array $projectTeamOptions = [];
    public $app;
    public $document;
    public $user;
    public $option = 'com_sportsmanagement';
    public string $request_url = '';
    public string $sortDirection = 'ASC';
    public string $sortColumn = 'pl.lastname';
    public bool $assign = false;
    public bool $assignclub = false;
    public int $season_id = 0;
    public int $team_id = 0;
    public int $project_id = 0;
    public int $project_team_id = 0;
    public int $persontype = 0;
    public int $type = 0;
    public string $whichview = '';
    public string $projectname = '';
    public string $prj_name = '';
    public string $team_name = '';

    public function display($tpl = null)
    {
        $this->app = Factory::getApplication();
        $this->document = $this->getDocument();
        $this->user = $this->app->getIdentity();
        $this->request_url = Uri::getInstance()->toString();

        $model = $this->getModel();
        if (!$model instanceof PlayersModel) {
            throw new \RuntimeException('PlayersModel is unavailable.', 500);
        }

        $layout = preg_replace('/_[34]$/', '', strtolower((string) $this->getLayout())) ?: 'default';
        if (!in_array($layout, ['default', 'assignpersons', 'assignpersonsclub', 'players_upload', 'assignconfirm'], true)) {
            $layout = 'default';
        }
        $this->setLayout($layout);

        $this->state = $this->get('State');
        $this->sortDirection = (string) $this->state->get('list.direction', 'ASC');
        $this->sortColumn = (string) $this->state->get('list.ordering', 'pl.lastname');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if ($layout === 'players_upload') {
            ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_TITLE'), 'users');
            ToolbarHelper::back('JPREV', Route::_('index.php?option=com_sportsmanagement&view=players', false));
            parent::display($tpl);
            return;
        }

        $input = $this->app->getInput();
        $this->whichview = $input->getCmd('whichview', '');
        $this->assignclub = $input->getBool('assignclub') || $layout === 'assignpersonsclub';
        $this->assign = in_array($layout, ['assignpersons', 'assignpersonsclub'], true);
        $this->project_id = $input->getInt('project_id')
            ?: $input->getInt('pid')
            ?: (int) $this->app->getUserState($this->option . '.pid', 0);
        $this->season_id = $input->getInt('season_id')
            ?: (int) $this->app->getUserState($this->option . '.season_id', 0);
        $this->team_id = $input->getInt('team_id')
            ?: (int) $this->app->getUserState($this->option . '.team_id', 0);
        $this->project_team_id = $input->getInt('project_team_id')
            ?: (int) $this->app->getUserState($this->option . '.project_team_id', 0);
        $this->persontype = $input->getInt('persontype')
            ?: (int) $this->app->getUserState($this->option . '.persontype', 0);
        $this->type = $input->getInt('type');

        if ($this->project_id > 0) {
            $this->app->setUserState($this->option . '.pid', $this->project_id);
        }
        if ($this->season_id > 0) {
            $this->app->setUserState($this->option . '.season_id', $this->season_id);
        }
        if ($this->team_id > 0) {
            $this->app->setUserState($this->option . '.team_id', $this->team_id);
        }
        if ($this->project_team_id > 0) {
            $this->app->setUserState($this->option . '.project_team_id', $this->project_team_id);
        }
        if ($this->persontype > 0) {
            $this->app->setUserState($this->option . '.persontype', $this->persontype);
        }

        if ($layout === 'assignconfirm') {
            $this->persons = $model->getPersonsToAssign();
            $this->projectname = $model->getProjectName($this->project_id);
            $this->projectTeamOptions = $model->getProjectTeamList();
            parent::display($tpl);
            return;
        }

        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters') ?: [];

        $this->positionOptions = array_merge(
            [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION'))],
            $model->getPositionOptions()
        );
        $this->agegroupOptions = array_merge(
            [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'))],
            $model->getAgeGroupOptions()
        );

        try {
            $databaseSelector = $input->getInt(
                'cfg_which_database',
                (int) $this->app->getUserState($this->option . '.cfg_which_database', 0)
            );
            $db = (new SportsManagementDatabaseResolver())->resolve($databaseSelector);
            $this->countryOptions = array_merge(
                [HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'))],
                CountryOptionsHelper::getOptions($db)
            );
        } catch (\Throwable $e) {
            $this->countryOptions = [
                HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY')),
            ];
            $this->app->enqueueMessage($e->getMessage(), 'warning');
        }

        $this->document->getWebAssetManager()->useScript('multiselect');

        if ($this->assign) {
            $this->prj_name = $model->getProjectName($this->project_id);
            $this->team_name = $this->project_team_id > 0
                ? $model->getProjectTeamName($this->project_team_id)
                : $model->getTeamName($this->team_id);
        } else {
            $this->addToolbar();
        }

        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_TITLE'), 'users');
        ToolbarHelper::publish('players.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('players.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::apply('players.saveshort');
        ToolbarHelper::editList('player.edit');
        ToolbarHelper::addNew('player.add');
        ToolbarHelper::deleteList('', 'players.delete');
        ToolbarHelper::checkin('players.checkin');
        ToolbarHelper::custom('player.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
    }
}
