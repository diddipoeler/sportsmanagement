<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Extrafields;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 extra-fields list view. */
final class HtmlView extends BaseHtmlView
{
    public $items = [];
    public $pagination;
    public $state;
    public $filterForm;
    public $activeFilters = [];

    public function display($tpl = null)
    {
        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters') ?: [];

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_EXTRAFIELDS_TITLE'), 'list');
        ToolbarHelper::addNew('extrafield.add');
        ToolbarHelper::editList('extrafield.edit');
        ToolbarHelper::publish('extrafields.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('extrafields.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('extrafields.checkin');
        ToolbarHelper::trash('extrafields.trash');

        parent::display($tpl);
    }
}
