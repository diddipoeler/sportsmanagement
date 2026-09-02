<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 calendar runtime.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarDate;
use Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarRuntime;

if (!class_exists(CalendarRuntime::class)) {
    require_once dirname(__DIR__) . '/src/Runtime/CalendarDate.php';
    require_once dirname(__DIR__) . '/src/Runtime/CalendarRuntime.php';
}

if (!class_exists('JSMCalendar', false)) {
    class_alias(CalendarRuntime::class, 'JSMCalendar');
}

if (!class_exists('modJSMCalendarHelperDate', false)) {
    class_alias(CalendarDate::class, 'modJSMCalendarHelperDate');
}
