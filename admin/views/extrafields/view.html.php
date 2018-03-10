<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage extrafields
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewextrafields
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewextrafields extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewextrafields::init()
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
       
        $table = JTable::getInstance('club', 'sportsmanagementTable');
		$this->table	= $table;
		
	}
	
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
        // Set toolbar items for the page
		$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_EXTRAFIELDS_TITLE');
		JToolbarHelper::addNew('extrafield.add');
		JToolbarHelper::editList('extrafield.edit');
		JToolbarHelper::custom('extrafield.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
		JToolbarHelper::archiveList('extrafield.export',JText::_('JTOOLBAR_EXPORT'));
	
        parent::addToolbar();
	}
}
?>