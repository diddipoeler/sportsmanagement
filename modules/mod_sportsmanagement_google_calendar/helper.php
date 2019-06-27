<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                GNU General Public License version 2 or later; see LICENSE.txt
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die;

use Joomla\Registry\Registry;


/**
 * ModJSMGoogleCalendarHelper
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class ModJSMGoogleCalendarHelper {

	/**
	 * The google calendar api key
	 *
	 * @var string
	 */
	protected $apiKey;

	/**
	 * The google calendar id
	 *
	 * @var string
	 */
	protected $calendarId;

	
	/**
	 * ModJSMGoogleCalendarHelper::__construct()
	 * 
	 * @param mixed $params
	 * @return void
	 */
	public function __construct(Registry $params)
	{
		$this->apiKey     = $params->get('api_key', null);
		$this->calendarId = $params->get('calendar_id', null);
	}

	
	/**
	 * ModJSMGoogleCalendarHelper::nextEvents()
	 * 
	 * @param mixed $maxEvents
	 * @return
	 */
	public function nextEvents($maxEvents)
	{
		$options = array(
			'timeMin'    => JDate::getInstance()->toISO8601(),
			'orderBy'    => 'startTime',
			'maxResults' => $maxEvents,
		);

		$events = $this->getEvents($options);

		return $this->prepareEvents($events);
	}

	
	/**
	 * ModJSMGoogleCalendarHelper::duration()
	 * 
	 * @param mixed $event
	 * @return
	 */
	public static function duration($event)
	{
		$startDateFormat = isset($event->start->dateTime) ? 'd.m.Y H:i' : 'd.m.Y';
		$endDateFormat   = isset($event->end->dateTime) ? 'd.m.Y H:i' : 'd.m.Y';

		if ($event->startDate == $event->endDate)
		{
			return $event->startDate->format($startDateFormat, true);
		}

		if ($event->startDate->dayofyear == $event->endDate->dayofyear)
		{
			return $event->startDate->format($startDateFormat, true) . ' - ' . $event->endDate->format('H:i', true);
		}

		return $event->startDate->format($startDateFormat, true) . ' - ' . $event->endDate->format($endDateFormat, true);
	}


	/**
	 * ModJSMGoogleCalendarHelper::getEvents()
	 * 
	 * @param mixed $options
	 * @return
	 */
	protected function getEvents($options)
	{
		$defaultOptions = array(
			'singleEvents' => 'true',
		);

		$options = array_merge($defaultOptions, $options);

		// Create an instance of a default Http object.
		$http = JHttpFactory::getHttp();
		$url  = 'https://www.googleapis.com/calendar/v3/calendars/'
			. urlencode($this->calendarId) . '/events?key=' . urlencode($this->apiKey)
			. '&' . http_build_query($options);

		$response = $http->get($url);
		$data     = json_decode($response->body);

		if ($data && isset($data->items))
		{
			return $data->items;
		}
		elseif ($data)
		{
			return array();
		}

		throw new UnexpectedValueException("Unexpected data received from Google: `{$response->body}`.");
	}

	
	/**
	 * ModJSMGoogleCalendarHelper::prepareEvents()
	 * 
	 * @param mixed $events
	 * @return
	 */
	protected function prepareEvents($events)
	{
		foreach ($events AS $i => $event)
		{
			$events[$i] = $this->prepareEvent($event);
		}

		return $events;
	}


	/**
	 * ModJSMGoogleCalendarHelper::prepareEvent()
	 * 
	 * @param mixed $event
	 * @return
	 */
	protected function prepareEvent($event)
	{
		$event->startDate = $this->unifyDate($event->start);
		$event->endDate   = $this->unifyDate($event->end);

		return $event;
	}

	
	/**
	 * ModJSMGoogleCalendarHelper::unifyDate()
	 * 
	 * @param mixed $date
	 * @return
	 */
	protected function unifyDate($date)
	{
		$timeZone = (isset($date->timezone)) ? $date->timezone : null;

		if (isset($date->dateTime))
		{
			return JDate::getInstance($date->dateTime, $timeZone);
		}

		return JDate::getInstance($date->date, $timeZone);
	}
}