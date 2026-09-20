<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GCalendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GCalendar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @version   5.6.0
 * @package   GCalendar
 * @author    Digital Peak http://www.digital-peak.com
 * @copyright Copyright (C) 2007 - 2013 Digital Peak. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

\defined('_JEXEC') or die;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

JLoader::import('joomla.application.component.view');

JLoader::import('components.com_sportsmanagement.libraries.GCalendar.GCalendarZendHelper', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.dbutil', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.util', JPATH_ADMINISTRATOR);

class sportsmanagementViewJSONFeed extends HtmlView
{

	public function display($tpl = null)
	{
		/** @var SiteApplication $app */
		$app = Factory::getContainer()->get(SiteApplication::class);

		if (!$app->isClient('site')) {
			throw new \RuntimeException('SportsManagement JSON feed view requires the Joomla site application.', 500);
		}

		$input = $app->getInput();
		$start = jsmGCalendarUtil::getDate($input->getInt('start'));
		$input->set('start', $start->format('U') - $start->getTimezone()->getOffset($start));
		$end = jsmGCalendarUtil::getDate($input->getInt('end'));
		$input->set('end', $end->format('U') - $end->getTimezone()->getOffset($end));

		parent::display($tpl);
	}
}
