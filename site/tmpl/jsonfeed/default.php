<?php
/**
 * Individual-event JSON output for SportsManagement Google Calendar feeds.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

$app = Factory::getApplication();
$app->getDocument()->setMimeEncoding('application/json');
$itemId = $app->getInput()->getInt('Itemid', 0);

$fadeColor = static function (string $color): string {
    $hex = strtoupper(ltrim(trim($color), '#'));

    if (!preg_match('/^[0-9A-F]{6}$/', $hex)) {
        $hex = '135CAE';
    }

    $red = (int) round((hexdec(substr($hex, 0, 2)) + 4 * 255) / 5);
    $green = (int) round((hexdec(substr($hex, 2, 2)) + 4 * 255) / 5);
    $blue = (int) round((hexdec(substr($hex, 4, 2)) + 4 * 255) / 5);

    return sprintf('#%02X%02X%02X', $red, $green, $blue);
};

$data = [];

foreach ($this->events as $event) {
    $eventId = trim((string) ($event['id'] ?? ''));
    $calendarId = (int) ($event['gcid'] ?? 0);
    $start = trim((string) ($event['start'] ?? ''));

    if ($eventId === '' || $calendarId <= 0 || $start === '') {
        continue;
    }

    $title = trim((string) ($event['title'] ?? ''));
    $descriptionText = trim((string) ($event['description'] ?? ''));
    $location = trim((string) ($event['location'] ?? ''));
    $descriptionParts = [];

    if ($descriptionText !== '') {
        $descriptionParts[] = nl2br(htmlspecialchars($descriptionText, ENT_QUOTES, 'UTF-8'));
    }

    if ($location !== '') {
        $descriptionParts[] = htmlspecialchars($location, ENT_QUOTES, 'UTF-8');
    }

    $description = implode('<br>', $descriptionParts);
    $url = Route::_(
        'index.php?option=com_sportsmanagement&view=event&eventID=' . rawurlencode($eventId)
            . '&gcid=' . $calendarId
            . ($itemId > 0 ? '&Itemid=' . $itemId : ''),
        false
    );

    $data[] = [
        'id' => $eventId,
        'gcid' => $calendarId,
        'title' => $this->compactMode === 0 ? $title : "\u{00A0}",
        'start' => $start,
        'end' => (string) ($event['end'] ?? $start),
        'url' => $url,
        'color' => $fadeColor((string) ($event['color'] ?? '#135CAE')),
        'allDay' => $this->compactMode === 0 ? !empty($event['allDay']) : true,
        'description' => $description,
    ];
}

echo json_encode(
    $data,
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
