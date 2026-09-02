<?php
/**
 * Legacy function bridge for the native Joomla 5/6 calendar helper runtime.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarFunctions;

if (!class_exists(CalendarFunctions::class)) {
    require_once __DIR__ . '/src/Runtime/CalendarFunctions.php';
}

if (!function_exists('getCalender')) {
    function getCalender($year = '', $month = ''): void
    {
        CalendarFunctions::render($year, $month);
    }
}

if (!function_exists('getMonthList')) {
    function getMonthList($selected = ''): string
    {
        return CalendarFunctions::monthList($selected);
    }
}

if (!function_exists('getYearList')) {
    function getYearList($selected = ''): string
    {
        return CalendarFunctions::yearList($selected);
    }
}

if (!function_exists('getEvents')) {
    function getEvents($date = ''): string
    {
        return CalendarFunctions::events($date);
    }
}
