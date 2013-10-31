<?php
/**
 * @copyright	Copyright (C) 2006-2013 JoomLeague.net. All rights reserved.
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
 * @package	JoomLeague
 * @since	0.1
 */
class JoomleagueViewProjectReferee extends JView
{

	function display($tpl=null)
	{
		$mainframe	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$db	 		= JFactory::getDBO();
		$uri		= JFactory::getURI();
		$user		= JFactory::getUser();
		$model		= $this->getModel();

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
        
        $project_id	= JRequest::getInt('pid');
        $mdlProject = JModel::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($project_id);
        $this->assignRef('project',$project);
        
		// Assign the Data
		$this->form = $form;
		$this->item = $item;
		$this->script = $script;
        
        //build the html select list for positions
		$refereepositions=array();
		$refereepositions[]=JHTML::_('select.option',	'0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_REF_POS'));
        $mdlPositions = JModel::getInstance("Positions", "sportsmanagementModel");
	    $project_ref_positions = $mdlPositions->getRefereePositions($project_id);
        $refereepositions=array_merge($refereepositions,$project_ref_positions);
        $lists['refereepositions']=JHTML::_(	'select.genericlist',
												$refereepositions,
												'project_position_id',
												'class="inputbox" size="1"',
												'value',
												'text',$this->item->project_position_id);
		unset($refereepositions);
        
/*        
		//get the projectreferee data of the project_team
		$projectreferee =& $this->get('data');
		$isNew=($projectreferee->id < 1);

		// fail if checked out not by 'me'
		if ($model->isCheckedOut($user->get('id')))
		{
			$msg=JText::sprintf('DESCBEINGEDITTED',JText::_('COM_JOOMLEAGUE_ADMIN_P_REF_THE_PREF'),$projectreferee->name);
			$mainframe->redirect('index.php?option=com_joomleague',$msg);
		}

		// Edit or Create?
		if ($isNew)
		{
			$projectreferee->order=0;
		}

		//build the html select list for positions
		$refereepositions=array();
		$refereepositions[]=JHTML::_('select.option',	'0',JText::_('COM_JOOMLEAGUE_GLOBAL_SELECT_REF_POS'));
		if ($res=& $model->getRefereePositions())
		{
			$refereepositions=array_merge($refereepositions,$res);
		}
		$lists['refereepositions']=JHTML::_(	'select.genericlist',
												$refereepositions,
												'project_position_id',
												'class="inputbox" size="1"',
												'value',
												'text',$projectreferee->project_position_id);
		unset($refereepositions);
                
		$projectws	=& $this->get('Data','projectws');

		$this->assignRef('form',			$this->get('form'));
		$this->assignRef('projectws',		$projectws);
*/



		$this->assignRef('lists',			$lists);
		$this->assignRef('projectreferee',	$projectreferee);
		$extended = $this->getExtended($projectreferee->extended, 'projectreferee');		
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