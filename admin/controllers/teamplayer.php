<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');

/**
 * HelloWorld Controller
 */
class sportsmanagementControllerteamplayer extends JControllerForm
{

    var $_project_id = 0;
    var $_team_id = 0;
    var $_project_team_id = 0;
    
    
    function __construct()
	{
		parent::__construct();

		$this->_project_id	= JRequest::getVar('pid');
	}
    

}
