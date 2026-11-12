<?php
/**
 * Joomla 5/6 SportsManagement administrator helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\Http\HttpFactory;

/**
 * Resolve address information required by SportsManagement club records.
 */
final class LocationHelper
{
    /**
     * Resolve one address to latitude/longitude.
     *
     * Delegates to the validated geocoding request and exposes only the two
     * coordinate fields expected by legacy administrator club forms.
     *
     * @param string $address Address text to geocode, including street and city.
     * @return array{latitude:string,longitude:string}|array{}
     */
    public function resolve(string $address): array
    {
        $result = $this->resolveDetailed($address);

        if (!isset($result['latitude'], $result['longitude'])) {
            return [];
        }

        return [
            'latitude' => (string) $result['latitude'],
            'longitude' => (string) $result['longitude'],
        ];
    }

    /**
     * Resolve coordinates and normalized OpenStreetMap address details.
     *
     * Public Nominatim usage is intentionally limited to an explicit save
     * operation. Rendering an administrator form never triggers this request.
     * Rejects control characters and excessively long addresses, validates
     * coordinate ranges, and ignores malformed response values. Bounds the
     * downloaded JSON payload before decoding to avoid excessive allocations.
     * Remote address keys and values are restricted to bounded printable text
     * to avoid propagating malformed geocoder data into administrator forms.
     *
     * @param string $address Address to resolve through the Nominatim API.
     * @return array{latitude:string,longitude:string,address:array<string,string>}|array{}
     */
    public function resolveDetailed(string $address): array
    {
        $address = trim($address);

        // The remote geocoder should only receive actual, bounded address
        // text. Reject malformed input before allocating an HTTP request.
        if ($address === ''
            || strlen($address) > 512
            || preg_match('/[\x00-\x1F\x7F]/', $address)) {
            return [];
        }

        $url = 'https://nominatim.openstreetmap.org/search'
            . '?format=jsonv2&addressdetails=1&limit=1&q=' . urlencode($address);
        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => 'SportsManagement Joomla Extension (https://github.com/diddipoeler/sportsmanagement)',
        ];

        try {
            $response = HttpFactory::getHttp()->get($url, $headers, 10);
            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();

            if ($statusCode < 200 || $statusCode >= 300 || $body === ''
                || strlen($body) > 1048576) {
                return [];
            }

            $data = json_decode($body, true, 32);
            $result = is_array($data) ? ($data[0] ?? null) : null;

            if (!is_array($result) || !isset($result['lat'], $result['lon'])) {
                return [];
            }

            // A remote geocoder response is not trusted input: booleans and
            // nested values are not coordinates, even when PHP could cast them.
            if ((!is_string($result['lat']) && !is_int($result['lat']) && !is_float($result['lat']))
                || (!is_string($result['lon']) && !is_int($result['lon']) && !is_float($result['lon']))) {
                return [];
            }

            $latitude = filter_var((string) $result['lat'], FILTER_VALIDATE_FLOAT);
            $longitude = filter_var((string) $result['lon'], FILTER_VALIDATE_FLOAT);

            if ($latitude === false || $longitude === false
                || !is_finite($latitude) || !is_finite($longitude)
                || $latitude < -90.0 || $latitude > 90.0
                || $longitude < -180.0 || $longitude > 180.0) {
                return [];
            }

            $addressDetails = [];

            if (isset($result['address']) && is_array($result['address'])) {
                foreach ($result['address'] as $key => $value) {
                    if (!is_string($key) || preg_match('/^[A-Za-z0-9_]{1,64}$/D', $key) !== 1
                        || (!is_string($value) && !is_int($value) && !is_float($value))) {
                        continue;
                    }

                    if (is_float($value) && !is_finite($value)) {
                        continue;
                    }

                    $text = trim((string) $value);

                    if ($text === '' || strlen($text) > 255
                        || preg_match('/[\x00-\x1F\x7F]/', $text) === 1) {
                        continue;
                    }

                    $addressDetails[$key] = $text;
                }
            }

            return [
                'latitude' => (string) $result['lat'],
                'longitude' => (string) $result['lon'],
                'address' => $addressDetails,
            ];
        } catch (\Throwable) {
            return [];
        }
    }
}
