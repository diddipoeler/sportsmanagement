<?php
/**
 * Native Joomla 5/6 helper for the SportsManagement Google Calendar module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementGoogleCalendar\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Date\Date;
use Joomla\Http\HttpFactory;
use Joomla\Registry\Registry;

final class GoogleCalendarHelper
{
    public function getData(
        Registry $params,
        object $module,
        CMSApplicationInterface $app,
        CacheControllerFactoryInterface $cacheFactory
    ): array {
        $apiKey = trim((string) $params->get('api_key', ''));
        $calendarId = trim((string) $params->get('calendar_id', ''));

        if ($apiKey === '' || $calendarId === '') {
            return ['events' => []];
        }

        // Google Calendar Events.list accepts at most 2500 results per request.
        $maxEvents = min(2500, max(1, (int) $params->get('max_list_events', 5)));
        $lifetime = max(1, (int) $params->get('api_cache_time', 60));
        $cache = $cacheFactory->createCacheController('callback', [
            'caching' => true,
            'lifetime' => $lifetime,
            'defaultgroup' => 'mod_sportsmanagement_google_calendar',
        ]);

        try {
            $events = $cache->call(
                [$this, 'loadNextEvents'],
                $apiKey,
                $calendarId,
                $maxEvents
            );
        } catch (\Exception) {
            // Temporary HTTP/API/cache failures must not break the page.
            // Do not cache a failed request as an empty event list.
            return ['events' => []];
        }

        return ['events' => is_array($events) ? $events : []];
    }

    /**
     * Callback-cache entry point. It is public because Joomla's callback cache invokes it.
     *
     * @return array<int, object>
     */
    public function loadNextEvents(string $apiKey, string $calendarId, int $maxEvents): array
    {
        // Guard the public callback as well as the module parameter. Legacy
        // callers may invoke it directly with an out-of-range event count.
        $maxEvents = min(2500, max(1, $maxEvents));

        $options = [
            'timeMin' => Date::getInstance()->toISO8601(),
            'orderBy' => 'startTime',
            'maxResults' => $maxEvents,
            'singleEvents' => 'true',
        ];

        $http = (new HttpFactory())->getHttp();
        $url = 'https://www.googleapis.com/calendar/v3/calendars/'
            . rawurlencode($calendarId)
            . '/events?key=' . rawurlencode($apiKey)
            . '&' . http_build_query($options);

        $response = $http->get($url);
        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($status < 200 || $status >= 300) {
            throw new \RuntimeException('Google Calendar request failed with HTTP status ' . $status);
        }

        $data = json_decode($body);

        if (json_last_error() !== JSON_ERROR_NONE || !is_object($data)) {
            throw new \UnexpectedValueException('Unexpected data received from Google Calendar.');
        }

        if (!isset($data->items) || !is_array($data->items)) {
            throw new \UnexpectedValueException('Google Calendar response contains no event list.');
        }

        $events = [];

        foreach ($data->items as $event) {
            if (!is_object($event)) {
                continue;
            }

            try {
                $events[] = $this->prepareEvent($event);
            } catch (\Exception) {
                // Invalid remote dates/time zones may also throw from Joomla's
                // date parser. Skip that event and retain the valid entries.
                continue;
            }
        }

        return $events;
    }

    public static function duration(object $event): string
    {
        if (!isset($event->startDate, $event->endDate)) {
            return '';
        }

        // Google's all-day end.date is exclusive. Show the last included
        // calendar day rather than displaying an extra day to visitors.
        if (isset($event->start->date, $event->end->date)
            && !isset($event->start->dateTime, $event->end->dateTime)) {
            $lastDay = clone $event->endDate;
            $lastDay->modify('-1 day');

            if ($lastDay < $event->startDate) {
                return '';
            }

            $start = $event->startDate->format('d.m.Y', true);
            $end = $lastDay->format('d.m.Y', true);

            return $start === $end ? $start : $start . ' - ' . $end;
        }

        $startDateFormat = isset($event->start->dateTime) ? 'd.m.Y H:i' : 'd.m.Y';
        $endDateFormat = isset($event->end->dateTime) ? 'd.m.Y H:i' : 'd.m.Y';

        if ($event->startDate == $event->endDate) {
            return $event->startDate->format($startDateFormat, true);
        }

        if ($event->startDate->format('Y-m-d') === $event->endDate->format('Y-m-d')
            && isset($event->start->dateTime, $event->end->dateTime)) {
            return $event->startDate->format($startDateFormat, true)
                . ' - ' . $event->endDate->format('H:i', true);
        }

        return $event->startDate->format($startDateFormat, true)
            . ' - ' . $event->endDate->format($endDateFormat, true);
    }

    public function prepareEvent(object $event): object
    {
        // Remote JSON is untrusted: Google event date fields must be objects.
        // Reject unexpected scalar/array values before calling the typed parser.
        if (!isset($event->start, $event->end)
            || !is_object($event->start) || !is_object($event->end)) {
            throw new \UnexpectedValueException('Google Calendar event has invalid date fields.');
        }

        $event->startDate = $this->unifyDate($event->start);
        $event->endDate = $this->unifyDate($event->end);
        if ($event->endDate < $event->startDate) {
            throw new \UnexpectedValueException('Google Calendar event ends before it starts.');
        }

        // All-day event end dates are exclusive. A matching start/end day
        // represents an empty interval, not a valid one-day event.
        if (isset($event->start->date, $event->end->date)
            && !isset($event->start->dateTime, $event->end->dateTime)
            && $event->endDate <= $event->startDate) {
            throw new \UnexpectedValueException('Google Calendar all-day event has an empty date range.');
        }

        $event->jsmStartIso = $event->startDate->toISO8601(true);
        $event->jsmEndIso = $event->endDate->toISO8601(true);
        $event->jsmDuration = self::duration($event);

        return $event;
    }

    private function unifyDate(?object $date): Date
    {
        if ($date === null) {
            throw new \UnexpectedValueException('Google Calendar event has no date information.');
        }

        $timeZone = $date->timeZone ?? null;

        if ($timeZone !== null && (!is_string($timeZone) || trim($timeZone) === '')) {
            throw new \UnexpectedValueException('Google Calendar event has an invalid time zone.');
        }

        if (isset($date->dateTime)) {
            if (!is_string($date->dateTime)
                || !preg_match('/^(\d{4})-(\d{2})-(\d{2})T/', $date->dateTime, $matches)
                || !checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1])) {
                throw new \UnexpectedValueException('Google Calendar event has an invalid date-time.');
            }

            return Date::getInstance($date->dateTime, $timeZone);
        }

        if (isset($date->date)) {
            if (!is_string($date->date)
                || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date->date)) {
                throw new \UnexpectedValueException('Google Calendar event has an invalid all-day date.');
            }

            [$year, $month, $day] = array_map('intval', explode('-', $date->date));
            if (!checkdate($month, $day, $year)) {
                throw new \UnexpectedValueException('Google Calendar event has an impossible all-day date.');
            }

            return Date::getInstance($date->date, $timeZone);
        }

        throw new \UnexpectedValueException('Google Calendar event has an invalid date.');
    }
}
