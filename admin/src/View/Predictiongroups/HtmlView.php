<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictiongroups;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator list view for prediction groups. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;
    public $filterForm;
    public array $activeFilters = [];
    public $user;
    public string $sortDirection = 'ASC';
    public string $sortColumn = 's.name';

    public function display($tpl = null)
    {
        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters') ?: [];
        $this->user = Factory::getApplication()->getIdentity();
        $this->sortDirection = (string) $this->state->get('list.direction', 'ASC');
        $this->sortColumn = (string) $this->state->get('list.ordering', 's.name');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->items) {
            Factory::getApplication()->enqueueMessage(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_GROUPS'),
                'warning'
            );
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONGROUPS_TITLE'), 'users');
        ToolbarHelper::addNew('predictiongroup.add');
        ToolbarHelper::editList('predictiongroup.edit');
        ToolbarHelper::deleteList('', 'predictiongroups.delete', 'JTOOLBAR_DELETE');
        ToolbarHelper::checkin('predictiongroups.checkin');
    }
}
