<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    mod_sportsmanagement_calendar
 * @subpackage jlxmlexports
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

jimport('joomla.application.component.view');

class sportsmanagementViewjlxmlexports extends JViewLegacy
{
	function display( $tpl = null )
	{

				// Get a refrence of the page instance in joomla
		$document = & Factory::getDocument();
		$uri = &Factory::getURI();

		  $model = $this->getModel();

		  $model->exportData();

		parent::display($tpl);
	}
}
