<?php
/**
 * Native Joomla 5/6 Google Calendar event layout.
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

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$title = trim((string) ($event['title'] ?? ''));
$calendarName = trim((string) ($event['calendarName'] ?? ''));
$description = trim((string) ($event['description'] ?? ''));
$location = trim((string) ($event['location'] ?? ''));
$start = trim((string) ($event['start'] ?? ''));
$end = trim((string) ($event['end'] ?? ''));
$allDay = !empty($event['allDay']);
$calendarId = (int) ($event['gcid'] ?? 0);
$color = (string) ($event['color'] ?? '#135CAE');

if ($title === '') {
    $title = $calendarName !== '' ? $calendarName : (string) ($event['id'] ?? '');
}

if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
    $color = '#135CAE';
}

$googleUrl = trim((string) ($event['htmlLink'] ?? ''));
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
$dateFormat = (string) $params->get('description_date_format', 'd.m.Y');
$timeFormat = (string) $params->get('description_time_format', 'H:i');
$timezoneName = (string) $this->app->get('offset', 'UTC');

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
