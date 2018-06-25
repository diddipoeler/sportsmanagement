<?php 
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage hitlist
 */
 
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );

/**
 * sportsmanagementViewhitlist
 * 
 * @package 
 * @author diddi
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class sportsmanagementViewhitlist extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewhitlist::init()
	 * 
	 * @return void
	 */
	function init( )
	{
        $model = $this->getModel();
        
        $this->tableclass = $this->jinput->getVar('table_class', 'table','request','string');
        $this->show_project = $this->jinput->getVar('show_project', 'table','request','string');
        $this->show_club = $this->jinput->getVar('show_club', 'table','request','string');
        $this->show_team = $this->jinput->getVar('show_team', 'table','request','string');
        $this->show_person = $this->jinput->getVar('show_person', 'table','request','string');
        $this->show_playground = $this->jinput->getVar('show_playground', 'table','request','string');
        $this->max_hits = $this->jinput->getVar('max_hits', 'table','request','string');
        
        if ( $this->show_project )
        {
            $items = $model->getSportsmanagementHits(NULL,$this->max_hits,'project');
        }
        if ( $this->show_club )
        {
            $items = $model->getSportsmanagementHits(NULL,$this->max_hits,'club');
        }
        if ( $this->show_team )
        {
            $items = $model->getSportsmanagementHits(NULL,$this->max_hits,'team');
        }
        if ( $this->show_person )
        {
            $items = $model->getSportsmanagementHits(NULL,$this->max_hits,'person');
        }
        
        if ( $this->show_playground )
        {
            $items = $model->getSportsmanagementHits(NULL,$this->max_hits,'playground');
        }
       
        $this->model_hits  = $model::$_success_text;
	
	}

}
?>