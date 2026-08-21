<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Eventtypes;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 event-types list view. */
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

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_EVENTS_TITLE'), 'list');
        ToolbarHelper::addNew('eventtype.add');
        ToolbarHelper::editList('eventtype.edit');
        ToolbarHelper::publish('eventtypes.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('eventtypes.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('eventtypes.checkin');
        ToolbarHelper::trash('eventtypes.trash');
        ToolbarHelper::custom('eventtype.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::archiveList('eventtype.export', Text::_('JTOOLBAR_EXPORT'));

        parent::display($tpl);
    }
}
