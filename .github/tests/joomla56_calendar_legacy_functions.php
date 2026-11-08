<?php
/**
 * Regression tests for Joomla 5/6 legacy calendar function compatibility.
 *
 * Covers calendar option generation, non-scalar request values and the legacy
 * global wrappers used by older Joomla module template overrides.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);
$root = dirname(__DIR__, 2);
require_once $root . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarFunctions.php';
require_once $root . '/modules/mod_sportsmanagement_calendar/calendarFunctions.php';

$runtime = \Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarFunctions::class;

$months = $runtime::monthList('02');
if (substr_count($months, '<option ') !== 12
    || !str_contains($months, 'value="02" selected')
    || $runtime::monthList([2]) === ''
    || str_contains($runtime::monthList([2]), ' selected')) {
    throw new RuntimeException('Legacy calendar month option regression.');
}

$years = $runtime::yearList('2024');
if (substr_count($years, '<option ') !== 11
    || !str_contains($years, 'value="2024" selected')
    || str_contains($runtime::yearList(['2024']), 'value="2024" selected')) {
    throw new RuntimeException('Legacy calendar year option regression.');
}

if ($runtime::events([]) === '' || str_contains($runtime::events([]), 'Array')) {
    throw new RuntimeException('Malformed legacy calendar event date regression.');
}

if (getMonthList('02') !== $months
    || getYearList('2024') !== $years
    || getEvents('2024-02-29') !== $runtime::events('2024-02-29')) {
    throw new RuntimeException('Legacy calendar global function bridge regression.');
}

ob_start();
try {
    getCalender(2024, 2);
    $html = (string) ob_get_contents();
} finally {
    ob_end_clean();
}

if (!str_contains($html, 'calendar-contain')
    || !str_contains($html, '<span class="calendar__date">29</span>')
    || !str_contains($html, 'value="02" selected')) {
    throw new RuntimeException('Legacy calendar leap-year markup regression.');
}

echo "Joomla 5/6 legacy calendar functions OK\n";
