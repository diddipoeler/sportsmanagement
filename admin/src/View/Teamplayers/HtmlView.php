<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Teamplayers;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

final class HtmlView extends BaseHtmlView
{
    public $items = [];
    public $pagination;
    public $state;
    public $filterForm;
    public $activeFilters = [];
    public ?object $project = null;
    public ?object $teamContext = null;
    public array $positionOptions = [];
    public array $contextParams = [];
    public int $personType = 1;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters') ?: [];
        $model = $this->getModel();
        $this->project = $model->getProjectContext();
        $this->teamContext = $model->getTeamContext();
        $this->positionOptions = $model->getProjectPositionOptions();
        $this->contextParams = $model->getContextParams();
        $this->personType = (int) $this->state->get('filter.persontype', 1);

        if (!$this->project || !$this->teamContext) {
            throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 404);
        }
        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $titleKey = $this->personType === 2 ? 'COM_SPORTSMANAGEMENT_ADMIN_TSTAFFS_TITLE' : 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_TITLE';
        ToolbarHelper::title(Text::_($titleKey) . ' ' . (string) $this->teamContext->team_name, 'users');
        ToolbarHelper::apply('teamplayers.saveshort');
        ToolbarHelper::publish('teamplayers.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('teamplayers.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::archiveList('teamplayers.archive');
        ToolbarHelper::trash('teamplayers.trash');
        ToolbarHelper::back('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_BACK', 'index.php?option=com_sportsmanagement&view=projectteams&pid=' . (int) $this->project->id);
        parent::display($tpl);
    }
}
