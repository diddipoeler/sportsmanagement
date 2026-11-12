<?php
/**
 * Verify Joomla 5/6 match-time fallbacks, geocoder address bounds and Liveticker payload.
 *
 * Usage: php .github/tests/joomla56_matchtime_geocoder_liveticker_guards.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML {
    /** Standalone test substitute for the Joomla date formatter. */
    final class HTMLHelper
    {
        /**
         * Return the input time format for deterministic formatting assertions.
         *
         * @param string $date Accepted match date.
         * @param string $format Requested Joomla time format.
         * @param string $timezone Date formatting timezone.
         * @return string Deterministic formatted value.
         */
        public static function date(string $date, string $format, string $timezone): string
        {
            return $format . '|' . $date;
        }
    }
}

namespace Joomla\CMS\Language {
    /** Minimal Joomla translation substitute for site display tests. */
    final class Text
    {
        /**
         * Return a stable translation string for the supplied key.
         *
         * @param string $key Joomla translation key.
         * @return string Translation marker suitable for assertions.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\Http {
    /** Simulates a remote geocoding response without network traffic. */
    final class HttpFactory
    {
        public static string $response = '[]';
        public static int $requests = 0;

        /**
         * Provide a local HTTP fixture with a successful JSON response.
         *
         * @return object HTTP adapter implementing a get method.
         */
        public static function getHttp(): object
        {
            return new class {
                /**
                 * Return the fixture response and count outgoing requests.
                 *
                 * @param string $url Geocoder request URL.
                 * @param array $headers HTTP request headers.
                 * @param int $timeout Maximum request time.
                 * @return object Response with status and body accessors.
                 */
                public function get(string $url, array $headers, int $timeout): object
                {
                    ++HttpFactory::$requests;

                    return new class {
                        /**
                         * Return a successful HTTP response status.
                         *
                         * @return int HTTP success status.
                         */
                        public function getStatusCode(): int
                        {
                            return 200;
                        }

                        /**
                         * Return the currently configured geocoder fixture body.
                         *
                         * @return string JSON payload.
                         */
                        public function getBody(): string
                        {
                            return HttpFactory::$response;
                        }
                    };
                }
            };
        }
    }
}

namespace {
    \define('_JEXEC', 1);
    $root = \dirname(__DIR__, 2);

    require_once $root . '/site/src/Helper/MatchTimeHelper.php';
    require_once $root . '/admin/src/Helper/LocationHelper.php';

    $match = \Diddipoeler\Component\SportsManagement\Site\Helper\MatchTimeHelper::class;

    if ($match::format((object) ['match_date' => '0000-00-00 00:00:00'], [], []) !== '--&nbsp;:&nbsp;--') {
        throw new \RuntimeException('Legacy zero database dates must not display as match times.');
    }

    if ($match::format((object) ['match_date' => '2026-11-12 19:00:00'], [], []) !== 'H:i|2026-11-12 19:00:00') {
        throw new \RuntimeException('Valid match times must still render.');
    }

    $geocoder = new \Diddipoeler\Component\SportsManagement\Administrator\Helper\LocationHelper();
    \Joomla\Http\HttpFactory::$response = \json_encode([[
        'lat' => '51.5',
        'lon' => '7.2',
        'address' => [
            'city' => 'Gelsenkirchen',
            'street' => 'Main Street',
            'control' => "bad\nvalue",
            'long' => \str_repeat('a', 256),
            'bad key' => 'unexpected',
            'nested' => ['value'],
        ],
    ]], JSON_THROW_ON_ERROR);

    $address = $geocoder->resolveDetailed('Main Street, Gelsenkirchen');

    if (($address['latitude'] ?? null) !== '51.5'
        || ($address['longitude'] ?? null) !== '7.2'
        || ($address['address'] ?? []) !== ['city' => 'Gelsenkirchen', 'street' => 'Main Street']) {
        throw new \RuntimeException('Geocoder must keep only valid address fields.');
    }

    $module = \file_get_contents(
        $root . '/modules/mod_sportsmanagement_liveticker/src/Dispatcher/Dispatcher.php'
    );

    if (!\is_string($module)
        || !\str_contains($module, 'foreach ([\'listHtml\', \'updateTimeout\', \'cssFile\', \'moduleId\'] as $key)')
        || !\str_contains($module, 'array_key_exists($key, $result)')
        || !\str_contains($module, 'return array_merge($data, $payload);')
        || \str_contains($module, 'return array_merge($data, $result);')) {
        throw new \RuntimeException('Liveticker dispatcher must preserve its Joomla layout context.');
    }

    echo "Joomla 5/6 match-time, geocoder and Liveticker payload guards: OK\n";
}
