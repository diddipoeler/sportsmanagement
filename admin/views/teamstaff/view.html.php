<?php
/**
 * @copyright	Copyright (C) 2013 fussballineuropa.de. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * HTML View class for the Sportsmanagement Component
 *
 * @static
 * @author	diddipoeler
 * @package	Sportsmanagement
 * @since	0.1
 */
class sportsmanagementViewTeamStaff extends JView
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
        
        //$project_id	= JRequest::getVar('pid');
        //$this->team_id	= JRequest::getVar('team_id');
        $this->team_id	= $mainframe->getUserState( "$option.team_id", '0' );
        
        //$this->project_id	= sportsmanagementHelper::getTeamplayerProject($this->item->projectteam_id);
        $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
        
        $mdlProject = JModel::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
        $this->assignRef('project',$project);
        
        $project_team = $mdlProject->getProjectTeam($this->item->projectteam_id);
        $this->assignRef('project_team',$project_team);
        
        $person_id	= $this->item->person_id;;
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
		$projectpositions[] = JHTML::_('select.option',	'0', JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION' ) );
        $mdlPositions = JModel::getInstance("Positions", "sportsmanagementModel");
	    $project_ref_positions = $mdlPositions->getStaffPositions($this->project_id);
        $projectpositions = array_merge( $projectpositions, $project_ref_positions );
        $lists['projectpositions'] = JHTML::_(	'select.genericlist',
												$projectpositions,
												'project_position_id',
												'class="inputbox" size="1"',
												'value',
												'text', $this->item->project_position_id );
		unset($projectpositions);
        
       

	
		

		
        
        $extended = sportsmanagementHelper::getExtended($item->extended, 'teamstaff');
		$this->assignRef( 'extended', $extended );
        $this->assignRef( 'lists', $lists );
        //$this->assign('cfg_which_media_tool', JComponentHelper::getParams($option)->get('cfg_which_media_tool',0) );
        
        if ( $show_debug_info )
        {
            $mainframe->enqueueMessage(JText::_('sportsmanagementViewTeamStaff project_ref_positions<br><pre>'.print_r($project_ref_positions,true).'</pre>'),'');
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
	 * @since	1.7
	 */
	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu', true);
        JRequest::setVar('project_team_id', $this->item->projectteam_id);
        JRequest::setVar('pid', $this->project_id);
        JRequest::setVar('team_id', $this->team_id);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;
		$canDo = sportsmanagementHelper::getActions($this->item->id);
		JToolBarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_TEAMSTAFF_NEW') : JText::_('COM_SPORTSMANAGEMENT_TEAMSTAFF_EDIT'), 'helloworld');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('teamstaff.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('teamstaff.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('teamstaff.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('teamstaff.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('teamstaff.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('teamstaff.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('teamstaff.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::custom('teamstaff.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('teamstaff.cancel', 'JTOOLBAR_CLOSE');
		}
        
        /*
        // Set toolbar items for the page
		$edit = JRequest::getVar( 'edit', true );
		$option = JRequest::getCmd('option');
		$params = JComponentHelper::getParams( $option );
		$default_name_format = $params->get("name_format");
		$name = sportsmanagementHelper::formatName(null, $this->project_teamstaff->firstname, $this->project_teamstaff->nickname, $this->project_teamstaff->lastname, $default_name_format);
		$text = !$edit ? JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_NEW' ) : JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TEAMSTAFF_TITLE' ). ': ' . $name;
		JToolBarHelper::title( $text);
		JLToolBarHelper::save('teamstaff.save');
			
		if ( !$edit )
		{
			JLToolBarHelper::cancel('teamstaff.cancel');
		}
		else
		{
			// for existing items the button is renamed `close` and the apply button is showed
			JLToolBarHelper::apply('teamstaff.apply');
			JLToolBarHelper::cancel( 'teamstaff.cancel', 'COM_SPORTSMANAGEMENT_GLOBAL_CLOSE' );
		}
		JToolBarHelper::back();
		JToolBarHelper::help( 'screen.joomleague', true );
        */
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