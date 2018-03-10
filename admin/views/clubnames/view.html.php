<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage clubnames
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * sportsmanagementViewClubnames
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementViewClubnames extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewClubnames::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
        $lists = array();
        $starttime = microtime(); 
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
        //build the html options for nation
		$nation[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ( $res = JSMCountries::getCountryOptions() )
        {
            $nation = array_merge($nation,$res);
            $this->search_nation	= $res;
        }
		
        $lists['nation'] = $nation;
        $this->table = JTable::getInstance('clubname', 'sportsmanagementTable');
		$this->lists	= $lists;
		
	}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.7
	 */
	protected function addToolbar()
	{
        // Set toolbar items for the page
		$this->title =  JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_CLUBNAMES_TITLE' );
        
        JToolbarHelper::publish('clubnames.publish', 'JTOOLBAR_PUBLISH', true);
		JToolbarHelper::unpublish('clubnames.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        JToolbarHelper::checkin('clubnames.checkin');
        JToolbarHelper::custom('clubnames.import', 'upload', 'upload', JText::_('JTOOLBAR_INSTALL'), false);
		JToolbarHelper::divider();
		JToolbarHelper::addNew('clubname.add');
		JToolbarHelper::editList('clubname.edit');

        parent::addToolbar();
	}
}
?>