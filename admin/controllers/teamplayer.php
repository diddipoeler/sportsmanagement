<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');

/**
 * SportsManagement Controller
 */
class sportsmanagementControllerteamplayer extends JControllerForm
{

/**
	 * Method to remove a teamplayer
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function remove()
	{
	$mainframe =& JFactory::getApplication();
    $pks = JRequest::getVar('cid', array(), 'post', 'array');
    $model = $this->getModel('teamplayer');
    $model->delete($pks);
	
    $this->setRedirect('index.php?option=com_sportsmanagement&view=teamplayers');    
        
   }    
    

}
