<?php
/**
 * Pure Joomla 5/6 calendar month, year and timezone input regressions.
 *
 * Verifies that invalid legacy values cannot be cast to another date or
 * produce warnings during calendar month-window construction.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);

/** Standalone parent; the input normalizers do not require Joomla. */
class PHPCalendar
{
}

require dirname(__DIR__, 2)
    . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarRuntime.php';

$runtime = \Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarRuntime::class;
$number = new ReflectionMethod($runtime, 'calendarNumber');
$timezone = new ReflectionMethod($runtime, 'calendarTimezone');

set_error_handler(static function (int $severity, string $message): never {
    throw new ErrorException($message, 0, $severity);
});

try {
    foreach ([
        [1, 1], [12, 12], ['0012', 12], [0, 1], [13, 12],
        [-3, 1], ['-03', 1], ['0000', 1], [' 07 ', 7],
        [[], 9], [[7], 9], [(object) ['month' => 7], 9],
        [true, 9], [7.5, 9], ['7junk', 9], ['7.5', 9],
        ['7e2', 9], ['', 9], [' ', 9],
        [(string) PHP_INT_MAX . '0', 9], [str_repeat('0', 65) . '7', 9],
    ] as [$raw, $expected]) {
        if ($number->invoke(null, $raw, 1, 12, 9) !== $expected) {
            throw new RuntimeException('Calendar month normalization accepted an invalid input.');
        }
    }

    foreach ([
        [2024, 2024], ['002024', 2024], [1969, 1970], [10000, 9999],
        [' 2026 ', 2026], ['2026junk', 2025], ['2e3', 2025],
        [[], 2025], [new stdClass(), 2025], [false, 2025],
        [(string) PHP_INT_MAX . '0', 2025],
    ] as [$raw, $expected]) {
        if ($number->invoke(null, $raw, 1970, 9999, 2025) !== $expected) {
            throw new RuntimeException('Calendar year normalization accepted an invalid input.');
        }
    }

    foreach ([
        ['Europe/Berlin', 'UTC', 'Europe/Berlin'],
        [' Pacific/Auckland ', 'UTC', 'Pacific/Auckland'],
        ['', 'Europe/Berlin', 'Europe/Berlin'],
        ['Invalid/TimeZone', 'Europe/Berlin', 'Europe/Berlin'],
        [['nested'], 'Europe/Berlin', 'Europe/Berlin'],
        [new stdClass(), 'UTC', 'UTC'],
        [false, 'Invalid/Fallback', 'UTC'],
        [str_repeat('A', 120), 'UTC', 'UTC'],
    ] as [$configured, $siteZone, $expected]) {
        $result = $timezone->invoke(null, $configured, $siteZone);

        if (!$result instanceof DateTimeZone || $result->getName() !== $expected) {
            throw new RuntimeException('Calendar timezone did not use the valid configured or site offset.');
        }
    }
} finally {
    restore_error_handler();
}

echo "Joomla 5/6 calendar period and timezone validation OK\n";
