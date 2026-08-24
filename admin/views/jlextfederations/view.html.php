<?php
/**
 * SportsManagement administrator federations list view.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewjlextfederations extends sportsmanagementView
{
    public function init()
    {
        $nation = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY')),
        ];

        if ($res = JSMCountries::getCountryOptions()) {
            $nation = array_merge($nation, $res);
            $this->search_nation = $res;
        }

        $this->lists = [
            'nation' => $nation,
            'nation2' => HTMLHelper::_(
                'select.genericlist',
                $nation,
                'filter_search_nation',
                'class="inputbox" style="width:140px;" onchange="this.form.submit();"',
                'value',
                'text',
                $this->state->get('filter.search_nation')
            ),
        ];
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_FEDERATIONS_TITLE');
        $this->icon = 'federations';

        ToolbarHelper::addNew('jlextfederation.add');
        ToolbarHelper::editList('jlextfederation.edit');
        ToolbarHelper::custom('jlextfederation.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::archiveList('jlextfederation.export', Text::_('JTOOLBAR_EXPORT'));

        parent::addToolbar();
    }
}
