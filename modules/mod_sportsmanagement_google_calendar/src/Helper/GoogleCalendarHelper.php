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
use Joomla\CMS\Log\Log;
use Joomla\Http\HttpFactory;
use Joomla\Registry\Registry;

final class GoogleCalendarHelper
{
    /**
     * Load Google Calendar events for the frontend module.
     *
     * Validates the API key and calendar identifier, bounds event count and
     * cache lifetime, and uses Joomla's callback cache. Excessive identifier
     * sizes are rejected before forming a URL or cache entry. A failed HTTP
     * or cache request produces no events without exposing the API key.
     *
     * @param Registry $params Google Calendar API and display configuration.
     * @param object $module Current Joomla module instance.
     * @param CMSApplicationInterface $app Current Joomla application.
     * @param CacheControllerFactoryInterface $cacheFactory Joomla cache controller factory.
     * @return array{events: array<int, object>} Validated event records or an empty list.
     */
    public function getData(
        Registry $params,
        object $module,
        CMSApplicationInterface $app,
        CacheControllerFactoryInterface $cacheFactory
    ): array {
        $rawApiKey = $params->get('api_key', '');
        $rawCalendarId = $params->get('calendar_id', '');

        // Legacy parameters may contain arrays after an invalid form submission.
        // Do not cast them to strings or attempt an outbound API request.
        if (!is_string($rawApiKey) || !is_string($rawCalendarId)) {
            return ['events' => []];
        }

        $apiKey = trim($rawApiKey);
        $calendarId = trim($rawCalendarId);

        // Reject control characters in remote API identifiers before using
        // them in the outbound URL or the Joomla callback-cache key.
        if (preg_match('/[\x00-\x1F\x7F]/', $apiKey . $calendarId)) {
            return ['events' => []];
        }

        if ($apiKey === '' || $calendarId === ''
            || strlen($apiKey) > 256 || strlen($calendarId) > 1024) {
            return ['events' => []];
        }

        // Google Calendar Events.list accepts at most 2500 results per request.
        $rawMaxEvents = $params->get('max_list_events', 5);
        $maxEvents = self::normalisePositiveLimit($rawMaxEvents, 5, 2500);
        // Keep the API cache bounded even when a legacy module parameter is
        // corrupted or configured to an excessively large lifetime.
        $rawLifetime = $params->get('api_cache_time', 60);
        $lifetime = self::normalisePositiveLimit($rawLifetime, 60, 10080);
        try {
            $cache = $cacheFactory->createCacheController('callback', [
                'caching' => true,
                'lifetime' => $lifetime,
                'defaultgroup' => 'mod_sportsmanagement_google_calendar',
            ]);
            $events = $cache->call(
                [$this, 'loadNextEvents'],
                $apiKey,
                $calendarId,
                $maxEvents
            );
        } catch (\Throwable $e) {
            // HTTP, API and cache failures must not break the frontend page.
            // Report the problem to Joomla without exposing the calendar key.
            Log::add(__METHOD__ . ': Google Calendar data is unavailable (' . get_class($e) . ')', Log::ERROR, 'jsmerror');
            return ['events' => []];
        }

        return ['events' => self::normaliseRenderableEvents($events, $maxEvents)];
    }

    /**
     * Validate cached Google Calendar events before passing them to the layout.
     *
     * The callback cache can contain old or malformed entries. Only object
     * records are rendered, and all fields used as text or URLs must be
     * strings so PHP 8 does not convert arrays to "Array" in the template.
     * Overlong cached strings are replaced by empty values to bound output
     * work even if the callback cache contains corrupted records.
     * The result is bounded to the configured maximum event count.
     *
     * @param mixed $events Data supplied by the Joomla callback cache.
     * @param int $maximum Maximum number of events rendered by the module.
     * @return array<int, object> Safe event records with scalar display fields.
     */
    private static function normaliseRenderableEvents(mixed $events, int $maximum): array
    {
        if (!is_array($events)) {
            return [];
        }

        $result = [];

        foreach ($events as $event) {
            if (!is_object($event)) {
                continue;
            }

            $safeEvent = clone $event;

            foreach (['htmlLink', 'summary', 'location', 'jsmStartIso', 'jsmEndIso', 'jsmDuration'] as $field) {
                $value = $safeEvent->{$field} ?? '';
                $safeEvent->{$field} = is_string($value) && strlen($value) <= 8192
                    ? $value
                    : '';
            }

            $result[] = $safeEvent;

            if (count($result) >= $maximum) {
                break;
            }
        }

        return $result;
    }

