<?php
/**
 * Native Joomla 5/6 Google Calendar event layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$event = is_array($this->event ?? null) ? $this->event : null;
$wa = $this->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle(
    'com_sportsmanagement.event',
    'components/com_sportsmanagement/tmpl/event/default.css',
    ['version' => 'auto']
);

if (!$event) {
    echo '<div class="alert alert-info">' . htmlspecialchars(Text::_('JGLOBAL_NO_MATCHING_RESULTS'), ENT_QUOTES, 'UTF-8') . '</div>';

    return;
}

// Imported and cached calendar entries may contain nested data instead of
// display strings. A passive layout never casts them to "Array" on PHP 8.
$readText = static function (mixed $value, int $maximum = 8192): string {
    if (!is_string($value) || strlen($value) > $maximum
        || preg_match('/[\x00-\x1F\x7F]/', $value) === 1) {
        return '';
    }

    return trim($value);
};
$escape = static fn (mixed $value): string => htmlspecialchars(
    is_string($value) ? $value : '',
    ENT_QUOTES,
    'UTF-8'
);
$title = $readText($event['title'] ?? null, 512);
$calendarName = $readText($event['calendarName'] ?? null, 512);
$description = $readText($event['description'] ?? null);
$location = $readText($event['location'] ?? null, 2048);
$start = $readText($event['start'] ?? null, 128);
$end = $readText($event['end'] ?? null, 128);
$allDay = ($event['allDay'] ?? false) === true || ($event['allDay'] ?? null) === 1;
$rawCalendarId = $event['gcid'] ?? null;
$calendarId = 0;
if (is_int($rawCalendarId) && $rawCalendarId > 0) {
    $calendarId = $rawCalendarId;
} elseif (is_string($rawCalendarId) && strlen($rawCalendarId) <= 64
    && preg_match('/^[0-9]+$/D', $rawCalendarId) === 1) {
    $digits = ltrim($rawCalendarId, '0');
    if ($digits !== '') {
        $parsed = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $calendarId = $parsed === false ? 0 : $parsed;
    }
}
$color = $readText($event['color'] ?? '#135CAE', 7);

if ($title === '') {
    $title = $calendarName !== '' ? $calendarName : $readText($event['id'] ?? null, 512);
}

if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
    $color = '#135CAE';
}

$googleUrl = $readText($event['htmlLink'] ?? null, 2048);
$googleScheme = strtolower((string) parse_url($googleUrl, PHP_URL_SCHEME));

if (!filter_var($googleUrl, FILTER_VALIDATE_URL) || !in_array($googleScheme, ['http', 'https'], true)) {
    $googleUrl = '';
}

$calendarUrl = $calendarId > 0
    ? Route::_('index.php?option=com_sportsmanagement&view=gcalendar&gcids=' . $calendarId)
    : '';
$mapsUrl = $location !== ''
    ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($location)
    : '';

$params = $this->app->getParams();
$dateFormat = $readText($params->get('description_date_format', 'd.m.Y'), 64) ?: 'd.m.Y';
$timeFormat = $readText($params->get('description_time_format', 'H:i'), 64) ?: 'H:i';
$timezoneName = $readText($this->app->get('offset', 'UTC'), 128) ?: 'UTC';

try {
    $timezone = new DateTimeZone($timezoneName ?: 'UTC');
} catch (Throwable) {
    $timezone = new DateTimeZone('UTC');
}

$displayDate = '';

try {
    $startDate = $start !== '' ? new DateTimeImmutable($start) : null;
    $endDate = $end !== '' ? new DateTimeImmutable($end) : null;

    if ($startDate) {
        $startDate = $startDate->setTimezone($timezone);
    }

    if ($endDate) {
        $endDate = $endDate->setTimezone($timezone);
    }

    if ($allDay && $startDate) {
        if ($endDate && $endDate > $startDate) {
            // Google Calendar stores all-day end dates exclusively.
            $endDate = $endDate->modify('-1 day');
        }

        $displayDate = $startDate->format($dateFormat);

        if ($endDate && $endDate->format('Y-m-d') !== $startDate->format('Y-m-d')) {
            $displayDate .= ' – ' . $endDate->format($dateFormat);
        }
    } elseif ($startDate) {
        $displayDate = $startDate->format($dateFormat . ' ' . $timeFormat);

        if ($endDate) {
            $sameDay = $endDate->format('Y-m-d') === $startDate->format('Y-m-d');
            $displayDate .= ' – ' . $endDate->format($sameDay ? $timeFormat : $dateFormat . ' ' . $timeFormat);
        }
    }
} catch (Throwable) {
    $displayDate = $start;
}
?>
<div id="gcal-event-container" class="card shadow-sm" style="border-left: .35rem solid <?= $escape($color) ?>;">
    <div class="card-body">
        <h2 class="card-title mb-3"><?= $escape($title) ?></h2>

        <dl class="row mb-0">
            <?php if ($calendarName !== '') : ?>
                <dt class="col-sm-3"><?= $escape(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_CALENDAR_LIST')) ?></dt>
                <dd class="col-sm-9">
                    <?php if ($calendarUrl !== '') : ?>
                        <a href="<?= $escape($calendarUrl) ?>"><?= $escape($calendarName) ?></a>
                    <?php else : ?>
                        <?= $escape($calendarName) ?>
                    <?php endif; ?>
                </dd>
            <?php endif; ?>

            <?php if ($displayDate !== '') : ?>
                <dt class="col-sm-3"><?= $escape(Text::_('JDATE')) ?></dt>
                <dd class="col-sm-9">
                    <time datetime="<?= $escape($start) ?>"><?= $escape($displayDate) ?></time>
                </dd>
            <?php endif; ?>

            <?php if ($location !== '') : ?>
                <dt class="col-sm-3"><?= $escape(Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_LOCATION')) ?></dt>
                <dd class="col-sm-9">
                    <a href="<?= $escape($mapsUrl) ?>" target="_blank" rel="noopener noreferrer"><?= $escape($location) ?></a>
                </dd>
            <?php endif; ?>
        </dl>

        <?php if ($description !== '') : ?>
            <h3 class="h5 mt-4"><?= $escape(Text::_('JGLOBAL_DESCRIPTION')) ?></h3>
            <div class="gcal-event-description"><?= nl2br($escape($description)) ?></div>
        <?php endif; ?>

        <?php if ($googleUrl !== '') : ?>
            <p class="mt-4 mb-0">
                <a class="btn btn-outline-primary" href="<?= $escape($googleUrl) ?>" target="_blank" rel="noopener noreferrer">
                    Google Calendar
                </a>
            </p>
        <?php endif; ?>
    </div>
</div>
