<?php
/**
 * Test the legacy administrator Nominatim bridge without network traffic.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    final class SportsManagementAdministratorApplicationResolver
    {
        /** Return an administrator fixture to verify client resolution. */
        public static function resolve(): \Joomla\CMS\Application\CMSApplicationInterface
        {
            return new class implements \Joomla\CMS\Application\CMSApplicationInterface {};
        }
    }
}

namespace Joomla\Http {
    final class HttpFactory
    {
        public static string $body = '[]';
        public static int $status = 200;
        public static int $requests = 0;
        public static string $lastUrl = '';
        public static int $lastTimeout = 0;

        /** Return an HTTP response fixture without opening real connections. */
        public static function getHttp(): object
        {
            return new class {
                /** Validate outgoing address requests and return a stubbed result. */
                public function get(string $url, array $headers, int $timeout): object
                {
                    ++HttpFactory::$requests;
                    HttpFactory::$lastUrl = $url;
                    HttpFactory::$lastTimeout = $timeout;

                    return new class {
                        /** Return the fake HTTP response status for this test. */
                        public function getStatusCode(): int { return HttpFactory::$status; }

                        /** Return the fake JSON body for this HTTP request. */
                        public function getBody(): string { return HttpFactory::$body; }
                    };
                }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);
    define('JPATH_ADMINISTRATOR', dirname(__DIR__, 2) . '/admin');
    require_once JPATH_ADMINISTRATOR . '/src/Helper/LocationHelper.php';
    require_once JPATH_ADMINISTRATOR . '/helpers/sportsmanagement.php';

    final class LegacyAdminGeocoderFixture extends \sportsmanagementHelper {}

    $helper = new LegacyAdminGeocoderFixture();
    \Joomla\Http\HttpFactory::$body = json_encode([[
        'lat' => '51.5',
        'lon' => '7.2',
        'address' => ['state' => 'NRW', 'city' => 'Gelsenkirchen',
                      'residential' => 'Buer', 'county' => 'Ruhrgebiet',
                      'nested' => ['invalid'], 'bad' => "unsafe\nnewline"],
    ]], JSON_THROW_ON_ERROR);

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $result = $helper->getOSMGeoCoords('Bahnhofstraße 1');
        if ($result !== [
            'latitude' => '51.5',
            'longitude' => '7.2',
            'COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME' => 'NRW',
            'COM_SPORTSMANAGEMENT_LOCALITY_LONG_NAME' => 'Gelsenkirchen',
            'COM_SPORTSMANAGEMENT_SUBLOCALITY_LONG_NAME' => 'Buer',
            'COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME' => 'Ruhrgebiet',
        ]) {
            throw new \RuntimeException('Legacy address bridge lost validated geocoder fields.');
        }

        if (\Joomla\Http\HttpFactory::$requests !== 1
            || \Joomla\Http\HttpFactory::$lastTimeout !== 10
            || !str_starts_with(\Joomla\Http\HttpFactory::$lastUrl, 'https://nominatim.openstreetmap.org/search?')
            || !str_contains(\Joomla\Http\HttpFactory::$lastUrl, urlencode('Bahnhofstraße 1'))) {
            throw new \RuntimeException('Legacy Nominatim bridge bypassed native HTTPS timeout.');
        }

        foreach ([null, true, [], new \stdClass(), '', str_repeat('A', 513), "Bad\nAddress"] as $invalid) {
            if ($helper->getOSMGeoCoords($invalid) !== [] || \Joomla\Http\HttpFactory::$requests !== 1) {
                throw new \RuntimeException('Malformed geocoder address opened a request.');
            }
        }

        \Joomla\Http\HttpFactory::$status = 503;
        if ($helper->getOSMGeoCoords('Test address') !== []) {
            throw new \RuntimeException('Failed Nominatim response became valid coordinates.');
        }

        \Joomla\Http\HttpFactory::$status = 200;
        \Joomla\Http\HttpFactory::$body = '[{"lat":"999","lon":"7.2"}]';
        if ($helper->getOSMGeoCoords('Test address') !== []) {
            throw new \RuntimeException('Out-of-range geocoding coordinates were accepted.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Administrator geocoder legacy bridge regression passed.\n";
}
