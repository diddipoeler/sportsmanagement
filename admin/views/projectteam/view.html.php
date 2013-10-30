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
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the Joomleague component
 *
 * @static
 * @package	JoomLeague
 * @since	0.1
 */
class sportsmanagementViewProjectteam extends JView
{
	function display($tpl = null)
	{
		$option 	= JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$project_id = $mainframe->getUserState( $option . 'project' );
		$uri 		= JFactory::getURI();
		$user 		= JFactory::getUser();
		$model		= $this->getModel();
		$lists		= array();

		//get the project_team
		$project_team =& $this->get('data');
		$isNew = ($project_team->id < 1);

		// fail if checked out not by 'me'
		if ($model->isCheckedOut($user->get('id')))
		{
			$msg = JText::sprintf('DESCBEINGEDITTED',JText::_('COM_JOOMLEAGUE_ADMIN_P_TEAM_THE_TEAM'),$project_team->name);
			$mainframe->redirect('index.php?option=com_joomleague',$msg);
		}

		// Edit or Create?
		if (!$isNew)
		{
			$model->checkout($user->get('id'));
		}
		else
		{
			// initialise new record
			$project_team->order = 0;
			// $project_team->parent_id = 0;
		}
		$projectws	=& $this->get('Data','projectws');

		//build the html select list for days of week
		if ($trainingData=$model->getTrainigData($project_team->id))
		{
			$daysOfWeek=array(	0 => JText::_('COM_JOOMLEAGUE_GLOBAL_SELECT'),
			1 => JText::_('COM_JOOMLEAGUE_GLOBAL_MONDAY'),
			2 => JText::_('COM_JOOMLEAGUE_GLOBAL_TUESDAY'),
			3 => JText::_('COM_JOOMLEAGUE_GLOBAL_WEDNESDAY'),
			4 => JText::_('COM_JOOMLEAGUE_GLOBAL_THURSDAY'),
			5 => JText::_('COM_JOOMLEAGUE_GLOBAL_FRIDAY'),
			6 => JText::_('COM_JOOMLEAGUE_GLOBAL_SATURDAY'),
			7 => JText::_('COM_JOOMLEAGUE_GLOBAL_SUNDAY'));
			$dwOptions=array();
			foreach($daysOfWeek AS $key => $value){$dwOptions[]=JHTML::_('select.option',$key,$value);}
			foreach ($trainingData AS $td)
			{
				$lists['dayOfWeek'][$td->id]=JHTML::_('select.genericlist',$dwOptions,'dw_'.$td->id,'class="inputbox"','value','text',$td->dayofweek);
			}
			unset($daysOfWeek);
			unset($dwOptions);
		}

		if ($projectws->project_type == 'DIVISIONS_LEAGUE') // No divisions
		{
			//build the html options for divisions
			$division[]=JHTMLSelect::option('0',JText::_('COM_JOOMLEAGUE_GLOBAL_SELECT_DIVISION'));
			$mdlDivisions = JModel::getInstance("divisions", "JoomLeagueModel");
			if ($res =& $mdlDivisions->getDivisions($project_id)){
				$division=array_merge($division,$res);
			}
			$lists['divisions']=$division;
				
			unset($res);
			unset($divisions);
		}

		$this->assignRef('form'      	, $this->get('form'));	
		$extended = $this->getExtended($project_team->extended, 'projectteam');
		$this->assignRef( 'extended', $extended );
		//$this->assignRef('imageselect',		$imageselect);
		$this->assignRef('projectws',		$projectws);
		$this->assignRef('lists',			$lists);
		$this->assignRef('project_team',	$project_team);
		$this->assignRef('trainingData',	$trainingData);
        $this->assign('cfg_which_media_tool', JComponentHelper::getParams($option)->get('cfg_which_media_tool',0) );
		$this->addToolbar();
		parent::display($tpl);
	}
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_JOOMLEAGUE_ADMIN_P_TEAM_TITLE'));
		
		JLToolBarHelper::save('projectteam.save');
		JLToolBarHelper::apply('projectteam.apply');
		JLToolBarHelper::cancel('projectteam.cancel',JText::_('COM_JOOMLEAGUE_GLOBAL_CLOSE'));
		JToolBarHelper::divider();
		JLToolBarHelper::onlinehelp();
	}
}
?>