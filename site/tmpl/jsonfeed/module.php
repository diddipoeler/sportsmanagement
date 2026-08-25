<?php
/**
 * Compact-by-day JSON output for the SportsManagement Google Calendar module.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

Factory::getApplication()->getDocument()->setMimeEncoding('application/json');

$grouped = [];

foreach ($this->events as $event) {
    $startValue = (string) ($event['start'] ?? '');

    if ($startValue === '') {
        continue;
    }

    try {
        $start = new DateTimeImmutable($startValue);
    } catch (Throwable) {
        continue;
    }

    $dates = [$start->format('Y-m-d')];

    if (!empty($event['allDay']) && !empty($event['end'])) {
        try {
            $cursor = $start->setTime(0, 0)->modify('+1 day');
            $end = (new DateTimeImmutable((string) $event['end']))->setTime(0, 0);

            while ($cursor < $end) {
                $dates[] = $cursor->format('Y-m-d');
                $cursor = $cursor->modify('+1 day');
            }
        } catch (Throwable) {
            // A malformed end value must not break the complete feed.
        }
    }

    foreach ($dates as $date) {
        $grouped[$date] ??= [];
        $grouped[$date][] = $event;
    }
}

ksort($grouped);
$data = [];

foreach ($grouped as $date => $events) {
    $calendarIds = array_values(array_unique(array_filter(array_map(
        static fn (array $event): int => (int) ($event['gcid'] ?? 0),
        $events
    ))));
    $titles = array_values(array_filter(array_map(
        static fn (array $event): string => trim((string) ($event['title'] ?? '')),
        $events
    ), 'strlen'));
    $description = '<strong>' . count($events) . '</strong><ul>';

    foreach ($titles as $title) {
        $description .= '<li>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</li>';
    }

    $description .= '</ul>';
    $firstEvent = $events[0] ?? [];
    [$year, $month, $day] = array_pad(explode('-', $date), 3, '');
    $url = Route::_(
        'index.php?option=com_sportsmanagement&view=gcalendar&gcids=' . implode(',', $calendarIds),
        false
    ) . '#year=' . rawurlencode($year)
        . '&month=' . rawurlencode($month)
        . '&day=' . rawurlencode($day)
        . '&view=agendaDay';

    $data[] = [
        'id' => $date,
        'title' => "\u{00A0}",
        'start' => $date,
        'url' => $url,
        'color' => (string) ($firstEvent['color'] ?? '#135CAE'),
        'allDay' => true,
        'description' => $description,
    ];
}

echo json_encode(
    $data,
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
