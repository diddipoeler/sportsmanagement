<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage projectreferee
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewProjectReferee
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewProjectReferee extends sportsmanagementView
{

	
	/**
	 * sportsmanagementViewProjectReferee::init()
	 * 
	 * @return
	 */
	public function init ()
	{
        $this->show_debug_info	= JComponentHelper::getParams($this->option)->get('show_debug_info', 0);
		$lists = array();
 
/**
 * Check for errors.
 */
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
        
        $this->_persontype = $this->jinput->get('persontype');
        if ( empty($this->_persontype) )
        {
            $this->_persontype	= $this->app->getUserState( "$this->option.persontype", '0' );
        }
        
        $this->project_id	= $this->item->project_id;
        $mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
        $this->project	= $project;
        
        $person_id	= $this->item->person_id;
        $mdlPerson = JModelLegacy::getInstance("Person", "sportsmanagementModel");
	    $project_person = $mdlPerson->getPerson(0,$person_id);
/**
 * name für den titel setzen
 */
        $this->item->name = $project_person->lastname.' - '.$project_person->firstname;
        
        $this->project_person	= $project_person;
                      
        
        if ( $this->show_debug_info )
        {
            $this->app->enqueueMessage(JText::_('sportsmanagementViewProjectReferee project_ref_positions<br><pre>'.print_r($project_ref_positions,true).'</pre>'),'');
        }
        
		$extended = sportsmanagementHelper::getExtended($this->item->extended, 'projectreferee');		
		$this->extended	= $extended;

	}

	
	/**
	 * sportsmanagementViewProjectReferee::addToolbar()
	 * 
	 * @return void
	 */
	protected function addToolbar()
	{
    	$this->jinput->set('hidemainmenu', true);
        $isNew = $this->item->id ? $this->title = JText::_('COM_SPORTSMANAGEMENT_PROJECTREFEREE_EDIT') : $this->title = JText::_('COM_SPORTSMANAGEMENT_PROJECTREFEREE_NEW');
        $this->icon = 'projectreferee';
        $this->app->setUserState( "$this->option.pid", $this->item->project_id );
        $this->app->setUserState( "$this->option.persontype", $this->_persontype );	
        parent::addToolbar();
	}
	
}
?>
