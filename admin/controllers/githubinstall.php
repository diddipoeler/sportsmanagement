<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 
/**
 * SportsManagement Controller
 */
/**
 * sportsmanagementControllergithubinstall
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllergithubinstall extends JControllerForm
{

/**
	 * Method to store 
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
  function store()
	{
        // Check for request forgeries
		JRequest::checkToken() or die('JINVALID_TOKEN');
$msg = '';

        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);
	}

}
