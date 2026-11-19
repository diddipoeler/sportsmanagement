<?php
/**
 * Verify that the retained FullCalendar event helper rejects malformed PHP 8 inputs.
 *
 * Usage: php .github/tests/joomla56-fullcalendar-demo-event-inputs.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

defined('_JEXEC') || define('_JEXEC', 1);

require_once dirname(__DIR__, 2)
    . '/modules/mod_sportsmanagement_google_calendar/media/fullcalendar/demos/php/utils.php';

$valid = new Event(['title' => 'Fixture', 'start' => '2026-11-19', 'end' => '2026-11-20']);
if (!$valid->allDay || $valid->toArray()['start'] !== '2026-11-19') {
    throw new RuntimeException('Valid all-day FullCalendar events changed unexpectedly.');
}

$invalid = [
    ['start' => ['2026-11-19']],
    ['start' => true],
    ['start' => 123],
    ['start' => ''],
    ['start' => str_repeat('1', 129)],
    ['start' => "2026-11-19\n"],
    ['start' => '2026-11-19', 'end' => ['2026-11-20']],
    ['start' => '2026-11-19', 'end' => null],
];

foreach ($invalid as $fields) {
    try {
        new Event($fields);
    } catch (InvalidArgumentException) {
        continue;
    }

    throw new RuntimeException('Invalid FullCalendar event date was accepted.');
}

$largeTitle = new Event(['title' => ['not a title'], 'start' => '2026-11-19']);
if ($largeTitle->title !== '') {
    throw new RuntimeException('Malformed FullCalendar title was not normalised.');
}

echo "Joomla 5/6 FullCalendar event input checks passed.\n";
