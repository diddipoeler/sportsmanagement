<?php
// No direct access to this file
defined('_JEXEC') or die;
 
/**
 * HelloWorld component helper.
 */
abstract class sportsmanagementHelper
{
	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($submenu) 
	{
		JSubMenuHelper::addEntry(JText::_('COM_SPORTSMANAGEMENT_MENU'), 'index.php?option=com_sportsmanagement', $submenu == 'menu');
		JSubMenuHelper::addEntry(JText::_('COM_SPORTSMANAGEMENT_SUBMENU_EXTENSIONS'), 'index.php?option=com_sportsmanagement&view=extensions', $submenu == 'extensions');
		// set some global property
		$document = JFactory::getDocument();
		$document->addStyleDeclaration('.icon-48-helloworld {background-image: url(../media/com_sportsmanagement/images/tux-48x48.png);}');
		if ($submenu == 'extensions') 
		{
			$document->setTitle(JText::_('COM_SPORTSMANAGEMENT_ADMINISTRATION_EXTENSIONS'));
		}
	}
	/**
	 * Get the actions
	 */
	public static function getActions($messageId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;
 
		if (empty($messageId)) {
			$assetName = 'com_sportsmanagement';
		}
		else {
			$assetName = 'com_sportsmanagement.message.'.(int) $messageId;
		}
 
		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.delete'
		);
 
		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}
 
		return $result;
	}
}
