<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Teams;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TeamsModel;
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
    public $inlineOptions = [];
    public $clubId = 0;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof TeamsModel) {
            throw new \RuntimeException('Teams view requires TeamsModel.', 500);
        }

        $this->items = $model->getItems() ?: [];
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();
        $this->filterForm = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters() ?: [];
        $this->inlineOptions = $model->getInlineOptions();
        $this->clubId = (int) $this->state->get('filter.club_id');

        if ($errors = $model->getErrors()) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_TITLE'), 'users');
        ToolbarHelper::apply('teams.saveshort');
        ToolbarHelper::addNew('team.add');
        ToolbarHelper::editList('team.edit');
        ToolbarHelper::custom('teams.copysave', 'copy', 'copy', Text::_('JTOOLBAR_DUPLICATE'), true);
        ToolbarHelper::publish('teams.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('teams.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('teams.checkin');
        ToolbarHelper::trash('teams.trash');
        ToolbarHelper::custom('team.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::archiveList('team.export', Text::_('JTOOLBAR_EXPORT'));

        if ($this->clubId > 0) {
            ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=clubs');
        }

        parent::display($tpl);
    }
}
