<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.model');
 
/**
 * 
 */
class sportsmanagementModelcpanel extends JModel
{

public function getVersion() 
	{
	   $mainframe =& JFactory::getApplication();
	   $this->_db->setQuery('SELECT manifest_cache FROM #__extensions WHERE name = "com_sportsmanagement"');
       $manifest_cache = json_decode( $this->_db->loadResult(), true );
	   //$mainframe->enqueueMessage(JText::_('manifest_cache<br><pre>'.print_r($manifest_cache,true).'</pre>'   ),'');
       return $manifest_cache['version'];	
	}

public function getGithubRequests()
{
$mainframe =& JFactory::getApplication();    
$paramsdata = JComponentHelper::getParams('com_sportsmanagement');
$mainframe->enqueueMessage(JText::_('getGithubRequests paramsdata<br><pre>'.print_r($paramsdata,true).'</pre>'   ),'');
    
}
    
}


?>    