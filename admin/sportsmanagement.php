<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_sportsmanagement')) 
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}
 
// require helper file
JLoader::register('SportsManagementHelper', dirname(__FILE__) . DS . 'helpers' . DS . 'sportsmanagement.php');
 
require_once(JPATH_ROOT.DS.'components'.DS.'com_sportsmanagement'.DS. 'helpers' . DS . 'countries.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_sportsmanagement'.DS. 'helpers' . DS . 'imageselect.php');

// welche tabelle soll genutzt werden
$params =& JComponentHelper::getParams( 'com_sportsmanagement' );
$database_table	= $params->get( 'cfg_which_database_table' ); 
DEFINE( 'COM_SPORTSMANAGEMENT_TABLE',		$database_table );
		
// import joomla controller library
jimport('joomla.application.component.controller');
 
// Get an instance of the controller prefixed by SportsManagement
$controller = JController::getInstance('SportsManagement');
 
// Perform the Request task
$controller->execute(JRequest::getCmd('task'));
 
// Redirect if set by the controller
$controller->redirect();
