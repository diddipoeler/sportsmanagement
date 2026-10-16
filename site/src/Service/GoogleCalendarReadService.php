<?php
/**
 * Native Joomla 5/6 read-only Google Calendar adapter.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Read-only Google Calendar adapter used by the public JSON feed and event view.
 *
 * The administrator OAuth importer stores the refresh token and client
 * credentials in the calendar row's params field. This service consumes that
 * data directly and deliberately does not depend on the removed GCalendar
 * Zend/DB utility classes.
 */
final class GoogleCalendarReadService
{
    // Bound remote requests even if the Google API keeps returning pages
    // containing no usable events or repeats pagination cursors.
    private const MAX_CALENDAR_PAGES = 10;
    private const MAX_SELECTED_CALENDARS = 50;

    private bool $googleClientLoaded = false;

    public function __construct(
        private readonly DatabaseInterface $db,
        private readonly CMSApplicationInterface $app
    ) {
    }

    /**
     * Return normalized events from the requested, access-authorized calendars.
     *
     * @param array<int> $calendarIds Internal #__sportsmanagement_gcalendar ids.
     * @return array<int, array<string, mixed>>
     */
    public function getEvents(array $calendarIds, ?int $start = null, ?int $end = null): array
    {
        // Reversed or empty ranges should not unexpectedly fetch the entire
        // calendar when timeMax would otherwise be omitted.
        if ($start !== null && $end !== null && $end <= $start) {
            return [];
        }

        $calendars = $this->getCalendars($calendarIds);

        if (!$calendars || !$this->loadGoogleClient()) {
            return [];
        }

        $events = [];

        foreach ($calendars as $calendar) {
            foreach ($this->fetchCalendarEvents($calendar, $start, $end) as $event) {
                $events[] = $event;
            }
        }

        // ISO calendar strings with different UTC offsets do not sort chronologically.
        usort($events, self::compareEventStarts(...));

        return $events;
    }

    /**
     * Compare calendar instants, not lexical date strings with UTC offsets.
     *
     * @param array<string, mixed> $left
     * @param array<string, mixed> $right
     */
    private static function compareEventStarts(array $left, array $right): int
    {
        $leftStart = (string) ($left['start'] ?? '');
        $rightStart = (string) ($right['start'] ?? '');
        $leftTime = strtotime($leftStart);
        $rightTime = strtotime($rightStart);

        if ($leftTime !== false && $rightTime !== false) {
            return $leftTime <=> $rightTime;
        }

        return strcmp($leftStart, $rightStart);
    }

    /**
     * Return one normalized event if the calendar and event are accessible.
     *
     * @return array<string, mixed>|null
     */
    public function getEvent(int $calendarId, string $eventId): ?array
    {
        if ($calendarId <= 0 || $eventId === '' || strlen($eventId) > 512
            || preg_match('/[\\x00-\\x1F\\x7F]/', $eventId)) {
            return null;
        }

        $calendars = $this->getCalendars([$calendarId]);

        if (!$calendars || !$this->loadGoogleClient()) {
            return null;
        }

        $calendar = $calendars[0];
        $service = $this->createService($calendar);

        if ($service === null) {
            return null;
        }

        try {
            $googleEvent = $service->events->get((string) $calendar->calendar_id, $eventId);

            return $this->normalizeEvent($calendar, $googleEvent);
        } catch (\Throwable $e) {
            Log::add(
                'Google Calendar event lookup failed for calendar ' . (int) $calendar->id . ' (' . get_class($e) . ').',
                Log::WARNING,
                'com_sportsmanagement'
            );

            return null;
        }
    }

