<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\Http\HttpFactory;

/**
 * Resolve the coordinates required by SportsManagement club records.
 */
final class LocationHelper
{
    /**
     * Preserve the coordinate part of the historic resolveLocation() contract.
     *
     * @return array{latitude:mixed,longitude:mixed}|array{}
     */
    public function resolve(string $address): array
    {
        $address = trim($address);

        if ($address === '') {
            return [];
        }

        $url = 'https://maps.google.com/maps/api/geocode/json?address='
            . urlencode($address)
            . '&sensor=false&language=de';

        try {
            $response = (new HttpFactory())->getHttp()->get($url);
            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();

            if ($statusCode < 200 || $statusCode >= 300 || $body === '') {
                return [];
            }

            $data = json_decode($body);

            if (!is_object($data) || ($data->status ?? null) !== 'OK') {
                return [];
            }

            $location = $data->results[0]->geometry->location ?? null;

            if (!is_object($location) || !isset($location->lat, $location->lng)) {
                return [];
            }

            return [
                'latitude' => $location->lat,
                'longitude' => $location->lng,
            ];
        } catch (\Throwable) {
            return [];
        }
    }
}
