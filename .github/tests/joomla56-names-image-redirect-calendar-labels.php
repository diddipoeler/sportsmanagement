<?php
/**
 * Test native person-name, remote image redirect and calendar label safeguards.
 *
 * Run: php .github/tests/joomla56-names-image-redirect-calendar-labels.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace {
    use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
    use Diddipoeler\Component\SportsManagement\Administrator\Helper\RemoteImageDownloadHelper;
    use Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarRenderer;

    \define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Helper/PersonNameFormatter.php';
    require_once $root . '/admin/src/Helper/RemoteImageDownloadHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarRenderer.php';

    if (PersonNameFormatter::format(null, 'Ada', '', 'Lovelace', 3) !== 'Ada Lovelace') {
        throw new \RuntimeException('Valid frontend name formatting regressed.');
    }

    foreach (["broken\xC3\x28", "Ada\n", "Ada\0", str_repeat('x', 1025)] as $badName) {
        if (PersonNameFormatter::format(null, $badName, '', 'Lovelace', 3) !== 'Lovelace') {
            throw new \RuntimeException('Malformed first name reached frontend HTML.');
        }

        if (PersonNameFormatter::format(null, 'Ada', '', $badName, 3) !== 'Ada') {
            throw new \RuntimeException('Malformed surname reached frontend HTML.');
        }

        if (PersonNameFormatter::format($badName, 'Ada', '', 'Lovelace', 3) !== 'Ada Lovelace') {
            throw new \RuntimeException('Malformed prefix reached frontend HTML.');
        }

        if (PersonNameFormatter::format(null, 'Ada', $badName, 'Lovelace', 0) !== 'Ada Lovelace') {
            throw new \RuntimeException('Malformed nickname reached frontend HTML.');
        }
    }

    // Verify the raw header guard directly without real network traffic.
    $redirect = new \ReflectionMethod(RemoteImageDownloadHelper::class, 'resolveRedirect');
    $downloader = new RemoteImageDownloadHelper();

    foreach ([
        "https://example.org/photo.jpg\n",
        "\thttps://example.org/photo.jpg",
        "https://example.org/photo.jpg\0",
        "https://example.org/photo.jpg\r",
    ] as $badHeader) {
        if ($redirect->invoke($downloader, 'https://example.org/base.jpg', $badHeader) !== null) {
            throw new \RuntimeException('Remote image redirect accepted raw control bytes.');
        }
    }

    $calendar = new CalendarRenderer();
    // Legacy module parameters must contain complete numeric tokens.
    $calendar->setStartDay('05');

    if ($calendar->getStartDay() !== 5) {
        throw new \RuntimeException('Valid zero-padded weekday setting was rejected.');
    }

    foreach (["5\n", " 5", "5\0", '5suffix', str_repeat('9', 1000), []] as $badDay) {
        $calendar->setStartDay($badDay);

        if ($calendar->getStartDay() !== 0) {
            throw new \RuntimeException('Invalid calendar start weekday was accepted.');
        }
    }

    $calendar->setStartMonth('09');

    if ($calendar->getStartMonth() !== 9) {
        throw new \RuntimeException('Valid zero-padded calendar month was rejected.');
    }

    foreach (["9\n", "\t9", "9\0", '9suffix', str_repeat('9', 1000), []] as $badMonth) {
        $calendar->setStartMonth($badMonth);

        if ($calendar->getStartMonth() !== 1) {
            throw new \RuntimeException('Invalid calendar start month was accepted.');
        }
    }

    $originalDays = $calendar->getDayNames();
    $originalMonths = $calendar->getMonthNames();

    foreach ([
        "Invalid\0",
        "Invalid\n",
        "broken\xC3\x28",
        str_repeat('x', 101),
    ] as $badLabel) {
        $days = $originalDays;
        $days[2] = $badLabel;
        $calendar->setDayNames($days);

        if ($calendar->getDayNames() !== $originalDays) {
            throw new \RuntimeException('Calendar accepted malformed weekday label.');
        }

        $months = $originalMonths;
        $months[2] = $badLabel;
        $calendar->setMonthNames($months);

        if ($calendar->getMonthNames() !== $originalMonths) {
            throw new \RuntimeException('Calendar accepted malformed month label.');
        }
    }

    $days = $originalDays;
    $days[2] = 'Di';
    $calendar->setDayNames($days);

    if ($calendar->getDayNames() !== $days) {
        throw new \RuntimeException('Valid translated weekday label was rejected.');
    }

    $months = $originalMonths;
    $months[2] = 'März';
    $calendar->setMonthNames($months);

    if ($calendar->getMonthNames() !== $months) {
        throw new \RuntimeException('Valid translated month label was rejected.');
    }

    echo "Joomla 5/6 names, image redirects and calendar labels passed.\n";
}
