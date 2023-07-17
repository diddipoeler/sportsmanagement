<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_google_calendar
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Http\HttpFactory;
use Joomla\Registry\Registry;

/**
 * ModJSMGoogleCalendarHelper
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
 */
class ModJSMGoogleCalendarHelper
{
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
	 * @param   mixed  $params
	 *
	 * @return void
	 */
	public function __construct(Registry $params)
	{
		$this->apiKey     = $params->get('api_key', null);
		$this->calendarId = $params->get('calendar_id', null);
	}

	/**
	 * ModJSMGoogleCalendarHelper::duration()
	 *
	 * @param   mixed  $event
	 *
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
	 * ModJSMGoogleCalendarHelper::nextEvents()
	 *
	 * @param   mixed  $maxEvents
	 *
	 * @return
	 */
	public function nextEvents($maxEvents)
	{
		$options = array(
			'timeMin'    => Date::getInstance()->toISO8601(),
			'orderBy'    => 'startTime',
			'maxResults' => $maxEvents,
		);

		$events = $this->getEvents($options);

		return $this->prepareEvents($events);
	}

	/**
	 * ModJSMGoogleCalendarHelper::getEvents()
	 *
	 * @param   mixed  $options
	 *
	 * @return
	 */
	protected function getEvents($options)
	{
		$defaultOptions = array(
			'singleEvents' => 'true',
		);

		$options = array_merge($defaultOptions, $options);

		// Create an instance of a default Http object.
		$http = HttpFactory::getHttp();
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
	 * @param   mixed  $events
	 *
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
	 * @param   mixed  $event
	 *
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
	 * @param   mixed  $date
	 *
	 * @return
	 */
	protected function unifyDate($date)
	{
		$timeZone = (isset($date->timezone)) ? $date->timezone : null;

		if (isset($date->dateTime))
		{
			return Date::getInstance($date->dateTime, $timeZone);
		}

		return Date::getInstance($date->date, $timeZone);
	}
}
