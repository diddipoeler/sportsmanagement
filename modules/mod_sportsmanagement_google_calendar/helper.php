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
use Joomla\Registry\Registry;

if (!class_exists(GoogleCalendarHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/GoogleCalendarHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(GoogleCalendarHelper::class)) {
    throw new \RuntimeException('SportsManagement native Google Calendar module helper could not be loaded.', 500);
}

if (!class_exists('ModJSMGoogleCalendarHelper', false)) {
    class ModJSMGoogleCalendarHelper
    {
        protected string $apiKey = '';
        protected string $calendarId = '';

        public function __construct(?Registry $params = null)
        {
            $params ??= new Registry();
            $this->apiKey = trim((string) $params->get('api_key', ''));
            $this->calendarId = trim((string) $params->get('calendar_id', ''));
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

            return (new GoogleCalendarHelper())->loadNextEvents(
                $this->apiKey,
                $this->calendarId,
                max(1, (int) $maxEvents)
            );
        }
    }
}

if (!class_exists('ModGoogleCalendarHelper', false)) {
    class_alias('ModJSMGoogleCalendarHelper', 'ModGoogleCalendarHelper');
}
