<?php
/**
 * SportsManagement administrator club names list view.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\ClubnameTable;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewClubnames extends sportsmanagementView
{
    public function init()
    {
        $lists = [];
        $nation = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY')),
        ];

        if ($res = JSMCountries::getCountryOptions()) {
            $nation = array_merge($nation, $res);
            $this->search_nation = $res;
        }

        $lists['nation'] = $nation;
        $this->table = new ClubnameTable($this->model->getDatabase());
        $this->lists = $lists;
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBNAMES_TITLE');
        ToolbarHelper::publish('clubnames.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('clubnames.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('clubnames.checkin');
        ToolbarHelper::custom('clubnames.import', 'upload', 'upload', Text::_('JTOOLBAR_INSTALL'), false);
        ToolbarHelper::divider();
        ToolbarHelper::addNew('clubname.add');
        ToolbarHelper::editList('clubname.edit');
        parent::addToolbar();
    }
}
