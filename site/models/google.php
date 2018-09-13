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
 * @package		GCalendar
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2007 - 2013 Digital Peak. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
JLoader::import( 'joomla.application.component.model' );

class sportsmanagementModelGoogle extends JModelLegacy 
{

	private $cached_data = null;

	public function getDBCalendars() 
    {
        $app = Factory::getApplication();
		if ($this->cached_data == null) 
        {
			$calendars = jsmGCalendarDBUtil::getAllCalendars();
			$this->cached_data = $calendars;
		}
        
        $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' cached_data<br><pre>'.print_r($this->cached_data,true).'</pre>'),'Notice');
        
		return $this->cached_data;
	}
}