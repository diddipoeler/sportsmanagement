<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       jsmgcalendars.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage jsmgcalendar
 */

defined('_JEXEC') or die();

/**
 * sportsmanagementControllerjsmGCalendars
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2018
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllerjsmGCalendars extends JSMControllerAdmin
{

	/**
	 * sportsmanagementControllerjsmGCalendars::getModel()
	 *
	 * @param   string $name
	 * @param   string $prefix
	 * @param   mixed  $config
	 * @return
	 */
	public function getModel($name = 'jsmGCalendar', $prefix = 'sportsmanagementModel', $config = Array() )
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

}
