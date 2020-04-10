<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage sportsmanagement
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
 * SportsManagement View
 */
class sportsmanagementViewsportsmanagement extends sportsmanagementView
{
	/**
	 * display method of Hello view
	 *
	 * @return void
	 */
	public function init()
	{
		// Get the Data
		$form   = $this->get('Form');
		$item   = $this->get('Item');
		$script = $this->get('Script');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			Log::add(implode('<br />', $errors));

			return false;
		}

		// Assign the Data
		$this->form   = $form;
		$this->item   = $item;
		$this->script = $script;

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
		$jinput = Factory::getApplication()->input;
		$jinput->set('hidemainmenu', true);
		$user   = Factory::getUser();
		$userId = $user->id;
		$isNew  = $this->item->id == 0;
		$canDo  = sportsmanagementHelper::getActions($this->item->id);
		ToolbarHelper::title($isNew ? Text::_('COM_SPORTSMANAGEMENT__NEW') : Text::_('COM_SPORTSMANAGEMENT__EDIT'), 'helloworld');

		// Built the actions for new and existing records.
		if ($isNew)
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create'))
			{
				ToolbarHelper::apply('sportsmanagement.apply', 'JTOOLBAR_APPLY');
				ToolbarHelper::save('sportsmanagement.save', 'JTOOLBAR_SAVE');
				ToolbarHelper::custom('sportsmanagement.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}

			ToolbarHelper::cancel('sportsmanagement.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				ToolbarHelper::apply('sportsmanagement.apply', 'JTOOLBAR_APPLY');
				ToolbarHelper::save('sportsmanagement.save', 'JTOOLBAR_SAVE');

				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create'))
				{
					ToolbarHelper::custom('sportsmanagement.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}

			if ($canDo->get('core.create'))
			{
				ToolbarHelper::custom('sportsmanagement.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}

			ToolbarHelper::cancel('sportsmanagement.cancel', 'JTOOLBAR_CLOSE');
		}
	}

	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument()
	{
		$isNew    = $this->item->id == 0;
		$document = Factory::getDocument();
		$document->setTitle($isNew ? Text::_('COM_HELLOWORLD_HELLOWORLD_CREATING') : Text::_('COM_HELLOWORLD_HELLOWORLD_EDITING'));
		$document->addScript(Uri::root() . $this->script);
		$document->addScript(Uri::root() . "/administrator/components/com_sportsmanagement/views/sportsmanagement/submitbutton.js");
		Text::script('COM_HELLOWORLD_HELLOWORLD_ERROR_UNACCEPTABLE');
	}
}
