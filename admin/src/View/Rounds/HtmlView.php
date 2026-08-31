<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Rounds;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\RoundsModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator view for project rounds. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;
    public $filterForm;
    public array $activeFilters = [];
    public $project = null;
    public $projectws = null;
    public int $project_id = 0;
    public array $teams = [];
    public int $division_id = 0;
    public int $massadd = 0;
    public int $populate = 0;

    public function display($tpl = null)
    {
        $app = $this->getApplication();
        $input = $app->getInput();
        $model = $this->getModel();

        if (!$model instanceof RoundsModel) {
            throw new \RuntimeException('Rounds model could not be loaded.', 500);
        }

        $layout = strtolower((string) $this->getLayout());
        $layout = preg_replace('/_[34]$/', '', $layout) ?: 'default';

        if (!in_array($layout, ['default', 'populate', 'massadd'], true)) {
            $layout = 'default';
        }

        $this->setLayout($layout);
        $this->state = $model->getState();
        $this->project_id = $input->getInt(
            'pid',
            (int) $app->getUserState('com_sportsmanagement.pid', 0)
        );

        if ($this->project_id > 0) {
            $app->setUserState('com_sportsmanagement.pid', $this->project_id);
        }

        $this->project = $model->getProject($this->project_id);
        $this->projectws = $this->project;

        if ($errors = $model->getErrors()) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->project && $this->project_id > 0) {
            throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_ERROR'), 404);
        }

        switch ($layout) {
            case 'populate':
                $this->populate = 1;
                $this->division_id = $input->getInt('division_id', 0);
                $this->teams = $model->getProjectTeamsOptions($this->project_id, $this->division_id);
                $this->getDocument()->setTitle(Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TITLE'));
                $this->addPopulateToolbar();
                break;

            case 'massadd':
                $this->massadd = 1;
                $this->addMassaddToolbar();
                break;

            default:
                $this->items = $model->getItems() ?: [];
                $this->pagination = $model->getPagination();
                $this->filterForm = $model->getFilterForm();
                $this->activeFilters = $model->getActiveFilters() ?: [];
                $this->addDefaultToolbar();
                break;
        }

        parent::display($tpl);
    }

    private function addProjectBackButton(): void
    {
        if ($this->project_id <= 0) {
            return;
        }

        ToolbarHelper::back(
            'JPREV',
            Route::_('index.php?option=com_sportsmanagement&view=project&layout=panel&id=' . $this->project_id, false)
        );
    }

    private function addDefaultToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_TITLE'), 'calendar');
        $this->addProjectBackButton();
        ToolbarHelper::publishList('rounds.publish');
        ToolbarHelper::unpublishList('rounds.unpublish');
        ToolbarHelper::link(
            Route::_('index.php?option=com_sportsmanagement&view=rounds&layout=populate&pid=' . $this->project_id, false),
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_BUTTON'),
            'refresh'
        );
        ToolbarHelper::apply('rounds.saveshort');
        ToolbarHelper::link(
            Route::_('index.php?option=com_sportsmanagement&view=rounds&layout=massadd&pid=' . $this->project_id, false),
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSADD_BUTTON'),
            'new'
        );
        ToolbarHelper::addNew('round.add');
        ToolbarHelper::deleteList(
            '',
            'rounds.deleteroundmatches',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSDEL_BUTTON')
        );
    }

    private function addPopulateToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TITLE'), 'calendar');
        ToolbarHelper::apply('round.startpopulate');
        ToolbarHelper::back(
            'JTOOLBAR_BACK',
            Route::_('index.php?option=com_sportsmanagement&view=rounds&pid=' . $this->project_id, false)
        );
    }

    private function addMassaddToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSADD_BUTTON'), 'calendar');
        ToolbarHelper::apply('rounds.massadd', 'JSAVE');
        ToolbarHelper::back(
            'JTOOLBAR_BACK',
            Route::_('index.php?option=com_sportsmanagement&view=rounds&pid=' . $this->project_id, false)
        );
    }
}
