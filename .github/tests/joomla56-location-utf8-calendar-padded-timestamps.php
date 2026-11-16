<?php
/**
 * Regression checks for UTF-8 location handling and padded calendar timestamps.
 *
 * Run from the repository root:
 * php .github/tests/joomla56-location-utf8-calendar-padded-timestamps.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    /** Minimal Joomla database interface for address formatting without queries. */
    interface DatabaseInterface
    {
    }
}

namespace Joomla\Http {
    /** Prevent accidental network requests from isolated administrator tests. */
    final class HttpFactory
    {
        /**
         * Reject HTTP calls: invalid address inputs must be refused locally.
         *
         * @return never Always throws if a test reaches the network layer.
         */
        public static function getHttp(): never
        {
            throw new \RuntimeException('Invalid address unexpectedly triggered a remote HTTP request.');
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Site\Helper\LocationAddressHelper;
    use Diddipoeler\Component\SportsManagement\Administrator\Helper\LocationHelper;
    use Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarFunctions;

    \define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    require_once $root . '/site/src/Helper/LocationAddressHelper.php';
    require_once $root . '/admin/src/Helper/LocationHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarFunctions.php';

    $database = new class implements \Joomla\Database\DatabaseInterface {
    };
    $bad = "broken\xC3\x28";
    $location = (object) [
        'address' => $bad,
        'state' => 'NRW',
        'location' => $bad,
        'city' => 'Gelsenkirchen',
        'zipcode' => '45879',
    ];

    $formatted = LocationAddressHelper::build($database, $location);

    if ($formatted !== 'NRW, 45879 Gelsenkirchen') {
        throw new \RuntimeException('Malformed UTF-8 location fields must be discarded: ' . $formatted);
    }

    $admin = new LocationHelper();

    if ($admin->resolveDetailed($bad) !== []) {
        throw new \RuntimeException('Administrator geocoding must reject malformed UTF-8 before HTTP.');
    }

    $previousTimezone = date_default_timezone_get();
    date_default_timezone_set('UTC');

    try {
        $normal = CalendarFunctions::events(1700000000);

        foreach (['1700000000', '0001700000000', '+0001700000000'] as $timestamp) {
            if (CalendarFunctions::events($timestamp) !== $normal) {
                throw new \RuntimeException('Padded calendar Unix timestamp was not preserved: ' . $timestamp);
            }
        }

        if (CalendarFunctions::events('-0001700000000') !== CalendarFunctions::events(-1700000000)) {
            throw new \RuntimeException('Padded negative Unix timestamp was not preserved.');
        }

        if (!str_contains(CalendarFunctions::events((string) PHP_INT_MAX . '9'), 'sidebar__heading')) {
            throw new \RuntimeException('Out-of-range calendar timestamp did not fall back safely.');
        }
    } finally {
        date_default_timezone_set($previousTimezone);
    }

    echo "Joomla 5/6 location UTF-8 and padded calendar timestamp regressions passed.\n";
}
