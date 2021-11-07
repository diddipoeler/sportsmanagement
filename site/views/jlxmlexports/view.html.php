<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlxmlexports
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    mod_sportsmanagement_calendar
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\HtmlView;

use Joomla\CMS\Factory;

jimport('joomla.application.component.view');

class sportsmanagementViewjlxmlexports extends HtmlView
{
	function display($tpl = null)
	{

		// Get a refrence of the page instance in joomla
		$document = &Factory::getDocument();
		$uri      = &Factory::getURI();

		$model = $this->getModel();

		$model->exportData();

		parent::display($tpl);
	}
}
