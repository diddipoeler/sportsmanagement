<?php
/**
 * @copyright	Copyright (C) 2013 fussballineuropa.de. All rights reserved.
 * @license		GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the Joomleague component
 *
 * @static
 * @package	Sportsmanagement
 * @since	0.1
 */
class sportsmanagementViewProjectReferee extends JView
{

	function display($tpl=null)
	{
		$mainframe	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$db	 		= JFactory::getDBO();
		$uri		= JFactory::getURI();
		$user		= JFactory::getUser();
		$model		= $this->getModel();
        $this->assign('show_debug_info', JComponentHelper::getParams($option)->get('show_debug_info',0) );

		$lists=array();
        
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
        
        $this->project_id	= $this->item->project_id;
        $mdlProject = JModel::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
        $this->assignRef('project',$project);
        
        $person_id	= $this->item->person_id;;
        $mdlPerson = JModel::getInstance("Person", "sportsmanagementModel");
	    $project_person = $mdlPerson->getPerson($person_id);
        $this->assignRef('project_person',$project_person);
        
        //build the html select list for positions
		//$refereepositions = array();
		$refereepositions[] = JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_REF_POS'));
        $mdlPositions = JModel::getInstance("Positions", "sportsmanagementModel");
	    $project_ref_positions = $mdlPositions->getRefereePositions($this->project_id);
        
        
        
        if ( $this->show_debug_info )
        {
            $mainframe->enqueueMessage(JText::_('sportsmanagementViewProjectReferee project_ref_positions<br><pre>'.print_r($project_ref_positions,true).'</pre>'),'');
        }
        
        
        $refereepositions = array_merge($refereepositions,$project_ref_positions);
        $lists['refereepositions'] = JHTML::_(	'select.genericlist',
												$refereepositions,
												'project_position_id',
												'class="inputbox" size="1"',
												'value',
												'text',$this->item->project_position_id);
		unset($refereepositions);
  
		$this->assignRef('lists',			$lists);
		//$this->assignRef('projectreferee',	$item);
		$extended = sportsmanagementHelper::getExtended($item->extended, 'projectreferee');		
		$this->assignRef( 'extended', $extended );
        $this->assign('cfg_which_media_tool', JComponentHelper::getParams($option)->get('cfg_which_media_tool',0) );
		
		$this->addToolbar();		
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
        JRequest::setVar('pid', $this->item->project_id);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;
		$canDo = sportsmanagementHelper::getActions($this->item->id);
		JToolBarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_PROJECTREFEREE_NEW') : JText::_('COM_SPORTSMANAGEMENT_PROJECTREFEREE_EDIT'), 'helloworld');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('projectreferee.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('projectreferee.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('projectreferee.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('projectreferee.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('projectreferee.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('projectreferee.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('projectreferee.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::custom('projectreferee.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('projectreferee.cancel', 'JTOOLBAR_CLOSE');
		}
    sportsmanagementHelper::ToolbarButtonOnlineHelp();    
        
        /*
        JToolBarHelper::title(JText::_('Edit project depending referee data'),'Referees');

		// Set toolbar items for the page
		$edit=JRequest::getVar('edit',true);
		$text=!$edit ? JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NEW') : JText::_('COM_SPORTSMANAGEMENT_GLOBAL_EDIT');
		
        
        JToolBarHelper::save('projectreferee.save');

		if (!$edit)
		{
			JToolBarHelper::cancel('projectreferee.cancel');
		}
		else
		{
			// for existing items the button is renamed `close` and the apply button is showed
			JToolBarHelper::apply('projectreferee.apply');
			JToolBarHelper::cancel('projectreferee.cancel','COM_SPORTSMANAGEMENT_GLOBAL_CLOSE');
		}
		//JLToolBarHelper::onlinehelp();
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