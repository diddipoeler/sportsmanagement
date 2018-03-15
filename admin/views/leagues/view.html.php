<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage leagues
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewLeagues
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewLeagues extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewLeagues::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
	
        $inputappend = '';
        $startmemory = memory_get_usage();
        $starttime = microtime(); 
        
        if ( JComponentHelper::getParams($this->option)->get('show_debug_info_backend') )
        {
        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }

		$this->table = JTable::getInstance('league', 'sportsmanagementTable');
        
        //build the html options for nation
		$nation[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ( $res = JSMCountries::getCountryOptions() )
        {
            $nation = array_merge($nation,$res);
            $this->search_nation = $res;
        }
		
        $lists['nation'] = $nation;
        $lists['nation2'] = JHtmlSelect::genericlist($nation,
					'filter_search_nation',
					$inputappend.'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
					'value',
					'text',
					$this->state->get('filter.search_nation'));

		unset($nation);
        $nation[] = JHtml::_('select.option', '0' ,JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_ASSOCIATION'));
        $mdlassociation = JModelLegacy::getInstance('jlextassociations', 'sportsmanagementModel');
        if ( $res = $mdlassociation->getAssociations($this->state->get('filter.federation')) )
        {
            $nation = array_merge($nation, $res);
            $this->search_association = $res;
        }
        
        $lists['association'] = array();
        foreach( $res as $row)
        {
            if (array_key_exists($row->country, $lists['association'] )) 
            {
            $lists['association'][$row->country][] = $row;
            }
            else
            {
            $lists['association'][$row->country][] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_ASSOCIATION'));
            $lists['association'][$row->country][] = $row;    
            }
        }
        
        $lists['association2'] = JHtmlSelect::genericlist($nation,
					'filter_search_association',
					$inputappend.'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
					'value',
					'text',
					$this->state->get('filter.search_association'));
        
        unset($myoptions);
        
        $myoptions[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'));
        $mdlagegroup = JModelLegacy::getInstance('agegroups', 'sportsmanagementModel');
        if ( $res = $mdlagegroup->getAgeGroups() )
        {
            $myoptions = array_merge($myoptions, $res);
            $this->search_agegroup	= $res;
        }
        $lists['agegroup'] = $myoptions;
        $lists['agegroup2'] = JHtmlSelect::genericlist($myoptions,
					'filter_search_agegroup',
					'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
					'value',
					'text',
					$this->state->get('filter.search_agegroup'));
        unset($myoptions);

$mdlassociation = JModelLegacy::getInstance('jlextassociations', 'sportsmanagementModel');
        
        if ( $res = $mdlassociation->getAssociations() )
        {
            $nation = array_merge($nation, $res);
            $this->federation = $res;
        }
		
		$this->lists = $lists;
		        
        if ( JComponentHelper::getParams($this->option)->get('show_debug_info_backend') )
        {
        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' speicherbelegung<br><pre>'.print_r(sportsmanagementModeldatabasetool::getMemory($startmemory, memory_get_usage()),true).'</pre>'),'Notice');
        }
        
        
		
	}
	
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
        // Set toolbar items for the page
		$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUES_TITLE');
        JToolbarHelper::apply('leagues.saveshort');
		JToolbarHelper::addNew('league.add');
		JToolbarHelper::editList('league.edit');
		JToolbarHelper::custom('league.import', 'upload', 'upload', JText::_('JTOOLBAR_UPLOAD'), false);
		JToolbarHelper::archiveList('league.export', JText::_('JTOOLBAR_EXPORT'));
                
        parent::addToolbar();
	}
}
?>
