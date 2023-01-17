<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage json
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    mod_sportsmanagement_calendar
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\HtmlView;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

// Import Joomla view library
jimport('joomla.application.component.view');

// Import Joomla html for use with stylesheets
jimport('joomla.html.html');


class sportsmanagementViewjson extends HtmlView
{
	// Overwriting JViewLegacy display method
	function display($tpl = null)
	{
		$app        = Factory::getApplication();
		$params     = $app->getParams();
		$dispatcher = JDispatcher::getInstance();

		// Get some data from the models
		$state       = $this->get('State');
		$item        = $this->get('Item');
		$this->form  = $this->get('Form');
		$this->state = $state;

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			Log::add(implode('<br />', $errors));

			return false;
		}

		// Get the stylesheet and/or other document values
		$this->addDocStyle();

		// Display the view
		parent::display($tpl);

	}

	/**
	 * Add the stylesheet to the document.
	 */
	protected function addDocStyle()
	{
		$doc = Factory::getDocument();
		$doc->addStyleSheet('media/com_sportsmanagement/css/site.stylesheet.css');
	}
}
