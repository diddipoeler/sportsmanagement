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
use Joomla\CMS\Filesystem\File;

class jsmGCalendarZendHelper {

	const SORT_ORDER_ASC = 'ascending';
	const SORT_ORDER_DESC = 'descending';

	const ORDER_BY_START_TIME = 'starttime';
	const ORDER_BY_LAST_MODIFIED = 'lastmodified';

	/**
	 * @param string $username
	 * @param string $password
	 *
	 * @return Zend_Gdata_Calendar_ListFeed|NULL
	 */
	public static function getCalendars($username, $password) 
    {
        $app = Factory::getApplication();
        
        
		try {
			$client = Zend_Gdata_ClientLogin::getHttpClient($username, $password, Zend_Gdata_Calendar::AUTH_SERVICE_NAME);

			$gdataCal = new Zend_Gdata_Calendar($client);
			return $gdataCal->getCalendarListFeed();
		} catch(Exception $e){
			JError::raiseWarning(200, $e->getMessage());
		}
		return null;
	}

	/**
	 * @param $calendar
	 * @param $startDate
	 * @param $endDate
	 * @param $max
	 * @param $filter
	 * @param $orderBy
	 * @param $pastEvents
	 * @param $sortOrder
	 *
	 * @return Zend_Gdata_App_Feed|NULL
	 */
	public static function getEvents($calendar, $startDate = null, $endDate = null, $max = 1000, $filter = null, $orderBy = jsmGCalendarZendHelper::ORDER_BY_START_TIME, $pastEvents = false, $sortOrder = jsmGCalendarZendHelper::SORT_ORDER_ASC, $startIndex = 1) 
    {
        $app = Factory::getApplication();
        
		// Implement View Level Access
		$user = Factory::getUser();
		if (!$user->authorise('core.admin') && !in_array($calendar->access, $user->getAuthorisedViewLevels())) {
			return array();
		}

		$cache = Factory::getCache('com_sportsmanagement');
		$cache->setCaching(jsmGCalendarUtil::getComponentParameter('gc_cache', 1) == '1');
		if(jsmGCalendarUtil::getComponentParameter('gc_cache', 1) == 2){
			$conf = Factory::getConfig();
			$cache->setCaching($conf->get('config.caching'));
		}
		$cache->setLifeTime(jsmGCalendarUtil::getComponentParameter('gc_cache_time', 900));

		//make a simple object for caching id
		$tmp = new JObject();
		$tmp->id = $calendar->id;
		$tmp->calendar_id = $calendar->calendar_id;
		$tmp->magic_cookie = $calendar->magic_cookie;
		$tmp->username = $calendar->username;
		$tmp->password = $calendar->password;
		$tmp->color = $calendar->color;
		$tmp->name = $calendar->name;

		$events = $cache->call(array('jsmGCalendarZendHelper', 'internalGetEvents'), $tmp, $startDate, $endDate, $max, $filter, $orderBy, $pastEvents, $sortOrder, $startIndex);
		$cache->gc();

		// Implement View Level Access
		$user = Factory::getUser();
		if (!$user->authorise('core.admin') && !in_array($calendar->access_content, $user->getAuthorisedViewLevels())) {
			foreach ($events as $event) {
				$event->setTitle(Text::_('COM_GCALENDAR_EVENT_BUSY_LABEL'));
				$event->setContent(null);
				$event->setWhere(null);
				$event->setWho(array());
			}
		}
       
		return $events;
	}

