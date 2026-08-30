<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage agegroups
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\AgegroupTable;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewagegroups
 */
class sportsmanagementViewagegroups extends sportsmanagementView
{
    public function init()
    {
        $factory = $this->app->bootComponent('com_sportsmanagement')->getMVCFactory();
        $this->table = new AgegroupTable($this->model->getDatabase());

        /** Build the html select list for sportstypes */
        $sportstypes = [
            HTMLHelper::_(
                'select.option',
                '0',
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER'),
                'id',
                'name'
            ),
        ];
        $sportsTypesModel = $factory->createModel('Sportstypes', 'Administrator');
        $allSportstypes = $sportsTypesModel ? $sportsTypesModel->getSportsTypes() : [];
        $sportstypes = array_merge($sportstypes, $allSportstypes);
        $this->sports_type = $allSportstypes;

        $lists['sportstypes'] = HTMLHelper::_(
            'select.genericList',
            $sportstypes,
            'filter_sports_type',
            'class="inputbox" onChange="this.form.submit();" style="width:120px"',
            'id',
            'name',
            $this->state->get('filter.sports_type')
        );

        /** Build the html options for nation */
        $nation = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY')),
        ];

        if ($res = JSMCountries::getCountryOptions()) {
            $nation = array_merge($nation, $res);
            $this->search_nation = $res;
        }

        $lists['nation'] = $nation;
        $lists['nation2'] = HTMLHelper::_(
            'select.genericList',
            $nation,
            'filter_search_nation',
            'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
            'value',
            'text',
            $this->state->get('filter.search_nation')
        );

        $sportstypeNames = [];
        foreach ($allSportstypes as $sportstype) {
            $sportstypeNames[(int) $sportstype->id] = (string) $sportstype->name;
        }

        foreach ($this->items as $item) {
            $item->sportstype = $sportstypeNames[(int) $item->sportstype_id] ?? null;
        }

        if (!$this->items) {
            $databaseTool = $factory->createModel('Databasetool', 'Administrator');

            if ($databaseTool) {
                $databaseTool->insertAgegroup(
                    $this->state->get('filter.search_nation'),
                    $this->state->get('filter.sports_type')
                );
            }

            $this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_NO_RESULT'), 'error');
        }

        $this->lists = $lists;
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_TITLE');
        ToolbarHelper::addNew('agegroup.add');
        ToolbarHelper::editList('agegroup.edit');
        ToolbarHelper::apply('agegroups.saveshort');
        ToolbarHelper::custom('agegroups.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::archiveList('agegroup.export', Text::_('JTOOLBAR_EXPORT'));
        parent::addToolbar();
    }
}
