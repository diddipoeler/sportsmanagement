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

use Joomla\CMS\MVC\Model\BaseDatabaseModel;


class sportsmanagementModelGCalendar extends BaseDatabaseModel 
{

	private $cached_data = null;
	protected $_extension = 'com_sportsmanagement';

	protected function populateState()
    {
		$this->setState('filter.extension', $this->_extension);

		$calendarids = Factory::getApplication()->getParams()->get('calendarids');
		if(!is_array($calendarids) && !empty($calendarids)){
			$calendarids = array($calendarids);
		}
		$tmp = Factory::getApplication()->input->getVar('gcids', null);
		if(!empty($tmp)){
			$calendarids = explode(',', $tmp);
		}
		$this->setState('calendarids', $calendarids);

		$this->setState('params', Factory::getApplication()->getParams());
	}

	public function getDBCalendars()
    {
        $app = Factory::getApplication();
		if($this->cached_data == null){
			$calendarids = $this->getState('calendarids');
			if(!empty($calendarids)){
				$this->cached_data = jsmGCalendarDBUtil::getCalendars($calendarids);
			} else {
				$this->cached_data = jsmGCalendarDBUtil::getAllCalendars();
			}
		}
        
        
        
		return $this->cached_data;
	}
}