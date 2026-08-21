<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 SportsManagement calendar module.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementCalendar\Site\Helper\CalendarHelper;

if (!class_exists(CalendarHelper::class)) {
    require_once __DIR__ . '/src/Helper/CalendarHelper.php';
}

if (!class_exists('modJSMCalendarHelper', false)) {
    class_alias(CalendarHelper::class, 'modJSMCalendarHelper');
}

if (!class_exists('ModSportsmanagementCalendarHelper', false)) {
    class_alias(CalendarHelper::class, 'ModSportsmanagementCalendarHelper');
}
