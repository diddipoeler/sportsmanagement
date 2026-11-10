<?php
/**
 * Pure Joomla 5/6 calendar connector date-field regressions.
 *
 * Rejects malformed imported date fields without PHP 8 warnings, and
 * preserves valid timestamps and backwards-compatible date strings.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);

/** Minimal parent for loading the isolated SportsManagement connector. */
class JSMCalendar
{
}

require dirname(__DIR__, 2)
    . '/modules/mod_sportsmanagement_calendar/connectors/sportsmanagement_j5.php';

$connector = SportsmanagementConnector::class;
$parseDate = new ReflectionMethod($connector, 'parseDate');
$normalise = new ReflectionMethod($connector, 'normaliseMatchDate');
$matchStart = new ReflectionMethod($connector, 'matchStart');

set_error_handler(static function (int $severity, string $message): never {
    throw new ErrorException($message, 0, $severity);
});

try {
    foreach ([
        '2023-02-29', '2024-13-01', '2024-00-01',
        'not-a-date', '2024-02-' . chr(0), '2024-01-0x',
        '', '0000-00-00', chr(0),
    ] as $invalid) {
        if ($parseDate->invoke(null, $invalid) !== null) {
            throw new RuntimeException('Invalid calendar date boundary was accepted.');
        }
    }

    foreach (['2024-02-29', '2024-02-29 12:00:00'] as $valid) {
        $date = $parseDate->invoke(null, $valid);

        if (!$date instanceof DateTimeImmutable || $date->format('Y-m-d') !== '2024-02-29') {
            throw new RuntimeException('Valid calendar leap-day boundary was rejected.');
        }
    }

    // Invalid range boundaries must stop birthday queries before touching
    // any Joomla application or external database.
    foreach ([
        ['start' => ['nested'], 'end' => '2026-12-31'],
        ['start' => '2026-01-01', 'end' => new stdClass()],
        ['start' => chr(0), 'end' => '2026-12-31'],
    ] as $range) {
        if ($connector::getBirthdays($range) !== []) {
            throw new RuntimeException('Corrupt birthday range triggered a database query.');
        }
    }

    foreach ([
        null, [], ['date'], new stdClass(), true, false,
        '', '0000-00-00 00:00:00', 'not-a-date', '2026-05-' . chr(0),
    ] as $raw) {
        $row = (object) ['match_date' => $raw];
        $normalise->invoke(null, $row);

        if ($row->timestamp !== 0 || $row->match_date !== null
            || $matchStart->invoke(null, $row) !== '') {
            throw new RuntimeException('Malformed calendar match date was not safely rejected.');
        }
    }

    foreach ([[], ['nested'], new stdClass(), true, chr(0)] as $raw) {
        if ($matchStart->invoke(null, (object) ['match_date' => $raw]) !== '') {
            throw new RuntimeException('Calendar match date formatting cast a corrupt field.');
        }
    }

    $date = new DateTimeImmutable('2026-05-12 18:30:00', new DateTimeZone('UTC'));
    $alreadyNormalised = (object) ['match_date' => $date];
    $normalise->invoke(null, $alreadyNormalised);

    if ($alreadyNormalised->timestamp !== $date->getTimestamp()
        || $alreadyNormalised->match_date !== $date
        || $matchStart->invoke(null, $alreadyNormalised) !== '2026-05-12 18:30'
        || $matchStart->invoke(null, (object) ['match_date' => '2026-05-12 18:30:00'])
            !== '2026-05-12 18:30') {
        throw new RuntimeException('Valid calendar match timestamps or date formatting changed.');
    }
} finally {
    restore_error_handler();
}

echo "Joomla 5/6 calendar connector date fields OK\n";
