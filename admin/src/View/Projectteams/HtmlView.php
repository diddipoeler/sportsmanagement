<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Projectteams;

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
    public array $divisionOptions = [];
    public array $playgroundOptions = [];
    public bool $individualProject = false;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters') ?: [];
        $model = $this->getModel();
        $this->project = $model->getProjectContext();
        if (!$this->project) throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 404);
        $this->divisionOptions = $model->getDivisionOptions();
        $this->playgroundOptions = $model->getPlaygroundOptions();
        $this->individualProject = (int) $this->project->project_art_id === 3;
        if ($errors = $this->get('Errors')) throw new \RuntimeException(implode("\n", $errors), 500);

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_TITLE') . ' - ' . (string) $this->project->name, 'users');
        ToolbarHelper::apply('projectteams.saveshort');
        ToolbarHelper::publish('projectteams.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('projectteams.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::custom('projectteams.use_table_yes', 'check', 'check', Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_INSCORE'), true);
        ToolbarHelper::custom('projectteams.use_table_no', 'cancel', 'cancel', Text::_('JNO'), true);
        ToolbarHelper::custom('projectteams.use_table_points_yes', 'check', 'check', Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_USE_FINALLY'), true);
        ToolbarHelper::custom('projectteams.use_table_points_no', 'cancel', 'cancel', Text::_('JNO'), true);
        ToolbarHelper::archiveList('projectteams.archive');
        ToolbarHelper::trash('projectteams.trash');
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=projects');
        parent::display($tpl);
    }
}
