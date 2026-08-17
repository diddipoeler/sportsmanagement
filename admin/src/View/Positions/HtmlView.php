<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Positions;

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
    public $parents = [];

    public function display($tpl = null)
    {
        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters') ?: [];
        $this->parents = $this->getModel()->getParentsPositions();

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_TITLE'), 'list');
        ToolbarHelper::apply('positions.saveshort');
        ToolbarHelper::addNew('position.add');
        ToolbarHelper::editList('position.edit');
        ToolbarHelper::publish('positions.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('positions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('positions.checkin');
        ToolbarHelper::trash('positions.trash');

        parent::display($tpl);
    }
}
