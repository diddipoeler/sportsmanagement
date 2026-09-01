<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Statistics;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\StatisticsModel;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator list view for statistic definitions. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;
    public array $lists = [];

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof StatisticsModel) {
            throw new \RuntimeException('StatisticsModel could not be loaded.', 500);
        }

        $this->items = $model->getItems() ?: [];
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();
        $this->buildFilters();
        $this->addToolbar();

        parent::display($tpl);
    }

    private function buildFilters(): void
    {
        $factory = Factory::getApplication()
            ->bootComponent('com_sportsmanagement')
            ->getMVCFactory();
        $sportstypesModel = $factory->createModel('Sportstypes', 'Administrator');
        $sportstypes = [
            HTMLHelper::_ (
                'select.option',
                0,
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_EVENTS_SPORTSTYPE_FILTER'),
                'id',
                'name'
            ),
        ];

        if ($sportstypesModel && method_exists($sportstypesModel, 'getSportsTypes')) {
            $sportstypes = array_merge($sportstypes, $sportstypesModel->getSportsTypes());
        }

        $this->lists['sportstypes'] = HTMLHelper::_(
            'select.genericList',
            $sportstypes,
            'filter_sports_type',
            'class="form-select" onchange="this.form.submit()"',
            'id',
            'name',
            (int) $this->state->get('filter.sports_type')
        );
    }

    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_STATISTICS_TITLE'), 'chart');
        ToolbarHelper::addNew('statistic.add');
        ToolbarHelper::editList('statistic.edit');
        ToolbarHelper::publish('statistics.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('statistics.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('statistics.checkin');
        ToolbarHelper::trash('statistics.trash');
        ToolbarHelper::custom('statistic.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::archiveList('statistic.export', Text::_('JTOOLBAR_EXPORT'));
    }
}
