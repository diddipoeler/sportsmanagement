<?php
/**
 * Joomla 5/6 compatibility bridge for the SportsManagement Google Calendar module helper.
 */
defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementGoogleCalendar\Site\Helper\GoogleCalendarHelper;

if (!class_exists(GoogleCalendarHelper::class)) {
    require_once __DIR__ . '/src/Helper/GoogleCalendarHelper.php';
}

if (!class_exists('ModJSMGoogleCalendarHelper', false)) {
    class_alias(GoogleCalendarHelper::class, 'ModJSMGoogleCalendarHelper');
}

if (!class_exists('ModGoogleCalendarHelper', false)) {
    class_alias(GoogleCalendarHelper::class, 'ModGoogleCalendarHelper');
}
