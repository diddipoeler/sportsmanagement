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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

JLoader::import('joomla.application.component.model');
BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'models', 'sportsmanagementModel');

class jsmGCalendarDBUtil
{

	public static function getCalendar($calendarID) 
    {
		$model = BaseDatabaseModel::getInstance('jsmGCalendars', 'sportsmanagementModel', array('ignore_request' => true));
		$model->setState('ids',$calendarID);
		$items = $model->getItems();
		if(empty($items)){
			return null;
		}
		return $items[0];
	}

	public static function getCalendars($calendarIDs) 
    {
		$model = BaseDatabaseModel::getInstance('jsmGCalendars', 'sportsmanagementModel', array('ignore_request' => true));
		$model->setState('ids', $calendarIDs);
		return $model->getItems();
	}

	public static function getAllCalendars() 
    {
		$model = BaseDatabaseModel::getInstance('jsmGCalendars', 'sportsmanagementModel', array('ignore_request' => true));
		return $model->getItems();
	}
}