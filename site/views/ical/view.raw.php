<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.raw.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage ical
 */

defined('_JEXEC') or die();

JLoader::import( 'joomla.application.component.view');

JLoader::import('components.com_sportsmanagement.libraries.GCalendar.GCalendarZendHelper', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.dbutil', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.util', JPATH_ADMINISTRATOR);

class sportsmanagementViewIcal extends JViewLegacy
{

	public function display($tpl = null) 
    {
		$this->setModel(JModelLegacy::getInstance('Event', 'sportsmanagementModel'), true);

		$this->event = $this->get('GCalendar');

		parent::display($tpl);
	}
}