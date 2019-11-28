<?php

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Log\Log;
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * HTML View class for the HelloWorld Component
 */
class sportsmanagementViewsportsmanagement extends JViewLegacy
{
	// Overwriting JViewLegacy display method
	function display($tpl = null) 
	{
		// Assign data to the view
		$this->item = $this->get('Item');
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			Log::add( implode('<br />', $errors));
			return false;
		}
		// Display the view
		parent::display($tpl);
	}
}
