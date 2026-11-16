<?php
/**
 * Regression checks for Joomla 5/6 match times and malformed date inputs.
 *
 * Run: php .github/tests/joomla56-match-time-admin-calendar-date-guards.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Language {
    /** Test translator allowing configurable match-clock placeholders. */
    final class Text
    {
        /** @var string Translated match clock format for the active test. */
        public static string $clockFormat = '%1$s&nbsp;h';

        /**
         * Return the configured clock format or its untranslated language key.
         *
         * @param string $key Joomla language key.
         * @return string Localized text for the requested key.
         */
        public static function _(string $key): string
        {
            return $key === 'COM_SPORTSMANAGEMENT_GLOBAL_CLOCK'
                ? self::$clockFormat : $key;
        }
    }
}

namespace Joomla\CMS\HTML {
    /** Standalone Joomla HTML date formatter with a deliberate failure mode. */
    final class HTMLHelper
    {
        /** @var bool Throw when date formatting should fail. */
        public static bool $throwOnDate = false;

        /**
         * Produce a fixed match time or simulate an unsupported Joomla date.
         *
         * @param string $date SQL date input.
         * @param string $format Display format.
         * @param string $timezone Requested timezone.
         * @return string Rendered match clock.
         */
        public static function date(string $date, string $format, string $timezone): string
        {
            if (self::$throwOnDate) {
                throw new \RuntimeException('Simulated invalid date format.');
            }

            return '16:30';
        }
    }
}

namespace Joomla\CMS\Date {
    /** Minimal Joomla Date implementation for isolated module date conversion tests. */
    class Date extends \DateTime
    {
        /**
         * Construct a PHP DateTime from a Joomla-style date/timezone pair.
         *
         * @param string $date Date string or at-prefixed Unix timestamp.
         * @param string $timezone Site timezone identifier.
         */
        public function __construct(string $date = 'now', string $timezone = 'UTC')
        {
            parent::__construct($date, new \DateTimeZone($timezone));
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Site\Helper\MatchTimeHelper;
    use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper;
    use Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarDate;
    use Joomla\CMS\HTML\HTMLHelper;
    use Joomla\CMS\Language\Text;

    \define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Helper/MatchTimeHelper.php';
    require_once $root . '/admin/src/Helper/SportsManagementDateHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarDate.php';

    $game = (object) ['match_date' => '2026-11-16 16:30:00'];
    $settings = ['show_time_suffix' => true];

    foreach (['%2$s', '%s %s', '%q', str_repeat('x', 501)] as $invalidFormat) {
        Text::$clockFormat = $invalidFormat;
        $rendered = MatchTimeHelper::format($game, $settings, []);

        if ($rendered !== '16:30&nbsp;h') {
            throw new \RuntimeException('Invalid match clock placeholders were not rejected.');
        }
    }

    Text::$clockFormat = '%1$s&nbsp;h';
    HTMLHelper::$throwOnDate = true;

    if (MatchTimeHelper::format($game, $settings, []) !== '--&nbsp;:&nbsp;--') {
        throw new \RuntimeException('Joomla match date formatting exception was not handled.');
    }

    $invalid = "2026-01-01\0broken";

    if (SportsManagementDateHelper::toSqlDate($invalid) !== $invalid) {
        throw new \RuntimeException('Invalid administrator date must remain available for upstream validation.');
    }

    foreach (["2026-01-01\0", ' 2026-01-01', '2026-01-01 ', "1700000000\0"] as $date) {
        if (CalendarDate::fromValue($date, 'UTC') !== null) {
            throw new \RuntimeException('Malformed calendar date was accepted.');
        }
    }

    $valid = CalendarDate::fromValue('1700000000', 'UTC');

    if ($valid === null || $valid->getTimestamp() !== 1700000000) {
        throw new \RuntimeException('Valid calendar Unix timestamp was rejected.');
    }

    echo "Joomla 5/6 match time and date input regressions passed.\n";
}
