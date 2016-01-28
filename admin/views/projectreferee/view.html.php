<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
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
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db	 		= sportsmanagementHelper::getDBConnection();
		$uri		= JFactory::getURI();
		$user		= JFactory::getUser();
		$model		= $this->getModel();
        $this->show_debug_info	= JComponentHelper::getParams($option)->get('show_debug_info', 0);

		$lists = array();
        
        // get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
		$script = $this->get('Script');
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
                      
		// Assign the Data
		$this->form = $form;
		$this->item = $item;
		$this->script = $script;
        
        $this->_persontype = $jinput->get('persontype');
        if ( empty($this->_persontype) )
        {
            $this->_persontype	= $app->getUserState( "$option.persontype", '0' );
        }
        
        $this->project_id	= $this->item->project_id;
        $mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
        $this->project	= $project;
        
        $person_id	= $this->item->person_id;
        //$person_id	= JRequest::getVar('person_id');
        $mdlPerson = JModelLegacy::getInstance("Person", "sportsmanagementModel");
	    $project_person = $mdlPerson->getPerson(0,$person_id);
        // name für den titel setzen
        $this->item->name = $project_person->lastname.' - '.$project_person->firstname;
        
        $this->project_person	= $project_person;
                      
        
        if ( $this->show_debug_info )
        {
            $app->enqueueMessage(JText::_('sportsmanagementViewProjectReferee project_ref_positions<br><pre>'.print_r($project_ref_positions,true).'</pre>'),'');
        }
        
        
		//$this->assignRef('projectreferee',	$item);
		$extended = sportsmanagementHelper::getExtended($item->extended, 'projectreferee');		
		$this->extended	= $extended;
        //$this->assign('cfg_which_media_tool', JComponentHelper::getParams($option)->get('cfg_which_media_tool',0) );
		

	}

	
	/**
	 * sportsmanagementViewProjectReferee::addToolbar()
	 * 
	 * @return void
	 */
	protected function addToolbar()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
        
	
    	$jinput->set('hidemainmenu', true);
        
        $isNew = $this->item->id ? $this->title = JText::_('COM_SPORTSMANAGEMENT_PROJECTREFEREE_EDIT') : $this->title = JText::_('COM_SPORTSMANAGEMENT_PROJECTREFEREE_NEW');
        $this->icon = 'projectreferee';

        $app->setUserState( "$option.pid", $this->item->project_id );
        $app->setUserState( "$option.persontype", $this->_persontype );	
        
	
        
        parent::addToolbar();
        
	}
    
   
    	
	
}
?>
