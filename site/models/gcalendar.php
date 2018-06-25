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

JLoader::import( 'joomla.application.component.model' );
//JLoader::import('components.com_sportsmanagement.libraries.dbutil', JPATH_ADMINISTRATOR);

class sportsmanagementModelGCalendar extends JModelLegacy 
{

	private $cached_data = null;
	protected $_extension = 'com_sportsmanagement';

	protected function populateState()
    {
		$this->setState('filter.extension', $this->_extension);

		$calendarids = JFactory::getApplication()->getParams()->get('calendarids');
		if(!is_array($calendarids) && !empty($calendarids)){
			$calendarids = array($calendarids);
		}
		$tmp = JFactory::getApplication()->input->getVar('gcids', null);
		if(!empty($tmp)){
			$calendarids = explode(',', $tmp);
		}
		$this->setState('calendarids', $calendarids);

		$this->setState('params', JFactory::getApplication()->getParams());
	}

	public function getDBCalendars()
    {
        $app = JFactory::getApplication();
		if($this->cached_data == null){
			$calendarids = $this->getState('calendarids');
			if(!empty($calendarids)){
				$this->cached_data = jsmGCalendarDBUtil::getCalendars($calendarids);
			} else {
				$this->cached_data = jsmGCalendarDBUtil::getAllCalendars();
			}
		}
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' cached_data<br><pre>'.print_r($this->cached_data,true).'</pre>'),'Notice');
        }
        
		return $this->cached_data;
	}
}