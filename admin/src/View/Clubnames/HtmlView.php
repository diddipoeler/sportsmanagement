<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Clubnames;

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

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBNAMES_TITLE'), 'address');
        ToolbarHelper::addNew('clubname.add');
        ToolbarHelper::editList('clubname.edit');
        ToolbarHelper::publish('clubnames.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('clubnames.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('clubnames.checkin');
        ToolbarHelper::trash('clubnames.trash');
        ToolbarHelper::custom('clubnames.import', 'upload', 'upload', Text::_('JTOOLBAR_INSTALL'), false);

        parent::display($tpl);
    }
}
