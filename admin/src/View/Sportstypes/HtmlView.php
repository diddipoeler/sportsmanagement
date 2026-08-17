<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Sportstypes;
\defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
final class HtmlView extends BaseHtmlView
{
    public $items = []; public $pagination; public $state; public $filterForm; public $activeFilters = [];
    public function display($tpl = null)
    {
        $this->items = $this->get('Items') ?: []; $this->pagination = $this->get('Pagination'); $this->state = $this->get('State'); $this->filterForm = $this->get('FilterForm'); $this->activeFilters = $this->get('ActiveFilters') ?: [];
        if ($errors = $this->get('Errors')) { throw new \RuntimeException(implode("\n", $errors), 500); }
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_SPORTSTYPES_TITLE'), 'list'); ToolbarHelper::addNew('sportstype.add'); ToolbarHelper::editList('sportstype.edit'); ToolbarHelper::publish('sportstypes.publish', 'JTOOLBAR_PUBLISH', true); ToolbarHelper::unpublish('sportstypes.unpublish', 'JTOOLBAR_UNPUBLISH', true); ToolbarHelper::checkin('sportstypes.checkin'); ToolbarHelper::trash('sportstypes.trash'); ToolbarHelper::custom('sportstype.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false); ToolbarHelper::archiveList('sportstype.export', Text::_('JTOOLBAR_EXPORT'));
        parent::display($tpl);
    }
}
