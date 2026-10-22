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

            if (preg_match('/[\x00-\x1F\x7F]/', $apiKey . $calendarId)) {
                return;
            }

            $this->apiKey = $apiKey;
            $this->calendarId = $calendarId;
        }

        public static function duration(object $event): string
        {
            return GoogleCalendarHelper::duration($event);
        }

        public function nextEvents($maxEvents): array
        {
            if ($this->apiKey === '' || $this->calendarId === '') {
                return [];
            }

            $maxEvents = min(2500, max(1, (int) $maxEvents));

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
    }
}

if (!class_exists('ModGoogleCalendarHelper', false)) {
    class_alias('ModJSMGoogleCalendarHelper', 'ModGoogleCalendarHelper');
}
