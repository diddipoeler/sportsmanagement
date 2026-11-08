<?php
/**
 * Joomla 5/6 calendar connector regression for validated IDs and month dates.
 *
 * Runs the actual private converter without loading Joomla or remote calendar
 * extensions so legacy module settings cannot change SQL filter identifiers.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);

// The connector extends the legacy calendar alias, but validation is static
// and does not require an application, database or installed JEvents extension.
class JSMCalendar {}

require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_calendar/connectors/sportsmanagement_j5.php';

$ids = new ReflectionMethod(SportsmanagementConnector::class, 'normaliseIds');

foreach ([
    [[1, '001', '2, 3; 004', null, [], true, '5x', '6.0', '-7', '0', PHP_INT_MAX . '0'], [1, 2, 3, 4]],
    [[' 8 9 ', '008,8', 9], [8, 9]],
    ['2, 3 0002', [2, 3]],
    [[new stdClass(), '0', '000', '001invalid'], []],
    [['0005', ' 0006 '], [5, 6]],
] as [$input, $expected]) {
    if ($ids->invoke(null, $input) !== $expected) {
        throw new RuntimeException('Calendar connector must reject malformed SQL filter IDs.');
    }
}

$parseDate = new ReflectionMethod(SportsmanagementConnector::class, 'parseDate');

foreach (['2026-02-29', '2026-02-30', '2026-13-01', '2026-00-01', 'invalid'] as $invalid) {
    if ($parseDate->invoke(null, $invalid) !== null) {
        throw new RuntimeException('Calendar connector accepted an impossible month boundary.');
    }
}

foreach (['2024-02-29', '2026-11-08 16:30:00'] as $valid) {
    $date = $parseDate->invoke(null, $valid);
    if (!$date instanceof DateTimeImmutable || $date->format('Y-m-d') !== substr($valid, 0, 10)) {
        throw new RuntimeException('Calendar connector rejected an actual month boundary.');
    }
}

echo "Joomla 5/6 calendar connector ID and date validation OK\n";
