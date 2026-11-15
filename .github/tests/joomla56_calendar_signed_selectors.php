<?php
/**
 * Exercise signed and malformed Joomla 5/6 calendar module selectors.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);

// The selector parser does not use the renderer's output. This minimal legacy
// base class prevents loading the real module bridge and Joomla runtime.
class PHPCalendar
{
}

require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarRuntime.php';

/**
 * Verify the native month/year selector without starting a Joomla application.
 *
 * @param mixed $value Stored request or module selector.
 * @param int $minimum Lowest supported selector value.
 * @param int $maximum Highest supported selector value.
 * @param int $fallback Value for an invalid selector.
 * @param int $expected Expected parsed and clamped calendar value.
 * @return void
 */
function assertCalendarSelector(mixed $value, int $minimum, int $maximum, int $fallback, int $expected): void
{
    $parser = new ReflectionMethod(
        \Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarRuntime::class,
        'calendarNumber'
    );

    $actual = $parser->invoke(null, $value, $minimum, $maximum, $fallback);

    if ($actual !== $expected) {
        throw new RuntimeException('Unexpected calendar selector result: ' . var_export($value, true));
    }
}

assertCalendarSelector('+0007', 1, 12, 6, 7);
assertCalendarSelector('-0003', 1, 12, 6, 1);
assertCalendarSelector('00012', 1, 12, 6, 12);
assertCalendarSelector('+2026', 1970, 9999, 2025, 2026);
assertCalendarSelector('99999', 1970, 9999, 2025, 9999);
assertCalendarSelector(['7'], 1, 12, 6, 6);
assertCalendarSelector('+', 1, 12, 6, 6);
assertCalendarSelector('4x', 1, 12, 6, 6);
assertCalendarSelector('9999999999999999999999', 1, 12, 6, 6);

echo "Joomla 5/6 calendar signed selectors: OK\n";
