<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Seasons;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SeasonsModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator list view for seasons. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;
    public $filterForm;
    public array $activeFilters = [];

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof SeasonsModel) {
            throw new \RuntimeException('Seasons model could not be loaded.', 500);
        }

        $this->items = $model->getItems() ?: [];
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();
        $this->filterForm = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters() ?: [];

        if ($errors = $model->getErrors()) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_SEASONS_TITLE'), 'list');
        ToolbarHelper::addNew('season.add');
        ToolbarHelper::editList('season.edit');
        ToolbarHelper::publish('seasons.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('seasons.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('seasons.checkin');
        ToolbarHelper::trash('seasons.trash');
    }
}