	/**
	 * @param $calendar
	 * @param $eventId
	 *
	 * @return Zend_Gdata_App_Entry|NULL
	 */
	public static function getEvent($calendar, $eventId) 
    {
        $app = Factory::getApplication();
        
		// Implement View Level Access
		$user = Factory::getUser();
		if (!$user->authorise('core.admin') && !in_array($calendar->access, $user->getAuthorisedViewLevels())) {
			return null;
		}

		$cache = Factory::getCache('com_sportsmanagement');
		$cache->setCaching(jsmGCalendarUtil::getComponentParameter('gc_cache', 1) == '1');
		if(jsmGCalendarUtil::getComponentParameter('gc_cache', 1) == 2){
			$conf = Factory::getConfig();
			$cache->setCaching($conf->get('config.caching'));
		}
		$cache->setLifeTime(jsmGCalendarUtil::getComponentParameter('gc_cache_time', 900));

		//make a simple object for caching id
		$tmp = new JObject();
		$tmp->id = $calendar->id;
		$tmp->calendar_id = $calendar->calendar_id;
		$tmp->magic_cookie = $calendar->magic_cookie;
		$tmp->username = $calendar->username;
		$tmp->password = $calendar->password;
		$tmp->color = $calendar->color;
		$tmp->name = $calendar->name;

		$event =  $cache->call(array('jsmGCalendarZendHelper', 'internalGetEvent'), $tmp, $eventId);
		$cache->gc();

		// Implement View Level Access
		$user = Factory::getUser();
		if (!$user->authorise('core.admin') && !in_array($calendar->access_content, $user->getAuthorisedViewLevels())) {
			$event->setTitle(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_EVENT_BUSY_LABEL'));
			$event->setContent(null);
			$event->setWhere(null);
			$event->setWho(array());
		}
       
		return $event;
	}

	/**
	 * @return Zend_Gdata_App_Feed|NULL
	 */
	public static function internalGetEvents($calendar, $startDate = null, $endDate = null, $max = 1000, $filter = null, $orderBy = jsmGCalendarZendHelper::ORDER_BY_START_TIME, $pastEvents = false, $sortOrder = jsmGCalendarZendHelper::SORT_ORDER_ASC, $startIndex = 1)
    {
        $app = Factory::getApplication();
        
		try {
			$client = new Zend_Http_Client();

			if(!empty($calendar->username) && !empty($calendar->password)){
				$client = Zend_Gdata_ClientLogin::getHttpClient($calendar->username, $calendar->password, Zend_Gdata_Calendar::AUTH_SERVICE_NAME);
			}

			$service = new Zend_Gdata_Calendar($client);

			$query = $service->newEventQuery();
			$query->setUser($calendar->calendar_id);
			if($calendar->magic_cookie != null){
				$query->setVisibility('private-'.$calendar->magic_cookie);
			}
			$query->setProjection('full');
			$query->setOrderBy($orderBy);
			$query->setSortOrder($sortOrder);
			$query->setSingleEvents('true');
			if(!empty($filter)){
				$query->setQuery($filter);
			}
			if($startDate != null){
				$query->setStartMin(Factory::getDate($startDate)->format('Y-m-d\TH:i:s'));
			}
			if($endDate != null){
				$query->setStartMax(Factory::getDate($endDate)->format('Y-m-d\TH:i:s'));
			}
			if($startDate == null && $endDate == null){
				$query->setFutureEvents($pastEvents ? 'false': 'true');
			}

			$query->setMaxResults($max);
			$query->setStartIndex($startIndex);
			$query->setParam('ctz', 'UTC');
			$query->setParam('hl', jsmGCalendarUtil::getFrLanguage());

			$feed = $service->getFeed($query, 'GCalendar_Feed');
			foreach ($feed as $event) {
				$event->setParam('gcid', $calendar->id);
				$event->setParam('gccolor', $calendar->color);
				$event->setParam('gcname', $calendar->name);
			}
			return $feed;
		} catch (Zend_Gdata_App_Exception $e) {
			JError::raiseWarning(200, $e->getMessage());
			return null;
		}
	}

	/**
	 * @return Zend_Gdata_App_Entry|NULL
	 */
	public static function internalGetEvent($calendar, $eventId) 
    {
        $app = Factory::getApplication();
        
		try {
			$client = new Zend_Http_Client();

			if(!empty($calendar->username) && !empty($calendar->password)){
				$client = Zend_Gdata_ClientLogin::getHttpClient($calendar->username, $calendar->password, Zend_Gdata_Calendar::AUTH_SERVICE_NAME);
			}

			$service = new Zend_Gdata_Calendar($client);

			$query = $service->newEventQuery();
			$query->setUser($calendar->calendar_id);
			if($calendar->magic_cookie != null){
				$query->setVisibility('private-'.$calendar->magic_cookie);
			}
			$query->setProjection('full');
			$query->setEvent($eventId);

			$query->setParam('ctz', 'UTC');
			$query->setParam('hl', jsmGCalendarUtil::getFrLanguage());

			$event = $service->getEntry($query, 'GCalendar_Entry');
			$event->setParam('gcid', $calendar->id);
			$event->setParam('gccolor', $calendar->color);
			$event->setParam('gcname', $calendar->name);
			return $event;
		} catch (Zend_Gdata_App_Exception $e) {
			JError::raiseWarning(200, $e->getMessage());
			return null;
		}
	}

	public static function loadZendClasses() 
    {
        $app = Factory::getApplication();
        
		static $zendLoaded;
		if($zendLoaded == null){
			if (File::exists(JPATH_PLUGINS.DS.'system'.DS.'zend'.DS.'zend.php')) {
				require_once(JPATH_PLUGINS.DS.'system'.DS.'zend'.DS.'zend.php');
				$paths	= explode( PATH_SEPARATOR , get_include_path() );

				if( !in_array( ZEND_PATH, $paths ) ) {
					set_include_path('.'
							. PATH_SEPARATOR . ZEND_PATH
							. PATH_SEPARATOR . get_include_path()
					);
				}
			}
			ini_set("include_path", JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components'. DIRECTORY_SEPARATOR .'com_sportsmanagement' . DIRECTORY_SEPARATOR . 'libraries' . PATH_SEPARATOR . ini_get("include_path"));

			if(!class_exists('Zend_Loader')){
				require_once 'Zend/Loader.php';
			}

			Zend_Loader::loadClass('Zend_Gdata_AuthSub');
			Zend_Loader::loadClass('Zend_Gdata_HttpClient');
			Zend_Loader::loadClass('Zend_Gdata_Calendar');
			Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
			Zend_Loader::loadClass('GCalendar_Feed');
			Zend_Loader::loadClass('GCalendar_Entry');
			$zendLoaded = true;
		}
	}
}
jsmGCalendarZendHelper::loadZendClasses();