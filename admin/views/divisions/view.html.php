<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage divisions
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * sportsmanagementViewDivisions
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewDivisions extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewDivisions::init()
	 * 
	 * @return void
	 */
	public function init ()
	{

        $lists = array();
        $this->project_id = $this->app->getUserState( "$this->option.pid", '0' );
        $mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
        $starttime = microtime(); 

        $this->table = JTable::getInstance('division', 'sportsmanagementTable');
        $this->projectws = $project;
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
		$this->title =  JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DIVS_TITLE' );
        
        JToolbarHelper::publish('divisions.publish', 'JTOOLBAR_PUBLISH', true);
		JToolbarHelper::unpublish('divisions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        JToolbarHelper::checkin('divisions.checkin');
        JToolbarHelper::apply('divisions.saveshort');
		JToolbarHelper::divider();
		JToolbarHelper::addNew('division.add');
		JToolbarHelper::editList('division.edit');

        parent::addToolbar();
	}
}
?>
