<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
* @version   1.0.05
* @file      view.html.php
* @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license   This file is part of SportsManagement.
* @package   sportsmanagement
* @subpackage teams
*/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');

/**
 * sportsmanagementViewTeams
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewTeams extends sportsmanagementView {

    /**
     * sportsmanagementViewTeams::init()
     * 
     * @return void
     */
    public function init() {

        $starttime = microtime();

        if (COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO) {
            $app->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' Ausfuehrungszeit query<br><pre>' . print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()), true) . '</pre>'), 'Notice');
        }

        $this->table = JTable::getInstance('team', 'sportsmanagementTable');

        //build the html select list for sportstypes
        $sportstypes[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER'), 'id', 'name');
        $mdlSportsTypes = JModelLegacy::getInstance('SportsTypes', 'sportsmanagementModel');
        $allSportstypes = $mdlSportsTypes->getSportsTypes();
        $sportstypes = array_merge($sportstypes, $allSportstypes);

        $this->sports_type = $allSportstypes;
        $lists['sportstype'] = $sportstypes;
        $lists['sportstypes'] = JHtml::_('select.genericList', $sportstypes, 'filter_sports_type', 'class="inputbox" onChange="this.form.submit();" style="width:120px"', 'id', 'name', $this->state->get('filter.sports_type'));
        unset($sportstypes);

        //build the html options for nation
        $nation[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
        if ($res = JSMCountries::getCountryOptions()) {
            $nation = array_merge($nation, $res);
            //$this->assignRef('search_nation', $res);
            $this->search_nation = $res;
        }

        $lists['nation'] = $nation;
        $lists['nation2'] = JHtmlSelect::genericlist($nation, 'filter_search_nation', 'class="inputbox" style="width:140px; " onchange="this.form.submit();"', 'value', 'text', $this->state->get('filter.search_nation'));

        $myoptions = array();
        $myoptions[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'));
        $mdlagegroup = JModelLegacy::getInstance('agegroups', 'sportsmanagementModel');

        if ($res = $mdlagegroup->getAgeGroups()) {
            $myoptions = array_merge($myoptions, $res);
        }

        $lists['agegroup'] = $myoptions;
        unset($myoptions);

        $this->club_id = $this->jinput->get->get('club_id');
        //$this->jinput->set('club_id', $this->club_id);
        //$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_id<br><pre>'.print_r($this->club_id,true).'</pre>'),'');
        $this->lists = $lists;
    }

    /**
     * Add the page title and toolbar.
     *
     * @since	1.7
     */
    protected function addToolbar() {

        // Set toolbar items for the page
        $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_TITLE');
        $this->icon = 'teams';
        JToolbarHelper::apply('teams.saveshort');
        JToolbarHelper::addNew('team.add');
        JToolbarHelper::editList('team.edit');
        JToolbarHelper::custom('team.copysave', 'copy.png', 'copy_f2.png', JText::_('JTOOLBAR_DUPLICATE'), true);
        JToolbarHelper::custom('team.import', 'upload', 'upload', JText::_('JTOOLBAR_UPLOAD'), false);
        JToolbarHelper::archiveList('team.export', JText::_('JTOOLBAR_EXPORT'));

        if ($this->jinput->get->get('club_id')) {
            JToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=clubs');
        }

        parent::addToolbar();
    }

}

?>
