<?php 
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

class sportsmanagementViewAbout extends JView
{
	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();

		$model = $this->getModel();
		$about = $model->getAbout();
		$this->assignRef('about',	$about);

		// Set page title
		$document->setTitle(JText::_('COM_SPORTSMANAGEMENT_ABOUT_PAGE_TITLE'));

		parent::display($tpl);
	}

}
?>