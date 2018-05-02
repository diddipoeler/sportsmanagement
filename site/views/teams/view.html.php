<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teams
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * sportsmanagementViewTeams
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewTeams extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewTeams::init()
	 * 
	 * @return void
	 */
	function init()
	{
	
		$this->division = sportsmanagementModelProject::getDivision($this->jinput->getInt( "division", 0 ),$this->jinput->getInt('cfg_which_database',0));
        $this->teams = sportsmanagementModelProject::getTeams($this->jinput->getInt( "division", 0 ),'name',$this->jinput->getInt('cfg_which_database',0));

		// Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_TEAMS_TITLE' );
		if ( isset( $this->project ) )
		{
			$pageTitle .= " " . $this->project->name;
			if ( isset( $this->division ) )
			{
				$pageTitle .= " : ". $this->division->name;
			}
		}
		$this->document->setTitle( $pageTitle );
        
        $this->headertitle = JText::_( 'COM_SPORTSMANAGEMENT_TEAMS_TITLE' );

	}
}
?>