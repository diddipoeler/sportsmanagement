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
    $nativeFunctions = __DIR__ . '/src/Runtime/CalendarFunctions.php';

    if (is_file($nativeFunctions)) {
        require_once $nativeFunctions;
    }
}

if (!class_exists(CalendarFunctions::class)) {
    throw new \RuntimeException('SportsManagement native CalendarFunctions runtime could not be loaded.', 500);
}

if (!function_exists('getCalender')) {
    /**
     * Render the selected month using the native Joomla 5/6 calendar renderer.
     * Preserves the misspelled historical global function name for existing template overrides while delegating all calendar markup to the namespaced runtime.
     *
     * @param int|string $year Optional display year.
     * @param int|string $month Optional display month.
     * @return void Calendar HTML is sent to the output buffer.
     */
    function getCalender($year = '', $month = ''): void
    {
        CalendarFunctions::render($year, $month);
    }
}

if (!function_exists('getMonthList')) {
    /**
     * Build the list of month options for legacy calendar selectors.
     * Maintains the old global function signature while obtaining escaped option markup from the native calendar runtime.
     *
     * @param int|string $selected Currently selected month.
     * @return string HTML option elements for the twelve months.
     */
    function getMonthList($selected = ''): string
    {
        return CalendarFunctions::monthList($selected);
    }
}

if (!function_exists('getYearList')) {
    /**
     * Build the year dropdown for stored legacy calendar layouts.
     * Delegates year-range generation to the native renderer so existing template overrides keep their selectable year controls.
     *
     * @param int|string $selected Currently selected year; empty selects the current year.
     * @return string HTML option elements for nearby years.
     */
    function getYearList($selected = ''): string
    {
        return CalendarFunctions::yearList($selected);
    }
}

if (!function_exists('getEvents')) {
    /**
     * Generate the legacy sidebar header for a selected calendar date.
     * Passes the selected date through the namespaced runtime and returns the formatted weekday and month heading.
     *
     * @param string $date Optional date to display.
     * @return string Calendar sidebar heading HTML.
     */
    function getEvents($date = ''): string
    {
        return CalendarFunctions::events($date);
    }
}
