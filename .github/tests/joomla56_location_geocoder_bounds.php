<?php
/**
 * Joomla 5/6 administrator geocoder coordinates must be within earth bounds.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Http {
    final class HttpFactory
    {
        public static string $body = '[]';
        public static int $status = 200;

        public static function getHttp(): object
        {
            return new class {
                public function get(string $url, array $headers, int $timeout): object
                {
                    return new class {
                        public function getStatusCode(): int
                        {
                            return HttpFactory::$status;
                        }

                        public function getBody(): string
                        {
                            return HttpFactory::$body;
                        }
                    };
                }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Helper/LocationHelper.php';

    $helper = new \Diddipoeler\Component\SportsManagement\Administrator\Helper\LocationHelper();

    foreach ([
        [['lat' => '51.5', 'lon' => '7.2'], true],
        [['lat' => '90', 'lon' => '-180'], true],
        [['lat' => '90.01', 'lon' => '7.2'], false],
        [['lat' => '-90.1', 'lon' => '7.2'], false],
        [['lat' => '1.0', 'lon' => '180.01'], false],
        [['lat' => '1.0', 'lon' => '-180.01'], false],
        [['lat' => 'INF', 'lon' => '7.2'], false],
        [['lat' => '1e999', 'lon' => '7.2'], false],
        [['lat' => true, 'lon' => 7], false],
        [['lat' => [51], 'lon' => '7.2'], false],
        [['lat' => '51.5', 'lon' => ['7']], false],
        [['lat' => null, 'lon' => '7.2'], false],
    ] as [$response, $expectedValid]) {
        \Joomla\Http\HttpFactory::$body = json_encode([$response], JSON_THROW_ON_ERROR);
        $result = $helper->resolveDetailed('Sample address');

        if (($result !== []) !== $expectedValid) {
            throw new \RuntimeException('Geocoder validation failed for ' . json_encode($response));
        }
    }

    \Joomla\Http\HttpFactory::$body = json_encode([
        ['lat' => '51.5', 'lon' => '7.2', 'address' => ['city' => 'Sample', 'bad' => ['nested']]],
    ], JSON_THROW_ON_ERROR);

    $data = $helper->resolveDetailed('Sample address');

    if (($data['latitude'] ?? null) !== '51.5'
        || ($data['longitude'] ?? null) !== '7.2'
        || ($data['address'] ?? null) !== ['city' => 'Sample']) {
        throw new \RuntimeException('Valid geocoder details were not preserved.');
    }

    echo "Joomla 5/6 geocoder coordinate bounds passed.\n";
}
