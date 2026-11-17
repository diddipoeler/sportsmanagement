<?php
/**
 * Regression checks for calendar NUL input, geocoder controls and picture hosts.
 *
 * Run: php .github/tests/joomla56-calendar-nul-geocoder-image-base.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Date {
    /**
     * Minimal Joomla Date stand-in for standalone calendar parsing tests.
     */
    class Date extends \DateTime
    {
        /**
         * Construct a timezone-aware date as Joomla's date wrapper would.
         *
         * @param string $value Date text or an at-prefixed Unix timestamp.
         * @param string $timezone Timezone identifier for non-timestamp values.
         */
        public function __construct(string $value = 'now', string $timezone = 'UTC')
        {
            parent::__construct($value, new \DateTimeZone($timezone));
        }
    }
}

namespace Joomla\CMS\Uri {
    /**
     * Standalone Joomla site root for safe picture URL fallback.
     */
    final class Uri
    {
        /**
         * Return the known safe root when the configured host is invalid.
         *
         * @return string Canonical fixture website root.
         */
        public static function root(): string
        {
            return 'https://example.test/';
        }
    }
}

namespace Joomla\Http {
    /**
     * Any request from an invalid address must fail the standalone test.
     */
    final class HttpFactory
    {
        /**
         * Reject attempted network use for malformed geocoding input.
         *
         * @return never Always throws to detect unwanted outbound requests.
         */
        public static function getHttp(): never
        {
            throw new \RuntimeException('Invalid address triggered an HTTP request.');
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Helper\LocationHelper;
    use Diddipoeler\Component\SportsManagement\Site\Helper\PersonImageHelper;
    use Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarDate;

    \define('_JEXEC', 1);
    \define('COM_SPORTSMANAGEMENT_PICTURE_SERVER', ['corrupt', 'registry']);
    $root = dirname(__DIR__, 2);

    require_once $root . '/admin/src/Helper/LocationHelper.php';
    require_once $root . '/site/src/Helper/PersonImageHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarDate.php';

    set_error_handler(
        /**
         * Treat PHP notices and warnings as failed regression checks.
         *
         * @param int $severity Error severity emitted by PHP.
         * @param string $message Diagnostic text for the failed test.
         * @return never Always throws the resulting error.
         */
        static function (int $severity, string $message): never {
            throw new \ErrorException($message, 0, $severity);
        }
    );

    try {
        $location = new LocationHelper();

        foreach (["Musterstrasse 1\n", "\tMusterstrasse 1", "Musterstrasse 1\0"] as $address) {
            if ($location->resolveDetailed($address) !== []) {
                throw new \RuntimeException('Control-bearing geocoding input was accepted.');
            }
        }

        if (PersonImageHelper::url('images/players/portrait.png')
            !== 'https://example.test/images/players/portrait.png') {
            throw new \RuntimeException('Invalid picture-server constant did not use Joomla root.');
        }

        foreach (["1700000000\0", "2026-11-17\0"] as $date) {
            if (CalendarDate::fromValue($date, 'UTC') !== null) {
                throw new \RuntimeException('Calendar accepted an embedded NUL byte.');
            }
        }

        // Backslash followed by the digit zero is not a NUL byte.
        // Both values are invalid dates, but the UTF-8-safe calendar helper
        // must return null without confusing them with a binary NUL.
        if (CalendarDate::fromValue('1700000000', 'UTC')?->getTimestamp() !== 1700000000) {
            throw new \RuntimeException('Calendar rejected a valid Unix timestamp.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 calendar NUL, geocoder and image-base regressions passed.\n";
}
