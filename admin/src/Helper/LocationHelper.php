<?php
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
     *
     * @return array{latitude:string,longitude:string,address:array<string,string>}|array{}
     */
    public function resolveDetailed(string $address): array
    {
        $address = trim($address);

        if ($address === '') {
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

            if ($statusCode < 200 || $statusCode >= 300 || $body === '') {
                return [];
            }

            $data = json_decode($body, true);
            $result = is_array($data) ? ($data[0] ?? null) : null;

            if (!is_array($result) || !isset($result['lat'], $result['lon'])) {
                return [];
            }

            $latitude = filter_var((string) $result['lat'], FILTER_VALIDATE_FLOAT);
            $longitude = filter_var((string) $result['lon'], FILTER_VALIDATE_FLOAT);

            if ($latitude === false || $longitude === false) {
                return [];
            }

            $addressDetails = [];

            if (isset($result['address']) && is_array($result['address'])) {
                foreach ($result['address'] as $key => $value) {
                    if (!is_scalar($value)) {
                        continue;
                    }

                    $addressDetails[(string) $key] = trim((string) $value);
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
