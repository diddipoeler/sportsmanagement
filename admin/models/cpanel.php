<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * 
 */
class sportsmanagementModelcpanel extends JModelAdmin
{

public function getVersion() 
	{
	   $mainframe =& JFactory::getApplication();
	   $this->db->setQuery('SELECT params FROM #__extensions WHERE name = "com_sportsmanagement"');
       $params = json_decode( $db->loadResult(), true );
	$mainframe->enqueueMessage(JText::_('params<br><pre>'.print_r($params,true).'</pre>'   ),'');	
	}


    
}


?>    