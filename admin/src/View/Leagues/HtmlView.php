<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Leagues;

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
    public $inlineOptions = [];

    public function display($tpl = null)
    {
        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters') ?: [];
        $this->inlineOptions = $this->getModel()->getInlineOptions();

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUES_TITLE'), 'list');
        ToolbarHelper::apply('leagues.saveshort');
        ToolbarHelper::addNew('league.add');
        ToolbarHelper::editList('league.edit');
        ToolbarHelper::publish('leagues.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('leagues.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('leagues.checkin');
        ToolbarHelper::trash('leagues.trash');
        ToolbarHelper::custom('league.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::archiveList('league.export', Text::_('JTOOLBAR_EXPORT'));

        parent::display($tpl);
    }
}
