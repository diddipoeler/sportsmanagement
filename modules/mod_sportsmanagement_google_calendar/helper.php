<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Google Calendar module helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementGoogleCalendar\Site\Helper\GoogleCalendarHelper;
use Joomla\CMS\Log\Log;
use Joomla\Registry\Registry;

$nativeDependencies = [
    GoogleCalendarHelper::class => __DIR__ . '/src/Helper/GoogleCalendarHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(GoogleCalendarHelper::class)) {
    throw new \RuntimeException('SportsManagement native Google Calendar module helper could not be loaded.', 500);
}

if (!class_exists('ModJSMGoogleCalendarHelper', false)) {
    final class ModJSMGoogleCalendarHelper
    {
        protected string $apiKey = '';
        protected string $calendarId = '';

        /**
         * Initialise the legacy Google Calendar helper from module settings.
         * Converts incoming settings to Joomla Registry, accepts only string
         * API keys and calendar IDs, and rejects control characters and
         * oversized credentials before later event retrieval.
         * @param Registry|array|null $params Settings supplied by the module.
         * @return void
         */
        public function __construct($params = null)
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) ($params ?? []));
            $rawApiKey = $registry->get('api_key', '');
            $rawCalendarId = $registry->get('calendar_id', '');

            if (!is_string($rawApiKey) || !is_string($rawCalendarId)) {
                return;
            }

            $apiKey = trim($rawApiKey);
            $calendarId = trim($rawCalendarId);

            if (strlen($apiKey) > 256 || strlen($calendarId) > 1024
                || preg_match('/[\x00-\x1F\x7F]/', $apiKey . $calendarId)) {
                return;
            }

            $this->apiKey = $apiKey;
            $this->calendarId = $calendarId;
        }

        /**
         * Format the duration of a Google Calendar event.
         * Delegates to the native Joomla 5/6 GoogleCalendarHelper method so
         * legacy template callers use the same event duration format.
         * @param object $event Calendar event returned by the integration.
         * @return string Formatted duration of the event.
         */
        public static function duration(object $event): string
        {
            return GoogleCalendarHelper::duration($event);
        }

        /**
         * Retrieve the upcoming calendar entries for the configured calendar.
         * Returns an empty list when credentials are absent, clamps the event
         * limit to 1 through 2500 and delegates fetching to the native helper.
         * Rejects nested, boolean and overflowing legacy limit settings
         * instead of casting them to unintended values under PHP 8.
         * Logs an error category without exposing API credentials on failure.
         * @param mixed $maxEvents Requested maximum number of events.
         * @return array Upcoming event records, or an empty array on failure.
         */
        public function nextEvents($maxEvents): array
        {
            if ($this->apiKey === '' || $this->calendarId === '') {
                return [];
            }

            $maxEvents = self::normaliseEventLimit($maxEvents);

            try {
                return (new GoogleCalendarHelper())->loadNextEvents(
                    $this->apiKey,
                    $this->calendarId,
                    $maxEvents
                );
            } catch (\Throwable $e) {
                Log::add(
                    __METHOD__ . ': Google Calendar data is unavailable (' . get_class($e) . ')',
                    Log::ERROR,
                    'jsmerror'
                );

                return [];
            }
        }

        /**
         * Parse a legacy event-count limit without implicit PHP casts.
         *
         * Keeps zero-padded decimal settings compatible and clamps the
         * accepted range to the Google Calendar API's 1..2500 maximum.
         * Malformed, nested, boolean, float or overflowing inputs fall back
         * to five events, matching the native module helper's default.
         *
         * @param mixed $value Legacy event-count parameter.
         * @return int Validated number of events to retrieve.
         */
        private static function normaliseEventLimit(mixed $value): int
        {
            if (is_int($value)) {
                return min(2500, max(1, $value));
            }

            if (!is_string($value) || $value === '' || strlen($value) > 128
                || preg_match('/^[0-9]+$/D', $value) !== 1) {
                return 5;
            }

            $digits = ltrim($value, '0');
            $digits = $digits === '' ? '0' : $digits;
            $limit = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

            return $limit === false ? 5 : min(2500, max(1, $limit));
        }
    }
}

if (!class_exists('ModGoogleCalendarHelper', false)) {
    class_alias('ModJSMGoogleCalendarHelper', 'ModGoogleCalendarHelper');
}
