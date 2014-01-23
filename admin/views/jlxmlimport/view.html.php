<?php
/**
 * @copyright	Copyright (C) 2006-2013 JoomLeague.net. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );
#require_once ('libraries/joomla/factory.php');


/**
 * sportsmanagementViewJLXMLImport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewJLXMLImport extends JView
{
	function display( $tpl = null )
	{
  		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'JoomLeague XML Import' ), 'xmlimport' );
		JToolBarHelper::back();
		
        JToolBarHelper::divider();
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences(JRequest::getCmd('option'));

		$db		= JFactory::getDBO();
		$uri	= JFactory::getURI();

		#$user = JFactory::getUser();
		#$config = JFactory::getConfig();
		$config =& JComponentHelper::getParams('com_media');

		#$this->assignRef( 'user',			JFactory::getUser() );
		$this->assignRef( 'request_url',	$uri->toString() );
		#$this->assignRef( 'user',		$user);
		$this->assignRef( 'config',		$config);

		parent::display( $tpl );
	}
}
?>