<?php
namespace Diddipoeler\Module\SportsManagementGoogleCalendar\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Date\Date;
use Joomla\Http\HttpFactory;
use Joomla\Registry\Registry;

final class GoogleCalendarHelper
{
    public function getData(Registry $params, object $module, CMSApplicationInterface $app): array
    {
        $apiKey = trim((string) $params->get('api_key', ''));
        $calendarId = trim((string) $params->get('calendar_id', ''));

        if ($apiKey === '' || $calendarId === '') {
            return ['events' => []];
        }

        $maxEvents = max(1, (int) $params->get('max_list_events', 5));
        $lifetime = max(1, (int) $params->get('api_cache_time', 60));
        $cacheFactory = \Joomla\CMS\Factory::getContainer()->get(CacheControllerFactoryInterface::class);
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

        return ['events' => is_array($events) ? $events : []];
    }

    /**
     * Callback-cache entry point. It is public because Joomla's callback cache invokes it.
     *
     * @return array<int, object>
     */
    public function loadNextEvents(string $apiKey, string $calendarId, int $maxEvents): array
    {
        $options = [
            'timeMin' => Date::getInstance()->toISO8601(),
            'orderBy' => 'startTime',
            'maxResults' => $maxEvents,
            'singleEvents' => 'true',
        ];

        $http = HttpFactory::getHttp();
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

        if (!$data) {
            throw new \UnexpectedValueException('Unexpected data received from Google Calendar.');
        }

        if (!isset($data->items) || !is_array($data->items)) {
            return [];
        }

        return array_map([$this, 'prepareEvent'], $data->items);
    }

    public static function duration(object $event): string
    {
        if (!isset($event->startDate, $event->endDate)) {
            return '';
        }

        $startDateFormat = isset($event->start->dateTime) ? 'd.m.Y H:i' : 'd.m.Y';
        $endDateFormat = isset($event->end->dateTime) ? 'd.m.Y H:i' : 'd.m.Y';

        if ($event->startDate == $event->endDate) {
            return $event->startDate->format($startDateFormat, true);
        }

        if ($event->startDate->dayofyear == $event->endDate->dayofyear) {
            return $event->startDate->format($startDateFormat, true)
                . ' - ' . $event->endDate->format('H:i', true);
        }

        return $event->startDate->format($startDateFormat, true)
            . ' - ' . $event->endDate->format($endDateFormat, true);
    }

    public function prepareEvent(object $event): object
    {
        $event->startDate = $this->unifyDate($event->start ?? null);
        $event->endDate = $this->unifyDate($event->end ?? null);
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

        $timeZone = isset($date->timeZone) ? (string) $date->timeZone : null;

        if (!empty($date->dateTime)) {
            return Date::getInstance((string) $date->dateTime, $timeZone);
        }

        if (!empty($date->date)) {
            return Date::getInstance((string) $date->date, $timeZone);
        }

        throw new \UnexpectedValueException('Google Calendar event has an invalid date.');
    }
}
