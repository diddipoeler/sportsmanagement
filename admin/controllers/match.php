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
        $row =& $model->getTable();
        // bind the form fields to the table
        if (!$row->bind($post)) {
        $this->setError($this->_db->getErrorMsg());
        return false;
        }
         // make sure the record is valid
        if (!$row->check()) {
        $this->setError($this->_db->getErrorMsg());
        return false;
        }
        // store to the database
		if ($row->store($post))
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ADD_MATCH');
		}
		else
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_ADD_MATCH').$model->getError();
		}
		$link = 'index.php?option=com_sportsmanagement&view=matches';
		$this->setRedirect($link,$msg);
	}
    

}
