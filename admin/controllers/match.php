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


	/**
	 * Method add a match to round
	 *
	 * @access	public
	 * @return	
	 * @since	0.1
	 */
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
    
    
    
    /**
	 * Method to remove a matchday
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function remove()
	{
	$mainframe =& JFactory::getApplication();
    $pks = JRequest::getVar('cid', array(), 'post', 'array');
    $model = $this->getModel('match');
    $model->delete($pks);
	
    $this->setRedirect('index.php?option=com_sportsmanagement&view=matches');    
        
   }     
    

}
