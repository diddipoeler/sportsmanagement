<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controller library
jimport('joomla.application.component.controller');
 
/**
 * General Controller of SportsManagement component
 */
class SportsManagementController extends JController
{
	/**
	 * display task
	 *
	 * @return void
	 */
	function display($cachable = false, $urlparams = false) 
	{
		// set default view if not set
		//JRequest::setVar('view', JRequest::getCmd('view', 'SportsManagements'));
		JRequest::setVar('view', JRequest::getCmd('view', 'cpanel'));
 
		// call parent behavior
		parent::display($cachable);
 
		// Set the submenu
		sportsmanagementHelper::addSubmenu('messages');
	}
}
