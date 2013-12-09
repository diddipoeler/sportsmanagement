<?php
/**
 * @copyright	Copyright (C) 2006-2013 JoomLeague.net. All rights reserved.
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
 * HTML View class for the Joomleague component
 *
 * @static
 * @package		Joomleague
 * @since 0.1
 */
class sportsmanagementViewDivision extends JView
{
	function display( $tpl = null )
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
        
        $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
        $mdlProject = JModel::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
        $this->assignRef('project',$project);
        //$this->item->project_id = $this->project_id;
        
        
        
        $this->addToolbar();
        
        $mainframe->enqueueMessage(JText::_('sportsmanagementViewDivision item<br><pre>'.print_r($this->item,true).'</pre>'),'Notice');
        		
		parent::display($tpl);
        // Set the document
		$this->setDocument();

		
	}

	function _displayForm( $tpl )
	{
		$option = JRequest::getCmd('option');

		$mainframe	= JFactory::getApplication();
		$project_id = $mainframe->getUserState( 'com_joomleagueproject' );
		$db		= JFactory::getDBO();
		$uri 	= JFactory::getURI();
		$user 	= JFactory::getUser();
		$model	= $this->getModel();

		$lists = array();
		//get the division
		$division	=& $this->get( 'data' );
		$isNew		= ( $division->id < 1 );

		// fail if checked out not by 'me'
		if ( $model->isCheckedOut( $user->get( 'id' ) ) )
		{
			$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'COM_JOOMLEAGUE_ADMIN_DIVISION_THE_DIVISION' ), $division->name );
			$mainframe->redirect( 'index.php?option=' . $option, $msg );
		}

		// Edit or Create?
		if ( !$isNew )
		{
			$model->checkout( $user->get( 'id' ) );
		}
		else
		{
			// initialise new record
			$division->order	= 0;
		}

		/* build the html select list for ordering
		$query = '	SELECT	ordering AS value,
							name AS text
					FROM #__joomleague_division
					WHERE project_id = ' . $project_id . '
					ORDER BY ordering';

		$lists['ordering'] = JHtml::_( 'list.specificordering', $division, $division->id, $query, 1 );
		*/
		$projectws =& $this->get( 'Data', 'projectws' );

		//build the html select list for parent divisions
		$parents[] = JHtml::_( 'select.option', '0', JText::_( 'COM_JOOMLEAGUE_GLOBAL_SELECT_PROJECT' ) );
		if ( $res =& $model->getParentsDivisions() )
		{
			$parents = array_merge( $parents, $res );
		}
		$lists['parents'] = JHtml::_(	'select.genericlist', $parents, 'parent_id', 'class="inputbox" size="1"', 'value', 'text',
										$division->parent_id );
		unset( $parents );

		$this->assignRef( 'projectws',	$projectws );
		$this->assignRef( 'lists',		$lists );
		$this->assignRef( 'division',	$division );
		$this->assignRef('form',  $this->get('form'));
    $this->assign('cfg_which_media_tool', JComponentHelper::getParams($option)->get('cfg_which_media_tool',0) );		
		//$extended = $this->getExtended($projectreferee->extended, 'division');
		//$this->assignRef( 'extended', $extended );

		$this->addToolbar();		
		parent::display( $tpl );
	}
	
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{	
		JRequest::setVar('hidemainmenu', true);
        JRequest::setVar('pid', $this->project_id);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;
        
		$canDo = sportsmanagementHelper::getActions($this->item->id);
		JToolBarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_DIVISIONS_NEW') : JText::_('COM_SPORTSMANAGEMENT_DIVISIONS_EDIT'), 'helloworld');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			$this->item->project_id = $this->project_id;
            // For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('division.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('division.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('division.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('division.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('division.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('division.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('division.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::custom('division.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('division.cancel', 'JTOOLBAR_CLOSE');
		}
    sportsmanagementHelper::ToolbarButtonOnlineHelp();
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
