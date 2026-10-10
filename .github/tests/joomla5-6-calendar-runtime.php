<?php
/**
 * Regression tests for Joomla 5/6 calendar sites, administration, and modules.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);
require dirname(__DIR__, 2) . '/site/src/Service/GoogleCalendarReadService.php';
require dirname(__DIR__, 2) . '/admin/src/Service/GoogleCalendarMatchSynchronizer.php';

function calendarAssert(bool $condition, string $reason): void
{
    if (!$condition) {
        throw new RuntimeException($reason);
    }
}

// ISO strings with different UTC offsets do not sort chronologically.
$sort = new ReflectionMethod(
    \Diddipoeler\Component\SportsManagement\Site\Service\GoogleCalendarReadService::class,
    'compareEventStarts'
);
$early = ['start' => '2026-10-10T12:00:00+02:00'];
$late = ['start' => '2026-10-10T11:30:00+00:00'];
calendarAssert($sort->invoke(null, $early, $late) < 0, 'Mixed-offset event order is wrong.');
calendarAssert($sort->invoke(null, $late, $early) > 0, 'Reverse event order is wrong.');
calendarAssert(
    $sort->invoke(null, $early, ['start' => '2026-10-10T10:00:00+00:00']) === 0,
    'Same calendar instant must compare equally.'
);

class CalendarTestEventDateTime
{
    public string $dateTime = '';
    public string $timeZone = '';

    public function setDateTime(string $value): void { $this->dateTime = $value; }
    public function setTimeZone(string $value): void { $this->timeZone = $value; }
}

class CalendarTestEvent
{
    public ?CalendarTestEventDateTime $start = null;
    public ?CalendarTestEventDateTime $end = null;

    public function setSummary(string $value): void {}
    public function setDescription(string $value): void {}
    public function setLocation(string $value): void {}
    public function setStart(CalendarTestEventDateTime $value): void { $this->start = $value; }
    public function setEnd(CalendarTestEventDateTime $value): void { $this->end = $value; }
}

class_alias(CalendarTestEvent::class, 'Google\Service\Calendar\Event');
class_alias(CalendarTestEventDateTime::class, 'Google\Service\Calendar\EventDateTime');

$sync = (new ReflectionClass(
    \Diddipoeler\Component\SportsManagement\Administrator\Service\GoogleCalendarMatchSynchronizer::class
))->newInstanceWithoutConstructor();
$createEvent = new ReflectionMethod($sync, 'createEvent');
$match = (object) [
    'hometeam' => 'Alpha',
    'awayteam' => 'Beta',
    'team1_result' => null,
    'team2_result' => null,
    'match_date' => '2026-10-10 18:00:00',
];
$project = (object) ['timezone' => 'UTC', 'game_regular_time' => 0, 'halftime' => 0];
$event = $createEvent->invoke($sync, $match, $project);
calendarAssert($event->start !== null && $event->end !== null, 'Calendar start/end missing.');
calendarAssert(
    strtotime($event->end->dateTime) - strtotime($event->start->dateTime) === 3600,
    'Unconfigured duration must yield a valid one-hour event.'
);
$project->game_regular_time = 90;
$project->halftime = 15;
$event = $createEvent->invoke($sync, $match, $project);
calendarAssert(
    strtotime($event->end->dateTime) - strtotime($event->start->dateTime) === 6300,
    'Configured game duration and halftime must be preserved.'
);

function renderCalendarModule(array $items): string
{
    $events = $items;
    $params = new class {
        public function get(string $name, mixed $default = null): mixed { return $default; }
    };

    ob_start();

    try {
        require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_google_calendar/tmpl/default.php';

        return (string) ob_get_contents();
    } finally {
        ob_end_clean();
    }
}

foreach (['javascript:alert(1)', 'data:text/html,<h1>unsafe</h1>', 'ftp://example.org/'] as $url) {
    $html = renderCalendarModule([(object) ['htmlLink' => $url, 'summary' => '<b>Match</b>']]);
    calendarAssert(!str_contains($html, 'href='), 'Unsafe calendar link was rendered: ' . $url);
    calendarAssert(str_contains($html, '&lt;b&gt;Match&lt;/b&gt;'), 'Event title must be escaped.');
}

$html = renderCalendarModule([(object) [
    'htmlLink' => 'https://calendar.google.com/event?a=1&b=2',
    'summary' => 'Match',
]]);
calendarAssert(
    str_contains($html, 'href="https://calendar.google.com/event?a=1&amp;b=2"'),
    'Valid HTTPS calendar link must remain clickable and escaped.'
);

echo "Joomla 5/6 calendar regressions: OK\n";
