<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage agegroups
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewagegroups
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewagegroups extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewagegroups::init()
	 * 
	 * @return void
	 */
	public function init ()
	{

//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' state<br><pre>'.print_r($this->state,true).'</pre>'),'Notice');		

        $starttime = microtime(); 
        $mdlSportsType = JModelLegacy::getInstance('SportsType', 'sportsmanagementModel');
       
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }

        $this->table = JTable::getInstance('agegroup', 'sportsmanagementTable');
		
        //build the html select list for sportstypes
		$sportstypes[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER'),'id','name');
		$mdlSportsTypes = JModelLegacy::getInstance('SportsTypes', 'sportsmanagementModel');
		$allSportstypes = $mdlSportsTypes->getSportsTypes();
		$sportstypes = array_merge($sportstypes, $allSportstypes);
        $this->sports_type = $allSportstypes;
		
		$lists['sportstypes'] = JHtml::_( 'select.genericList',
							$sportstypes,
							'filter_sports_type',
							'class="inputbox" onChange="this.form.submit();" style="width:120px"',
							'id',
							'name',
							$this->state->get('filter.sports_type'));
		unset($sportstypes);
        
        //build the html options for nation
		$nation[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ($res = JSMCountries::getCountryOptions())
		{
			$nation = array_merge($nation,$res);
			$this->search_nation = $res;
		}
		
        $lists['nation'] = $nation;
        $lists['nation2'] = JHtmlSelect::genericlist(	$nation,
						'filter_search_nation',
						'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
						'value',
						'text',
						$this->state->get('filter.search_nation'));
        
        foreach ( $this->items as $item )
        {
            $sportstype = $mdlSportsType->getSportstype($item->sportstype_id);
            if ( $sportstype )
            {
            $item->sportstype = $sportstype->name;
            }
            else
            {
            $item->sportstype = NULL;    
            }
        }
        
        if ( count($this->items)  == 0 )
        {
            $databasetool = JModelLegacy::getInstance("databasetool", "sportsmanagementModel");
            $insert_agegroup = $databasetool->insertAgegroup($this->state->get('filter.search_nation'),$this->state->get('filter.sports_type'));
        $this->app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_NO_RESULT'),'Error');
        }

		$this->lists = $lists;
		
	}
	
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
        // Set toolbar items for the page
        $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_TITLE');
		JToolbarHelper::addNew('agegroup.add');
		JToolbarHelper::editList('agegroup.edit');
        JToolbarHelper::apply('agegroups.saveshort');
		JToolbarHelper::custom('agegroups.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
		JToolbarHelper::archiveList('agegroup.export',JText::_('JTOOLBAR_EXPORT'));
		
        parent::addToolbar();
	}
}
?>