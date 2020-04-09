<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage sportsmanagements
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewsportsmanagements
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewsportsmanagements extends sportsmanagementView
{
	/**
	 * SportsManagements view display method
	 *
	 * @return void
	 */
	function display($tpl = null)
	{
		// Get data from the model
		$items      = $this->get('Items');
		$pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			Log::add(implode('<br />', $errors));

			return false;
		}

		// Assign data to the view
		$this->items      = $items;
		$this->pagination = $pagination;

		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		// Get a refrence of the page instance in joomla
		$document = Factory::getDocument();

		// Set toolbar items for the page
		$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css' . '" type="text/css" />' . "\n";
		$document->addCustomTag($stylelink);
		$canDo = sportsmanagementHelper::getActions();
		ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_S'), 'helloworld');

		if ($canDo->get('core.create'))
		{
			ToolbarHelper::addNew('sportsmanagement.add', 'JTOOLBAR_NEW');
		}

		if ($canDo->get('core.edit'))
		{
			ToolbarHelper::editList('sportsmanagement.edit', 'JTOOLBAR_EDIT');
		}

		if ($canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('', 'sportsmanagements.delete', 'JTOOLBAR_DELETE');
		}

		if ($canDo->get('core.admin'))
		{
			ToolbarHelper::divider();
			ToolbarHelper::preferences('com_sportsmanagement');
		}
	}

	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument()
	{
		$document = Factory::getDocument();
		$document->setTitle(Text::_('COM_HELLOWORLD_ADMINISTRATION'));
	}
}
