<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage referees
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * sportsmanagementViewReferees
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewReferees extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewReferees::init()
	 * 
	 * @return void
	 */
	function init()
	{
		
		if ( !$this->config  )
		{
			$this->config = sportsmanagementModelProject::getTemplateConfig( 'players',$this->jinput->getInt('cfg_which_database',0));
		}

		$this->rows = $this->model->getReferees();

		// Set page title
		$pagetitle=JText::_( 'COM_SPORTSMANAGEMENT_REFEREES_PAGE_TITLE' );
		$this->document->setTitle( JText::sprintf( $pagetitle, $this->project->name ) );
        
        $this->headertitle = JText::_( 'COM_SPORTSMANAGEMENT_REFEREES_TITLE' );

	}

}
?>