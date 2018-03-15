<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage playgrounds
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewPlaygrounds
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewPlaygrounds extends sportsmanagementView
{

	/**
	 * sportsmanagementViewPlaygrounds::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
		
$starttime = microtime(); 
		
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
		
        
        $this->table = JTable::getInstance('playground', 'sportsmanagementTable');

        
        //build the html options for nation
		$nation[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ($res = JSMCountries::getCountryOptions())
        {
            $nation = array_merge($nation, $res);
            $this->search_nation = $res;
            }
		
        $lists['nation'] = $nation;
        $lists['nation2'] = JHtmlSelect::genericlist($nation, 
							'filter_search_nation', 
							'class="inputbox" style="width:140px; " onchange="this.form.submit();"', 
							'value', 
							'text', 
							$this->state->get('filter.search_nation'));


	
		$this->lists	= $lists;
	
        
        
		
	}

	
	/**
	 * sportsmanagementViewPlaygrounds::addToolbar()
	 * 
	 * @return void
	 */
	protected function addToolbar()
	{
		
        // Set toolbar items for the page
		$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_TITLE');
		JToolbarHelper::editList('playground.edit');
		JToolbarHelper::addNew('playground.add');
		JToolbarHelper::custom('playground.import', 'upload', 'upload', JText::_('JTOOLBAR_UPLOAD'), false);
		JToolbarHelper::archiveList('playground.export', JText::_('JTOOLBAR_EXPORT'));
		

        parent::addToolbar();
	}
}
?>