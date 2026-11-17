<?php
/**
 * Regression checks for ambiguous matches, geocoder fields and calendar controls.
 *
 * Run: php .github/tests/joomla56-match-geocoder-calendar-controls.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Date {
    /** Minimal Joomla date wrapper for standalone calendar validation. */
    class Date extends \DateTime
    {
        /**
         * Parse a Joomla-style time and its configured timezone.
         *
         * @param string $date Date text or at-prefixed timestamp.
         * @param string $timezone Requested date timezone.
         */
        public function __construct(string $date = 'now', string $timezone = 'UTC')
        {
            parent::__construct($date, new \DateTimeZone($timezone));
        }
    }
}

namespace Joomla\Http {
    /** Fake Joomla HTTP factory that returns controlled geocoding content. */
    final class HttpFactory
    {
        /**
         * Return an HTTP client without opening network connections.
         *
         * @return object Client with a get() method.
         */
        public static function getHttp(): object
        {
            return new class {
                /**
                 * Return one successful fake Nominatim lookup response.
                 *
                 * @param string $url Geocoding request URL.
                 * @param array $headers HTTP request headers.
                 * @param int $timeout Maximum request duration.
                 * @return object HTTP response with JSON body.
                 */
                public function get(string $url, array $headers, int $timeout): object
                {
                    return new class {
                        /**
                         * Return the simulated HTTP success status.
                         *
                         * @return int HTTP response status.
                         */
                        public function getStatusCode(): int
                        {
                            return 200;
                        }

                        /**
                         * Return valid coordinates and malformed address details.
                         *
                         * @return string JSON geocoder response.
                         */
                        public function getBody(): string
                        {
                            return json_encode([[
                                'lat' => '51.5',
                                'lon' => '7.2',
                                'address' => [
                                    'road' => "Invalid street\n",
                                    'city' => 'Gelsenkirchen',
                                    'postcode' => "45879\0",
                                    'state' => 'NRW',
                                ],
                            ]], JSON_THROW_ON_ERROR);
                        }
                    };
                }
            };
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Helper\LocationHelper;
    use Diddipoeler\Component\SportsManagement\Site\Helper\MatchResultHelper;
    use Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarDate;

    \define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    require_once $root . '/site/src/Helper/MatchResultHelper.php';
    require_once $root . '/admin/src/Helper/LocationHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarDate.php';

    $badFixture = (object) [
        'projectteam1_id' => '14',
        'projectteam2_id' => '14',
        'team1_result' => '3',
        'team2_result' => '1',
    ];

    if (MatchResultHelper::outcome($badFixture, 14) !== false) {
        throw new \RuntimeException('Ambiguous duplicate-team result was accepted.');
    }

    $badFixture->projectteam2_id = '22';

    if (MatchResultHelper::outcome($badFixture, 14) !== 1
        || MatchResultHelper::outcome($badFixture, 22) !== -1
        || MatchResultHelper::outcome($badFixture, 99) !== false) {
        throw new \RuntimeException('Valid home/away results no longer resolve correctly.');
    }

    $address = (new LocationHelper())->resolveDetailed('Gelsenkirchen, Deutschland');

    if (($address['latitude'] ?? null) !== '51.5'
        || ($address['longitude'] ?? null) !== '7.2'
        || ($address['address']['city'] ?? null) !== 'Gelsenkirchen'
        || ($address['address']['state'] ?? null) !== 'NRW'
        || isset($address['address']['road'], $address['address']['postcode'])) {
        throw new \RuntimeException('Invalid remote geocoder controls survived address normalization.');
    }

    foreach (["2026-11-17\n12:30:00", "2026-11-17\0", "2026-11-17\x01"] as $date) {
        if (CalendarDate::fromValue($date, 'UTC') !== null) {
            throw new \RuntimeException('Calendar accepted a date with raw control characters.');
        }
    }

    if (CalendarDate::fromValue('1700000000', 'UTC')?->getTimestamp() !== 1700000000) {
        throw new \RuntimeException('Valid calendar timestamps no longer work.');
    }

    echo "Joomla 5/6 match, geocoder and calendar regressions passed.\n";
}
