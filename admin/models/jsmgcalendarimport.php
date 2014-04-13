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

JLoader::import('joomla.application.component.model');

class sportsmanagementModeljsmgcalendarImport extends JModelLegacy 
{

	public function __construct() 
    {
		parent::__construct();

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}

	public function setId($id) 
    {
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	public function getOnlineData() 
    {
		try {
			$user = JRequest::getVar('user', null);
			$pass = JRequest::getVar('pass', null);

			$calendars = jsmGCalendarZendHelper::getCalendars($user, $pass);
			if ($calendars == null) {
				return null;
			}

			$this->_data = array();
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_sportsmanagement/tables');
			foreach ($calendars as $calendar) {
				$table = $this->getTable('jsmGCalendar', 'sportsmanagementTable');
				$table->id = 0;
				$cal_id = substr($calendar->getId(),strripos($calendar->getId(),'/')+1);
				$table->calendar_id = $cal_id;
				$table->username = $user;
				$table->password = $pass;
				$table->name = (string)$calendar->getTitle();
				if(strpos($calendar->getColor(), '#') === 0){
					$color = str_replace("#","", (string)$calendar->getColor());
					$table->color = $color;
				}

				$this->_data[] = $table;
			}
		} catch(Exception $e){
			JError::raiseWarning(200, $e->getMessage());
			$this->_data = null;
		}

		return $this->_data;
	}

	public function getDBData() 
    {
		$query = "SELECT * FROM #__sportsmanagement_gcalendar";
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}

	public function store()	
    {
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_sportsmanagement/tables');
		$row = $this->getTable('jsmGCalendar', 'sportsmanagementTable');

		$cids = JRequest::getVar('cid', array(0), 'post', 'array');
		foreach ($cids as $index => $cid) {
			$data = unserialize(base64_decode($cid));
			$row->id = 0;
			$row->calendar_id = $data['id'];
			$row->color = $data['color'];
			$row->name = $data['name'];
            $row->username = JComponentHelper::getParams('com_sportsmanagement')->get('google_mail_account','');
            $row->password = JComponentHelper::getParams('com_sportsmanagement')->get('google_mail_password','');

			if (!$row->check()) {
				JError::raiseWarning( 500, $row->getError() );
				return false;
			}

			if (!$row->store()) {
				JError::raiseWarning( 500, $row->getError() );
				return false;
			}
		}
		return true;
	}
}