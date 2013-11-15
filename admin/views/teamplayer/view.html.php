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
 * @package	Sportsmanagement
 * @since	0.1
 */
class sportsmanagementViewTeamPlayer extends JView
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
		
        $matchdays = sportsmanagementHelper::getRoundsOptions($this->project_id, 'ASC', false);
        
        $projectpositions = array();
		$projectpositions[] = JHTML::_('select.option',	'0', JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION' ) );
        $mdlPositions = JModel::getInstance("Positions", "sportsmanagementModel");
	    $project_ref_positions = $mdlPositions->getPlayerPositions($this->project_id);
        if ( $project_ref_positions )
        {
        $projectpositions = array_merge( $projectpositions, $project_ref_positions );
        }
        $lists['projectpositions'] = JHTML::_(	'select.genericlist',
												$projectpositions,
												'project_position_id',
												'class="inputbox" size="1"',
												'value',
												'text', $this->item->project_position_id );
		unset($projectpositions);
        
        
        // injury details
		$myoptions = array();
		$myoptions[]		= JHTML::_( 'select.option', '0', JText::_( 'JNO' ) );
		$myoptions[]		= JHTML::_( 'select.option', '1', JText::_( 'JYES' ) );
		$lists['injury']	= JHTML::_( 'select.radiolist',
										$myoptions,
										'injury',
										'class="inputbox" size="1"',
										'value',
										'text',
										$this->item-->injury );
		unset($myoptions);

		$lists['injury_date']	 = JHTML::_( 'select.genericlist',
											$matchdays,
											'injury_date',
											'class="inputbox" size="1"',
											'value',
											'text',
											$this->item-->injury_date );
		$lists['injury_end']	= JHTML::_( 'select.genericlist',
											$matchdays,
											'injury_end',
											'class="inputbox" size="1"',
											'value',
											'text',
											$this->item-->injury_end );
/*
		// suspension details
		$myoptions		= array();
		$myoptions[]	= JHTML::_('select.option', '0', JText::_( 'JNO' ) );
		$myoptions[]	= JHTML::_('select.option', '1', JText::_( 'JYES' ));
		$lists['suspension']		= JHTML::_( 'select.radiolist',
												$myoptions,
												'suspension',
												'class="radio" size="1"',
												'value',
												'text',
												$this->item-->suspension );
		unset($myoptions);
*/
		$lists['suspension_date']	 = JHTML::_( 'select.genericlist',
												$matchdays,
												'suspension_date',
												'class="inputbox" size="1"',
												'value',
												'text',
												$this->item-->suspension_date );
		$lists['suspension_end']	= JHTML::_( 'select.genericlist',
												$matchdays,
												'suspension_end',
												'class="inputbox" size="1"',
												'value',
												'text',
												$this->item-->suspension_end );
/*
		// away details
		$myoptions		= array();
		$myoptions[]	= JHTML::_( 'select.option', '0', JText::_( 'JNO' ) );
		$myoptions[]	= JHTML::_( 'select.option', '1', JText::_( 'JYES' ) );
		$lists['away']	= JHTML::_( 'select.radiolist',
									$myoptions,
									'away',
									'class="inputbox" size="1"',
									'value',
									'text',
									$this->item-->away );
		unset($myoptions);

		$lists['away_date'] = JHTML::_( 'select.genericlist',
										$matchdays,
										'away_date',
										'class="inputbox" size="1"',
										'value',
										'text',
										$this->item-->away_date );
		$lists['away_end']	= JHTML::_( 'select.genericlist',
										$matchdays,
										'away_end',
										'class="inputbox" size="1"',
										'value',
										'text',
										$this->item-->away_end );
        */
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
	   
		JRequest::setVar('hidemainmenu', true);
        JRequest::setVar('project_team_id', $this->item->projectteam_id);
        JRequest::setVar('pid', $this->project_id);
        JRequest::setVar('team_id', $this->team_id);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;
		$canDo = sportsmanagementHelper::getActions($this->item->id);
		JToolBarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_TEAMPLAYER_NEW') : JText::_('COM_SPORTSMANAGEMENT_TEAMPLAYER_EDIT'), 'helloworld');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('teamplayer.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('teamplayer.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('teamplayer.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('teamplayer.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('teamplayer.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('teamplayer.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('teamplayer.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::custom('teamplayer.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('teamplayer.cancel', 'JTOOLBAR_CLOSE');
		}

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
