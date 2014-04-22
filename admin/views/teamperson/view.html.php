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
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );


/**
 * sportsmanagementViewTeamPerson
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewTeamPerson extends JView
{

	function display( $tpl = null )
	{
		$mainframe	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$uri		= JFactory::getURI();
		$user		= JFactory::getUser();
		$model		= $this->getModel();
		$lists		= array();
        $show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        
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

        //$this->team_id	= JRequest::getVar('team_id');
        $this->team_id	= $mainframe->getUserState( "$option.team_id", '0' );
        $this->_persontype = $mainframe->getUserState( "$option.persontype", '0' );
        
        //$this->project_id	= sportsmanagementHelper::getTeamplayerProject($this->item->projectteam_id);
        $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
        
        $mdlProject = JModel::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
        $this->assignRef('project',$project);
        
        $project_team = $mdlProject->getProjectTeam($this->item->projectteam_id);
        $this->assignRef('project_team',$project_team);
        
        $person_id	= $this->item->person_id;
        $mdlPerson = JModel::getInstance("Person", "sportsmanagementModel");
	    $project_person = $mdlPerson->getPerson($person_id);
        $this->assignRef('project_person',$project_person);
        
        // personendaten setzen
        $this->form->setValue('injury',null,$project_person->injury);
        $this->form->setValue('injury_date',null,$project_person->injury_date);
        $this->form->setValue('injury_end',null,$project_person->injury_end);
        $this->form->setValue('injury_detail',null,$project_person->injury_detail);
        $this->form->setValue('injury_date_start',null,$project_person->injury_date_start);
        $this->form->setValue('injury_date_end',null,$project_person->injury_date_end);
        
        $this->form->setValue('suspension',null,$project_person->suspension);
        $this->form->setValue('suspension_date',null,$project_person->suspension_date);
        $this->form->setValue('suspension_end',null,$project_person->suspension_end);
        $this->form->setValue('suspension_detail',null,$project_person->suspension_detail);
        $this->form->setValue('susp_date_start',null,$project_person->susp_date_start);
        $this->form->setValue('susp_date_end',null,$project_person->susp_date_end);
        
        $this->form->setValue('away',null,$project_person->away);
		$this->form->setValue('away_date',null,$project_person->away_date);
        $this->form->setValue('away_end',null,$project_person->away_end);
        $this->form->setValue('away_detail',null,$project_person->away_detail);
        $this->form->setValue('away_date_start',null,$project_person->away_date_start);
        $this->form->setValue('away_date_end',null,$project_person->away_date_end);
        
        //$matchdays = sportsmanagementHelper::getRoundsOptions($this->project_id, 'ASC', false);
        
        $projectpositions = array();
		$projectpositions[] = JHtml::_('select.option',	'0', JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION' ) );
        $mdlPositions = JModel::getInstance("Positions", "sportsmanagementModel");
	    $project_ref_positions = $mdlPositions->getPlayerPositions($this->project_id);
        if ( $project_ref_positions )
        {
        $projectpositions = array_merge( $projectpositions, $project_ref_positions );
        }
        $lists['projectpositions'] = JHtml::_(	'select.genericlist',
												$projectpositions,
												'project_position_id',
												'class="inputbox" size="1"',
												'value',
												'text', $this->item->project_position_id );
		unset($projectpositions);
        



        $extended = sportsmanagementHelper::getExtended($item->extended, 'teamplayer');
		$this->assignRef( 'extended', $extended );
        $this->assignRef( 'lists', $lists );
        //$this->assign('cfg_which_media_tool', JComponentHelper::getParams($option)->get('cfg_which_media_tool',0) );
        
        if ( $show_debug_info )
        {
            $mainframe->enqueueMessage(JText::_('sportsmanagementViewTeamPlayer project_id<br><pre>'.print_r($this->project_id,true).'</pre>'),'');
            $mainframe->enqueueMessage(JText::_('sportsmanagementViewTeamPlayer project<br><pre>'.print_r($this->project,true).'</pre>'),'');
            $mainframe->enqueueMessage(JText::_('sportsmanagementViewTeamPlayer project_ref_positions<br><pre>'.print_r($project_ref_positions,true).'</pre>'),'');
        }
 
		// Set the toolbar
		$this->addToolBar();
 
		// Display the template
		parent::display($tpl);
 
		// Set the document
		$this->setDocument();
        
        
 
	}
    
    /**
	* Add the page title and toolbar.
	*
	* @since	1.6
	*/
	protected function addToolbar()
	{
	   $mainframe	= JFactory::getApplication();
	   // Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
	   
		JRequest::setVar('hidemainmenu', true);
        JRequest::setVar('project_team_id', $this->item->projectteam_id);
        JRequest::setVar('pid', $this->project_id);
        JRequest::setVar('team_id', $this->team_id);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;
		$canDo = sportsmanagementHelper::getActions($this->item->id);
        
		if ( $this->_persontype == 1 )
        {
        JToolBarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMPLAYER_NEW') : JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMPLAYER_EDIT'), 'teamplayer');
		}
        elseif ( $this->_persontype == 2 )
        {
        JToolBarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMSTAFF_NEW') : JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMSTAFF_EDIT'), 'teamstaff');
		}
        
        $mainframe->enqueueMessage(get_class($this).' '.__FUNCTION__.' _persontype<br><pre>'.print_r($this->_persontype, true).'</pre><br>','Notice');
        
        // Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('teamperson.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('teamperson.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('teamperson.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('teamperson.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('teamperson.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('teamperson.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('teamperson.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::custom('teamperson.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('teamperson.cancel', 'JTOOLBAR_CLOSE');
		}
        
        JToolBarHelper::divider();
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
		JToolBarHelper::preferences(JRequest::getCmd('option'));

	}
    
    
    
    /**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$isNew = $this->item->id == 0;
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('COM_HELLOWORLD_HELLOWORLD_CREATING') : JText::_('COM_HELLOWORLD_HELLOWORLD_EDITING'));
		$document->addScript(JURI::root() . $this->script);
		$document->addScript(JURI::root() . "/administrator/components/com_sportsmanagement/views/sportsmanagement/submitbutton.js");
		JText::script('COM_HELLOWORLD_HELLOWORLD_ERROR_UNACCEPTABLE');
	}
    

}
?>