    /**
     * Validate a bounded positive integer parameter.
     *
     * Accepts integer and zero-padded decimal string values while rejecting malformed, boolean, array and overflowing inputs. The configured fallback is used when invalid.
     *
     * @param mixed $value Raw module setting.
     * @param int $default Fallback positive value.
     * @param int $maximum Maximum accepted value.
     * @return int Validated and clamped positive integer.
     */
    private static function normalisePositiveLimit(mixed $value, int $default, int $maximum): int
    {
        if (!is_int($value) && (!is_string($value) || !preg_match('/^[0-9]+$/D', $value))) {
            return $default;
        }

        // PHP's integer validator rejects leading-zero strings such as "0002".
        // These are valid legacy module settings, so normalize them first.
        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $number === false ? $default : min($maximum, max(1, $number));
    }

    /**
     * Fetch and parse the next events through Joomla's callback cache.
     *
     * Queries the Google Calendar API over HTTPS with redirects disabled, validates the response and parses individual events without breaking the entire result on malformed dates. The number of accepted events remains bounded.
     *
     * @param string $apiKey Google Calendar API credential.
     * @param string $calendarId Calendar identifier from module settings.
     * @param int $maxEvents Maximum number of events to retrieve.
     * @return array<int, object> Parsed Google Calendar event records.
     * @throws \RuntimeException When the HTTP response indicates failure.
     * @throws \UnexpectedValueException When the API response is malformed.
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

        // The API key is embedded in the request query. Do not forward it
        // to a different host through an HTTP redirect.
        $http = (new HttpFactory())->getHttp(['follow_location' => false], ['Curl', 'Stream']);
        $url = 'https://www.googleapis.com/calendar/v3/calendars/'
            . rawurlencode($calendarId)
            . '/events?key=' . rawurlencode($apiKey)
            . '&' . http_build_query($options);

        // Bound the outbound request so an unresponsive calendar endpoint
        // cannot hold up the Joomla page indefinitely.
        $response = $http->get($url, [], 10);
        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($status < 200 || $status >= 300) {
            throw new \RuntimeException('Google Calendar request failed with HTTP status ' . $status);
        }

        $data = json_decode($body);

        if (json_last_error() !== JSON_ERROR_NONE || !is_object($data)) {
            throw new \UnexpectedValueException('Unexpected data received from Google Calendar.');
        }

        // The API can return an error object with HTTP 200, for example
        // when a proxy rewrites the upstream response status.
        if (isset($data->error)) {
            throw new \UnexpectedValueException('Google Calendar API returned an error.');
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
            } catch (\Throwable) {
                // Invalid remote dates/time zones may also throw from Joomla's
                // date parser. Skip that event and retain the valid entries.
                continue;
            }

            if (count($events) >= $maxEvents) {
                break;
            }
        }

        return $events;
    }

    /**
     * Format the human-readable time span of a calendar event.
     *
     * Uses the exclusive end date for all-day Google Calendar events and formats same-day and multi-day timed events appropriately.
     *
     * @param object $event Prepared event with Joomla start and end dates.
     * @return string Human-readable duration or an empty string for missing dates.
     */
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

    /**
     * Validate and enrich one remote Google Calendar event.
     *
     * Parses start and end values, rejects negative or empty all-day ranges, and adds ISO 8601 timestamps and a formatted duration for the template.
     *
     * @param object $event Google Calendar API event record.
     * @return object Event extended with jsmStartIso, jsmEndIso and jsmDuration.
     * @throws \UnexpectedValueException On malformed or reversed date intervals.
     */
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

    /**
     * Parse one Google Calendar time value as a Joomla date.
     *
     * Checks all-day calendar dates and RFC3339 date-time values, including bounds for time zone offsets and invalid date components.
     *
     * @param object|null $date Google Calendar start or end object.
     * @return Date Normalised Joomla calendar date.
     * @throws \UnexpectedValueException When the value or its time zone is invalid.
     */
    private function unifyDate(?object $date): Date
    {
        if ($date === null) {
            throw new \UnexpectedValueException('Google Calendar event has no date information.');
        }

        $timeZone = $date->timeZone ?? null;

        if ($timeZone !== null && (!is_string($timeZone) || trim($timeZone) === '')) {
            throw new \UnexpectedValueException('Google Calendar event has an invalid time zone.');
        }

        if (is_string($timeZone)) {
            try {
                $timeZone = new \DateTimeZone($timeZone);
            } catch (\Exception) {
                throw new \UnexpectedValueException('Google Calendar event has an unknown time zone.');
            }
        }

        if (isset($date->dateTime)) {
            if (!is_string($date->dateTime)
                || !preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/D', $date->dateTime, $matches)
                || !checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1])
                || (int) $matches[4] > 23
                || (int) $matches[5] > 59
                || (int) $matches[6] > 59
                || (preg_match('/([+-])(\d{2}):(\d{2})$/D', $date->dateTime, $offset)
                    && ((int) $offset[2] > 14
                        || ((int) $offset[2] === 14 && (int) $offset[3] !== 0)
                        || (int) $offset[3] > 59))) {
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
