<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 
/**
 * SportsManagement Controller
 */
class sportsmanagementControllermatch extends JControllerForm
{

//	add a match to round
	function addmatch()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$post = JRequest::get('post');
		$post['project_id'] = $mainframe->getUserState( "$option.pid", '0' );
		$post['round_id'] = $mainframe->getUserState( "$option.rid", '0' );
		$model = $this->getModel('match');
		if ($model->store($post))
		{
			$msg = JText::_('COM_JOOMLEAGUE_ADMIN_MATCH_CTRL_ADD_MATCH');
		}
		else
		{
			$msg = JText::_('COM_JOOMLEAGUE_ADMIN_MATCH_CTRL_ERROR_ADD_MATCH').$model->getError();
		}
		$link = 'index.php?option=com_joomleague&view=matches';
		$this->setRedirect($link,$msg);
	}
    

}
