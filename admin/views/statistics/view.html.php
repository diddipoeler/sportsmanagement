<?php
/**
 * SportsManagement administrator statistics list view.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\StatisticTable;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewStatistics extends sportsmanagementView
{
    public function init()
    {
        $factory = $this->app->bootComponent('com_sportsmanagement')->getMVCFactory();
        $this->table = new StatisticTable($this->model->getDatabase());

        $sportstypes = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_EVENTS_SPORTSTYPE_FILTER'), 'id', 'name'),
        ];
        $sportsTypesModel = $factory->createModel('Sportstypes', 'Administrator');
        $allSportstypes = $sportsTypesModel ? $sportsTypesModel->getSportsTypes() : [];
        $sportstypes = array_merge($sportstypes, $allSportstypes);

        $this->lists = [
            'sportstypes' => HTMLHelper::_(
                'select.genericList',
                $sportstypes,
                'filter_sports_type',
                'class="inputbox" onChange="this.form.submit();" style="width:120px"',
                'id',
                'name',
                $this->state->get('filter.sports_type')
            ),
        ];
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_STATISTICS_TITLE');
        ToolbarHelper::publishList();
        ToolbarHelper::unpublishList();
        ToolbarHelper::divider();
        ToolbarHelper::editList('statistic.edit');
        ToolbarHelper::addNew('statistic.add');
        ToolbarHelper::custom('statistic.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::archiveList('statistic.export', Text::_('JTOOLBAR_EXPORT'));
        parent::addToolbar();
    }
}