    /**
     * @param array<int> $calendarIds
     * @return array<int, object>
     */
    public function getCalendars(array $calendarIds): array
    {
        // Cap selections before constructing the SQL IN clause or making
        // per-calendar Google API requests.
        $calendarIds = array_slice(array_values(array_unique(array_filter(
            $calendarIds,
            static fn (mixed $id): bool => is_int($id) && $id > 0
        ))), 0, self::MAX_SELECTED_CALENDARS);

        // Never turn an empty client-side selection into "all calendars".
        if (!$calendarIds) {
            return [];
        }

        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('id'),
                $this->db->quoteName('name'),
                $this->db->quoteName('calendar_id'),
                $this->db->quoteName('color'),
                $this->db->quoteName('params'),
                $this->db->quoteName('access'),
                $this->db->quoteName('access_content'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_gcalendar'))
            ->whereIn($this->db->quoteName('id'), $calendarIds, ParameterType::INTEGER);

        $user = $this->app->getIdentity();

        // Missing identity must never bypass the calendar access-level filter.
        if ($user === null) {
            return [];
        }

        if (!$user->authorise('core.admin', 'com_sportsmanagement')) {
            $levels = array_values(array_unique(array_filter(array_map(
                'intval',
                $user->getAuthorisedViewLevels()
            ))));

            if (!$levels) {
                return [];
            }

            $query->whereIn($this->db->quoteName('access'), $levels, ParameterType::INTEGER);
            $query->whereIn($this->db->quoteName('access_content'), $levels, ParameterType::INTEGER);
        }

        $query->order($this->db->quoteName('name') . ' ASC');
        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchCalendarEvents(object $calendar, ?int $start, ?int $end): array
    {
        $service = $this->createService($calendar);

        if ($service === null) {
            return [];
        }

        $calendarAddress = trim((string) ($calendar->calendar_id ?? ''));

        if ($calendarAddress === '') {
            return [];
        }

        $options = [
            'maxResults' => 1000,
            'orderBy' => 'startTime',
            'showDeleted' => false,
            'singleEvents' => true,
        ];

        if ($start !== null && $start > 0) {
            $options['timeMin'] = $this->rfc3339($start);
        }

        if ($end !== null && $end > 0 && ($start === null || $end > $start)) {
            $options['timeMax'] = $this->rfc3339($end);
        }

        $events = [];
        $pageToken = null;
        $seenTokens = [];
        $pages = 0;

        try {
            do {
                if ($pageToken !== null) {
                    if (isset($seenTokens[$pageToken])) {
                        break;
                    }

                    $seenTokens[$pageToken] = true;
                }

                $requestOptions = $options;

                if ($pageToken !== null) {
                    $requestOptions['pageToken'] = $pageToken;
                }

                ++$pages;

                $collection = $service->events->listEvents($calendarAddress, $requestOptions);
                $items = method_exists($collection, 'getItems') ? $collection->getItems() : ($collection->items ?? []);

                foreach ($items ?: [] as $googleEvent) {
                    $event = $this->normalizeEvent($calendar, $googleEvent);

                    if ($event !== null) {
                        $events[] = $event;
                    }
                }

                $nextToken = method_exists($collection, 'getNextPageToken')
                    ? $collection->getNextPageToken()
                    : ($collection->nextPageToken ?? null);
                $pageToken = is_string($nextToken) && $nextToken !== '' ? $nextToken : null;

                // The 1000-event limit alone cannot bound pagination when
                // malformed upstream events are discarded during normalisation.
                if (count($events) >= 1000) {
                    $events = array_slice($events, 0, 1000);
                    break;
                }
            } while ($pageToken !== null && $pages < self::MAX_CALENDAR_PAGES);
        } catch (\Throwable $e) {
            Log::add(
                'Google Calendar feed failed for calendar ' . (int) ($calendar->id ?? 0) . ' (' . get_class($e) . ').',
                Log::WARNING,
                'com_sportsmanagement'
            );
        }

        return $events;
    }

    private function createService(object $calendar): ?object
    {
        $rawParams = $calendar->params ?? '';
        if (!is_string($rawParams)) {
            return null;
        }

        $params = new Registry();

        try {
            $params->loadString($rawParams);
        } catch (\Throwable) {
            return null;
        }

        // Registry data originates from the database and may be malformed.
        // Never turn an array or object into the literal string "Array" or
        // pass it as an OAuth credential to the Google client.
        $rawClientId = $params->get('client-id', '');
        $rawClientSecret = $params->get('client-secret', '');
        $rawRefreshToken = $params->get('refreshToken', '');
        $rawCalendarAddress = $calendar->calendar_id ?? $params->get('calendarId', '');

        if (!is_string($rawClientId) || !is_string($rawClientSecret)
            || !is_string($rawRefreshToken) || !is_string($rawCalendarAddress)) {
            return null;
        }

        $clientId = trim($rawClientId);
        $clientSecret = trim($rawClientSecret);
        $refreshToken = trim($rawRefreshToken);
        $calendarAddress = trim($rawCalendarAddress);

        if ($calendarAddress === '' || $clientId === '' || $clientSecret === '' || $refreshToken === '') {
            Log::add(
                'Google Calendar credentials are incomplete for calendar ' . (int) ($calendar->id ?? 0) . '.',
                Log::WARNING,
                'com_sportsmanagement'
            );

            return null;
        }

        try {
            $client = new \Google_Client(['ioFileCache_directory' => (string) $this->app->get('tmp_path')]);
            $client->setApplicationName('JSMCalendar');
            $client->setClientId($clientId);
            $client->setClientSecret($clientSecret);

            if (method_exists($client, 'setScopes')) {
                $client->setScopes(['https://www.googleapis.com/auth/calendar.readonly']);
            }

            if (method_exists($client, 'setAccessType')) {
                $client->setAccessType('offline');
            }

            if (method_exists($client, 'fetchAccessTokenWithRefreshToken')) {
                $result = $client->fetchAccessTokenWithRefreshToken($refreshToken);

                if (is_array($result) && !empty($result['error'])) {
                    throw new \RuntimeException((string) $result['error']);
                }
            } elseif (method_exists($client, 'refreshToken')) {
                $client->refreshToken($refreshToken);
            } else {
                throw new \RuntimeException('Installed Google API client cannot refresh OAuth tokens.');
            }

            return new \Google_Service_Calendar($client);
        } catch (\Throwable $e) {
            Log::add(
                'Google Calendar authentication failed for calendar ' . (int) ($calendar->id ?? 0) . ' (' . get_class($e) . ').',
                Log::WARNING,
                'com_sportsmanagement'
            );

            return null;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizeEvent(object $calendar, object $googleEvent): ?array
    {
        $eventId = trim((string) $this->callOrProperty($googleEvent, 'getId', 'id'));
        $startObject = $this->callOrProperty($googleEvent, 'getStart', 'start');
        $endObject = $this->callOrProperty($googleEvent, 'getEnd', 'end');

        if ($eventId === '' || !is_object($startObject) || !is_object($endObject)) {
            return null;
        }

        $startDateTime = trim((string) $this->callOrProperty($startObject, 'getDateTime', 'dateTime'));
        $startDate = trim((string) $this->callOrProperty($startObject, 'getDate', 'date'));
        $endDateTime = trim((string) $this->callOrProperty($endObject, 'getDateTime', 'dateTime'));
        $endDate = trim((string) $this->callOrProperty($endObject, 'getDate', 'date'));
        $allDay = $startDateTime === '' && $startDate !== '';
        $start = $allDay ? $startDate : $startDateTime;
        $end = $allDay ? $endDate : $endDateTime;

        if ($start === '') {
            return null;
        }

        if ($end === '') {
            $end = $start;
        }

        return [
            'id' => $eventId,
            'gcid' => (int) ($calendar->id ?? 0),
            'calendarId' => (string) ($calendar->calendar_id ?? ''),
            'calendarName' => (string) ($calendar->name ?? ''),
            'title' => (string) $this->callOrProperty($googleEvent, 'getSummary', 'summary'),
            'description' => (string) $this->callOrProperty($googleEvent, 'getDescription', 'description'),
            'location' => (string) $this->callOrProperty($googleEvent, 'getLocation', 'location'),
            'htmlLink' => (string) $this->callOrProperty($googleEvent, 'getHtmlLink', 'htmlLink'),
            'start' => $start,
            'end' => $end,
            'allDay' => $allDay,
            'color' => $this->normalizeColor((string) ($calendar->color ?? '')),
        ];
    }

    private function loadGoogleClient(): bool
    {
        if ($this->googleClientLoaded) {
            return true;
        }

        if (!class_exists('Google_Client') || !class_exists('Google_Service_Calendar')) {
            $autoloaders = [
                JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/libraries/google-php/vendor/autoload.php',
                JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/libraries/google-php/autoload.php',
            ];

            foreach ($autoloaders as $autoloader) {
                if (is_file($autoloader)) {
                    require_once $autoloader;
                    break;
                }
            }
        }

        $this->googleClientLoaded = class_exists('Google_Client') && class_exists('Google_Service_Calendar');

        if (!$this->googleClientLoaded) {
            Log::add(
                'Google API client is not available for the SportsManagement calendar feed.',
                Log::WARNING,
                'com_sportsmanagement'
            );
        }

        return $this->googleClientLoaded;
    }

    private function callOrProperty(object $object, string $method, string $property): mixed
    {
        if (method_exists($object, $method)) {
            return $object->{$method}();
        }

        return $object->{$property} ?? null;
    }

    private function normalizeColor(string $color): string
    {
        $color = strtoupper(ltrim(trim($color), '#'));

        return preg_match('/^[0-9A-F]{6}$/', $color) ? '#' . $color : '#135CAE';
    }

    private function rfc3339(int $timestamp): string
    {
        return (new \DateTimeImmutable('@' . $timestamp))
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format(DATE_RFC3339);
    }
}
