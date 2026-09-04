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
    class_alias(GoogleCalendarHelper::class, 'ModJSMGoogleCalendarHelper');
}

if (!class_exists('ModGoogleCalendarHelper', false)) {
    class_alias(GoogleCalendarHelper::class, 'ModGoogleCalendarHelper');
}
