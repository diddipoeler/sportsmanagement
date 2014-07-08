<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
// import Joomla html for use with stylesheets
jimport('joomla.html.html');


class sportsmanagementViewUpdsportsmanagement extends JViewLegacy
{
	// Overwriting JViewLegacy display method
	function display($tpl = null) 
	{
		$app		= JFactory::getApplication();
		$params		= $app->getParams();
		$dispatcher = JDispatcher::getInstance();

		// Get some data from the models
		$state		= $this->get('State');
		$item		= $this->get('Item');
		$this->form	= $this->get('Form');
		$this->state = $state;

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		// get the stylesheet and/or other document values
        $this->addDocStyle();

		// Display the view
        parent::display($tpl);

	}

	/**
	 * Add the stylesheet to the document.
	 */
	protected function addDocStyle()
	{
        $doc = JFactory::getDocument();
        $doc->addStyleSheet('media/com_sportsmanagement/css/site.stylesheet.css');
    }
}
