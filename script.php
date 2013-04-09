<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
/**
 * Script file of com_sportsmanagement component
 */
class com_sportsmanagementInstallerScript
{
	/*
     * The release value would ideally be extracted from <version> in the manifest file,
     * but at preflight, the manifest file exists only in the uploaded temp folder.
     */
    private $release = '1.0.00';
    
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
		echo '<p>' . JText::_('COM_SPORTSMANAGEMENT_UPDATE_TEXT') . $this->release . '</p>';
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
		echo '<p>' . JText::_('COM_SPORTSMANAGEMENT_PREFLIGHT_' . $type . '_TEXT' ) . $this->release . '</p>';
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
		echo '<p>' . JText::_('COM_SPORTSMANAGEMENT_POSTFLIGHT_' . $type . '_TEXT' ) . $this->release . '</p>';
        
    //$paramsdata = JComponentHelper::getParams('com_sportsmanagement');
    $db->setQuery('SELECT params FROM #__extensions WHERE name = "com_sportsmanagement"');
    $paramsdata = json_decode( $db->loadResult(), true );
	$paramsdefs = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'config.xml';
	$params = new JParameter( $paramsdata, $paramsdefs );
	
    $mainframe->enqueueMessage(JText::_('postflight paramsdata<br><pre>'.print_r($paramsdata,true).'</pre>'   ),'');
    $mainframe->enqueueMessage(JText::_('postflight params<br><pre>'.print_r($params,true).'</pre>'   ),'');
    
    /*
    $mainframe->enqueueMessage(JText::_('postflight params<br><pre>'.print_r($params,true).'</pre>'   ),'');
	$jRegistry = new JRegistry();
    $jRegistry->loadString($params->toString('ini'), 'ini');
  $newparams = $jRegistry->toString();
  $mainframe->enqueueMessage(JText::_('postflight newparams<br><pre>'.print_r($newparams,true).'</pre>'   ),'');
  $this->setParams( $newparams );
  */                              
    
    
        
	}
    
    /*
    * sets parameter values in the component's row of the extension table
    */
    function setParams($param_array) 
    {
        $mainframe =& JFactory::getApplication();
        $db = JFactory::getDbo();
                        
                                
                if ( count($param_array) > 0 ) {
                        // read the existing component value(s)
                        $db = JFactory::getDbo();
                        $db->setQuery('SELECT params FROM #__extensions WHERE name = "com_sportsmanagement"');
                        $params = json_decode( $db->loadResult(), true );
                        $mainframe->enqueueMessage(JText::_('setParams params<br><pre>'.print_r($params,true).'</pre>'   ),'');
                        // add the new variable(s) to the existing one(s)
                        foreach ( $param_array as $name => $value ) {
                                $params[ (string) $name ] = (string) $value;
                        }
                        // store the combined new and existing values back as a JSON string
                        $paramsString = json_encode( $params );
                        $db->setQuery('UPDATE #__extensions SET params = ' .
                                $db->quote( $paramsString ) .
                                ' WHERE name = "com_sportsmanagement"' );
                                $db->query();
                }
                
        }      
}
