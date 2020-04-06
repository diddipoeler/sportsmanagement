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
 * @package   GCalendar
 * @author    Digital Peak http://www.digital-peak.com
 * @copyright Copyright (C) 2007 - 2013 Digital Peak. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
JLoader::import('joomla.application.component.view');

JLoader::import('components.com_sportsmanagement.libraries.GCalendar.GCalendarZendHelper', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.dbutil', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.util', JPATH_ADMINISTRATOR);

class sportsmanagementViewJSONFeed extends JViewLegacy
{

    public function display($tpl = null)
    {
        $start = jsmGCalendarUtil::getDate(Factory::getApplication()->input->getInt('start'));
        Factory::getApplication()->input->setVar('start', $start->format('U') - $start->getTimezone()->getOffset($start));
        $end = jsmGCalendarUtil::getDate(Factory::getApplication()->input->getInt('end'));
        Factory::getApplication()->input->setVar('end', $end->format('U') - $end->getTimezone()->getOffset($end));

        parent::display($tpl);
    }
}
