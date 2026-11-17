<?php
/**
 * Regression tests for modal links, administrator extra fields and calendar year bounds.
 *
 * Run: php .github/tests/joomla56-modal-extra-fields-calendar-bounds.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace {
    use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
    use Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarFunctions;

    \define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    require_once $root . '/site/src/Helper/ModalImageHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarFunctions.php';

    // Mode 1 produces a popup link without needing Joomla's modal factory.
    foreach ([
        '../private.png',
        '%2e%2e/private.png',
        '%252e%252e/private.png',
        'folder/%2e%2e/private.png',
        'javascript:alert(1)',
        '//example.test/picture.png',
        "bad\x00source.png",
    ] as $invalidPicture) {
        if (ModalImageHelper::render('team-popup', $invalidPicture, 'Club', 20, '', 200, 200, 1) !== '') {
            throw new \RuntimeException('Unsafe modal picture was not rejected: ' . $invalidPicture);
        }
    }

    $safe = ModalImageHelper::render(
        'team-popup', 'images/clubs/logo.png', 'Club & Friends', 20, '', 200, 200, 1
    );

    if (!str_contains($safe, 'src="images/clubs/logo.png"')
        || !str_contains($safe, 'Club &amp; Friends')) {
        throw new \RuntimeException('Valid modal image or escaped alt text was lost.');
    }

    // A caller-supplied schema name must not become an executable HTML
    // event-handler attribute on the resulting image tag.
    $invalidSchema = ModalImageHelper::render(
        'team-popup', 'images/clubs/logo.png', 'Club', 20, '', 200, 200, 1,
        'onerror', 'alert(1)'
    );

    if (str_contains($invalidSchema, 'onerror=')
        || !str_contains($invalidSchema, 'itemprop="alert(1)"')) {
        throw new \RuntimeException('Unsafe HTML schema attribute was rendered.');
    }

    $allowedSchema = ModalImageHelper::render(
        'team-popup', 'images/clubs/logo.png', 'Club', 20, '', 200, 200, 1,
        'property', 'og:image'
    );

    if (!str_contains($allowedSchema, 'property="og:image"')) {
        throw new \RuntimeException('Valid image schema metadata attribute was rejected.');
    }

    $invalidDestination = ModalImageHelper::render(
        'team-popup', 'images/clubs/logo.png', 'Club', 20, '../hidden', 200, 200, 1
    );

    if (str_contains($invalidDestination, 'href="../hidden"')) {
        throw new \RuntimeException('Modal link traversal must not be emitted.');
    }

    $admin = file_get_contents($root . '/admin/src/Helper/ExtraFieldsSaveHelper.php');

    if (!is_string($admin)
        || !str_contains($admin, 'str_contains($value, "\\0")')
        || strpos($admin, 'str_contains($value, "\\0")') > strpos($admin, '->delete(')) {
        throw new \RuntimeException('Extra-field null-byte validation must precede deletion.');
    }

    if (!str_contains($admin, 'strlen($value) > 65535')) {
        throw new \RuntimeException('Administrator extra-field size guard is missing.');
    }

    foreach ([
        [1970, 1, '1970', '1', '1970', '2'],
        [9999, 12, '9999', '11', '9999', '12'],
    ] as [$year, $month, $previousYear, $previousNumber, $nextYear, $nextNumber]) {
        ob_start();

        try {
            CalendarFunctions::render($year, $month);
            $html = (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }

        // January 1970 must still show the final dates of December 1969
        // in its inactive first-week cells even when previous navigation is
        // intentionally clamped to the first supported calendar month.
        if ($year === 1970 && $month === 1
            && !str_contains($html, '<span class="calendar__date">29</span>')) {
            throw new \RuntimeException('Calendar previous-month filler lost December 1969.');
        }

        // Extract the navigation values independently of HTML whitespace and
        // attribute ordering, then check the exact supported boundary dates.
        $previous = [];
        $next = [];
        preg_match('~<a\\b[^>]*class="title-bar__prev"[^>]*>~s', $html, $previousLink);
        preg_match('~<a\\b[^>]*class="title-bar__next"[^>]*>~s', $html, $nextLink);

        preg_match('~data-calendar-year="([0-9]+)"~', $previousLink[0] ?? '', $previousYearMatch);
        preg_match('~data-calendar-month="([0-9]+)"~', $previousLink[0] ?? '', $previousMonthMatch);
        preg_match('~data-calendar-year="([0-9]+)"~', $nextLink[0] ?? '', $nextYearMatch);
        preg_match('~data-calendar-month="([0-9]+)"~', $nextLink[0] ?? '', $nextMonthMatch);

        if (($previousYearMatch[1] ?? null) !== $previousYear
            || ($previousMonthMatch[1] ?? null) !== $previousNumber
            || ($nextYearMatch[1] ?? null) !== $nextYear
            || ($nextMonthMatch[1] ?? null) !== $nextNumber) {
            throw new \RuntimeException(
                'Unexpected calendar navigation for ' . $year . '-' . $month
                . ': previous=' . ($previousLink[0] ?? 'missing')
                . ', next=' . ($nextLink[0] ?? 'missing')
            );
        }
    }

    // Calendar dropdowns must reject raw control characters instead of
    // turning "2\n" or "2000\n" into legitimate selected options.
    if (!str_contains(CalendarFunctions::monthList('2'), 'value="02" selected')
        || str_contains(CalendarFunctions::monthList("2\n"), ' selected')
        || str_contains(CalendarFunctions::monthList(str_repeat('9', 100000)), ' selected')) {
        throw new \RuntimeException('Calendar month selector accepted invalid raw input.');
    }

    if (!str_contains(CalendarFunctions::yearList('2000'), 'value="2000" selected')
        || str_contains(CalendarFunctions::yearList("2000\n"), 'value="2000" selected')) {
        throw new \RuntimeException('Calendar year selector accepted invalid raw input.');
    }

    echo "Joomla 5/6 modal, extra-field and calendar boundary regressions passed.\n";
}
