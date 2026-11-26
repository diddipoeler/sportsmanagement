<?php
/**
 * Regression checks for invalid iCalendar recurrence rules.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);
require_once dirname(__DIR__, 2) . '/admin/helpers/icaljsm.php';

$start = gmdate('Ymd', time() + 86400) . 'T120000Z';

foreach ([
    'INTERVAL=1',
    'FREQ=HOURLY',
    'FREQ=DAILY;INTERVAL=0',
    'FREQ=DAILY;INTERVAL=-1',
    'FREQ=DAILY;COUNT=0',
    'FREQ=DAILY;COUNT=1',
    'FREQ=DAILY;COUNT=oops',
    'FREQ=DAILY;FREQ=WEEKLY',
    'FREQ=DAILY;BROKEN',
    'FREQ=DAILY;UNTIL=invalid',
    'FREQ=DAILY;COUNT=2',
] as $rule) {
    $parser = new icaljsm(false, ['defaultTimeZone' => 'UTC', 'disableCharacterReplacement' => true]);
    $parser->cal['VEVENT'] = [[
        'UID' => 'regression',
        'DTSTART' => $start,
        'DTSTART_array' => [[], $start, $parser->iCalDateToUnixTimestamp($start), $start],
        'RRULE' => $rule,
        'RRULE_array' => [[], $rule],
    ]];
    $parser->eventCount = 1;
    $method = new ReflectionMethod($parser, 'processRecurrences');
    $method->invoke($parser);
    $expected = $rule === 'FREQ=DAILY;COUNT=2' ? 2 : 1;
    if (count($parser->cal['VEVENT']) !== $expected || $parser->eventCount !== $expected) {
        throw new RuntimeException('Unexpected recurrence count for ' . $rule);
    }
}

echo "Calendar recurrence validation regressions OK\n";
