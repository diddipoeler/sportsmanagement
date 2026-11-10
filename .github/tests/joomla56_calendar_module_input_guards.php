<?php
/**
 * Standalone input-safety regressions for the Joomla 5/6 calendar module helper.
 *
 * Checks timezone and legacy configuration normalization without requiring a
 * Joomla installation, a calendar database or network integrations.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);

require_once dirname(__DIR__, 2)
    . '/modules/mod_sportsmanagement_calendar/src/Helper/CalendarHelper.php';

$class = \Diddipoeler\Module\SportsManagementCalendar\Site\Helper\CalendarHelper::class;
$timezone = new ReflectionMethod($class, 'safeTimezone');
$date = new ReflectionMethod($class, 'dateFromValue');

set_error_handler(static function (int $severity, string $message): never {
    throw new ErrorException($message, 0, $severity);
});

try {
    foreach ([
        ['Europe/Berlin', 'Europe/Berlin'],
        ['America/New_York', 'America/New_York'],
        ['+01:00', '+01:00'],
        ['UTC', 'UTC'],
        ['Bad/Timezone', 'UTC'],
        [[], 'UTC'],
        [new stdClass(), 'UTC'],
        [false, 'UTC'],
        [null, 'UTC'],
        ["Europe/Berlin\0invalid", 'UTC'],
        ["Europe/Berlin\ninvalid", 'UTC'],
        [str_repeat('X', 129), 'UTC'],
    ] as [$configured, $expected]) {
        if ($timezone->invoke(null, $configured) !== $expected) {
            throw new RuntimeException('Calendar module accepted an invalid site timezone.');
        }
    }

    foreach ([
        ['default_jsm', 'default_jsm'],
        ['default_arrobefr', 'default_jsm'],
        ['default_tuicalendar', 'default_jsm'],
        ['calendar_custom-1', 'calendar_custom-1'],
        ['../admin', 'default_jsm'],
        ['a/b', 'default_jsm'],
        ['', 'default_jsm'],
        [str_repeat('x', 101), 'default_jsm'],
    ] as [$layout, $expected]) {
        if ($class::normaliseLayout($layout) !== $expected) {
            throw new RuntimeException('Calendar module accepted an invalid template layout.');
        }
    }

    foreach ([[], ['2026-02-03'], new stdClass(), true, false, 1.5, null] as $invalidDate) {
        if ($date->invoke(null, $invalidDate, 'UTC') !== null) {
            throw new RuntimeException('Calendar module accepted a non-scalar match date.');
        }
    }
} finally {
    restore_error_handler();
}

$source = file_get_contents(dirname(__DIR__, 2)
    . '/modules/mod_sportsmanagement_calendar/src/Helper/CalendarHelper.php');

if (!is_string($source)
    || !str_contains($source, 'is_string($rawLayout)')
    || !str_contains($source, 'is_string($rawPrefix)')
    || substr_count($source, "self::safeTimezone(\$app->get('offset', 'UTC'))") !== 3) {
    throw new RuntimeException('Native calendar runtime does not apply normalized legacy settings.');
}

echo "Joomla 5/6 calendar module timezone, layout and date input guards OK\n";
