<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage clubs
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * sportsmanagementViewClubs
 * 
 * @package 
 * @author abcde
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class sportsmanagementViewClubs extends sportsmanagementView
{

    /**
     * sportsmanagementViewClubs::init()
     * 
     * @return void
     */
    function init()
	{

		$division = sportsmanagementModelProject::getDivision(sportsmanagementModelClubs::$divisionid,sportsmanagementModelClubs::$cfg_which_database);
		$clubs = $this->model->getClubs();

		$this->division = $division;
		$this->clubs = $clubs;

		// Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_CLUBS_PAGE_TITLE' );
		if ( isset( $this->project ) )
		{
			$pageTitle .= ' - ' . $this->project->name;
			if ( isset( $this->division ) )
			{
				$pageTitle .= ' : ' . $this->division->name;
			}
		}
		$this->document->setTitle( $pageTitle );
        $this->headertitle = $pageTitle;

	}
}
?>