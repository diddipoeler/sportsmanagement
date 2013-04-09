<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
/**
 * Script file of com_sportsmanagement component
 */
class com_sportsmanagementInstallerScript
{
	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent) 
	{
		// $parent is the class calling this method
		$parent->getParent()->setRedirectURL('index.php?option=com_sportsmanagement');
	}
 
	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent) 
	{
		// $parent is the class calling this method
		echo '<p>' . JText::_('COM_SPORTSMANAGEMENT_UNINSTALL_TEXT') . '</p>';
	}
 
	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent) 
	{
		// $parent is the class calling this method
		echo '<p>' . JText::_('COM_SPORTSMANAGEMENT_UPDATE_TEXT') . '</p>';
	}
 
	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent) 
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		echo '<p>' . JText::_('COM_SPORTSMANAGEMENT_PREFLIGHT_' . $type . '_TEXT') . '</p>';
	}
 
	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent) 
	{
	$mainframe =& JFactory::getApplication();
    $db = JFactory::getDbo();
        // $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		echo '<p>' . JText::_('COM_SPORTSMANAGEMENT_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
        
    $paramsdata = JComponentHelper::getParams('com_sportsmanagement');
	$paramsdefs = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'config.xml';
	$params = new JParameter( $paramsdata, $paramsdefs );
	
	$jRegistry = new JRegistry();
    $jRegistry->loadString($params->toString('ini'), 'ini');
  $newparams = $jRegistry->toString();
  // store the combined new and existing values back as a JSON string
                                  
                        $db->setQuery('UPDATE #__extensions SET params = ' .
                                $db->quote( $newparams ) .
                                ' WHERE name = "com_sportsmanagement"' );
                                $db->query();
                                
    
    
        
	}
}
