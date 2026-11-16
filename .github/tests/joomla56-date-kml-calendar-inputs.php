<?php
/**
 * Regression tests for Joomla 5/6 match dates, UTF-8 KML and calendar timestamps.
 *
 * Run: php .github/tests/joomla56-date-kml-calendar-inputs.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Date {
    /**
     * Minimal Joomla Date stand-in for isolated timezone conversion tests.
     */
    class Date extends \DateTime
    {
        /**
         * Create an ordinary mutable date with a timezone-compatible signature.
         *
         * @param string $time SQL date value.
         * @return void
         */
        public function __construct(string $time = 'now')
        {
            parent::__construct($time);
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper;
    use Diddipoeler\Component\SportsManagement\Site\Helper\ClubKmlHelper;
    use Diddipoeler\Component\SportsManagement\Site\Helper\PlaygroundKmlHelper;
    use Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarFunctions;

    \define('_JEXEC', 1);
    \define('JPATH_SITE', sys_get_temp_dir());

    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Helper/SportsManagementDateHelper.php';
    require_once $root . '/site/src/Helper/ClubKmlHelper.php';
    require_once $root . '/site/src/Helper/PlaygroundKmlHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarFunctions.php';

    foreach (['2026-02-30 13:00:00', '2026-02-25 25:00:00', '2026-02-25 12:60:00'] as $invalidDate) {
        $match = (object) ['match_date' => $invalidDate, 'timezone' => 'UTC'];
        SportsManagementDateHelper::convertMatchDateToTimezone($match);

        if ($match->match_date !== null) {
            throw new \RuntimeException('Invalid SQL match date was accepted: ' . $invalidDate);
        }
    }

    $match = (object) ['match_date' => '2024-02-29 12:30:00', 'timezone' => 'UTC'];
    SportsManagementDateHelper::convertMatchDateToTimezone($match);

    if (!$match->match_date instanceof \Joomla\CMS\Date\Date
        || $match->match_date->format('Y-m-d H:i:s') !== '2024-02-29 12:30:00') {
        throw new \RuntimeException('Valid leap-day match date was rejected.');
    }

    $invalidUtf8 = "Invalid\xC3\x28";

    if (ClubKmlHelper::write(42, 'Street', $invalidUtf8, '', 50.0, 7.0)
        || PlaygroundKmlHelper::write(42, 'Street', $invalidUtf8, 50.0, 7.0)) {
        throw new \RuntimeException('Malformed UTF-8 must not produce KML files.');
    }

    $originalTimezone = date_default_timezone_get();
    date_default_timezone_set('UTC');

    try {
        $numeric = CalendarFunctions::events(1700000000);
        $string = CalendarFunctions::events('1700000000');

        if ($numeric !== $string || !str_contains($numeric, 'November 14')) {
            throw new \RuntimeException('Numeric-string calendar timestamp differs from integer input.');
        }

        if (!str_contains(CalendarFunctions::events(str_repeat('9', 70)), 'sidebar__heading')) {
            throw new \RuntimeException('Overflowing timestamp must be handled safely.');
        }
    } finally {
        date_default_timezone_set($originalTimezone);
    }

    echo "Joomla 5/6 match date, KML and calendar input regressions passed.\n";
}
