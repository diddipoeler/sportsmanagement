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
 * HTML View class for the Joomleague component
 *
 * @author	Kurt Norgaz
 * @package	Joomleague
 * @since	1.5.0a
 */
class sportsmanagementViewJLXMLImport extends JView
{
	function display( $tpl = null )
	{
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'JoomLeague XML Import' ), 'generic.png' );
		JToolBarHelper::back();
		#JLToolBarHelper::save( 'save', 'Import' );
		JLToolBarHelper::onlinehelp();

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