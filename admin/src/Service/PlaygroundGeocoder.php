<?php
/**
 * Joomla 5/6 playground geocoding service using OpenStreetMap Nominatim.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Http\HttpFactory;

/**
 * Small Nominatim client used by the administrator playground editor.
 *
 * The service deliberately returns normalized data instead of mutating the
 * playground record so views/models can decide which values to apply.
 */
final class PlaygroundGeocoder
{
    /**
     * Create the administrator playground geocoder with a database connection.
     * Uses this driver to resolve SportsManagement country codes before composing geocoding queries.
     *
     * @param DatabaseInterface $database Joomla connection used for country lookups.
     * @return void
     */
    public function __construct(private DatabaseInterface $database)
    {
    }

    /**
     * Look up a playground's state and coordinates with OpenStreetMap Nominatim.
     *
     * Builds a bounded, printable address from legacy playground fields,
     * discards nested imports and never sends malformed values to Nominatim.
     * Performs a timed HTTP lookup, bounds the JSON response and validates
     * external data before returning normalised values.
     *
     * @param object $playground Address, city, ZIP code and country of the venue.
     * @return array{state:string, latitude:?float, longitude:?float}|null Geocoded data or null on failure.
     */
    public function geocode(object $playground): ?array
    {
        $parts = [];
        $address = $this->stringValue($playground->address ?? null);

        if ($address !== '') {
            $parts[] = $address;
        }

        $city = $this->stringValue($playground->city ?? null);

        if ($city === '') {
            $city = $this->stringValue($playground->location ?? null);
        }

        if ($city !== '') {
            $parts[] = $city;
        }

        $zipcode = $this->stringValue($playground->zipcode ?? null);

        if ($zipcode !== '') {
            $parts[] = $zipcode;
        }

        $country = $this->getCountryName($this->stringValue($playground->country ?? null));

        if ($country !== '') {
            $parts[] = $country;
        }

        $query = implode(', ', $parts);

        if ($query === '' || strlen($query) > 512) {
            return null;
        }

        $url = 'https://nominatim.openstreetmap.org/search'
            . '?format=jsonv2&addressdetails=1&limit=1&q=' . rawurlencode($query);

        try {
            $response = (new HttpFactory())->getHttp()->get(
                $url,
                [
                    'Accept' => 'application/json',
                    'User-Agent' => 'SportsManagement Joomla Extension (https://github.com/diddipoeler/sportsmanagement)',
                ],
                5
            );
        } catch (\Throwable) {
            return null;
        }

        $status = $response->getStatusCode();

        if ($status < 200 || $status >= 300) {
            return null;
        }

        $body = (string) $response->getBody();

        if ($body === '' || strlen($body) > 1048576) {
            return null;
        }

        $data = json_decode($body, true, 32);

        if (!is_array($data) || !isset($data[0]) || !is_array($data[0])) {
            return null;
        }

        $result = $data[0];
        $addressData = isset($result['address']) && is_array($result['address'])
            ? $result['address']
            : [];
        $countryCode = $this->stringValue($addressData['country_code'] ?? null);
        $state = strtolower($countryCode) === 'gb'
            ? $this->stringValue($addressData['county'] ?? null)
            : $this->stringValue($addressData['state'] ?? null);

        if ($state === '') {
            $state = $this->stringValue($addressData['state_district'] ?? null);
        }

        $latitude = $this->coordinate($result['lat'] ?? null, -90.0, 90.0);
        $longitude = $this->coordinate($result['lon'] ?? null, -180.0, 180.0);

        if ($state === '' && $latitude === null && $longitude === null) {
            return null;
        }

        return [
            'state' => $state,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }

    /**
     * Expand a country code into a name suitable for geocoding searches.
     * Only valid ISO alpha-3 codes are sent to the parameter-bound lookup.
     * Non-string results are discarded; unknown countries retain their code
     * without generating PHP 8 array-to-string warnings.
     *
     * @param string $code SportsManagement three-letter country code.
     * @return string Country name, original code or empty string.
     */
    private function getCountryName(string $code): string
    {
        $code = strtoupper(trim($code));

        if (preg_match('/^[A-Z]{3}$/D', $code) !== 1) {
            return '';
        }

        try {
            $query = $this->database->createQuery()
                ->select($this->database->quoteName('name'))
                ->from($this->database->quoteName('#__sportsmanagement_countries'))
                ->where($this->database->quoteName('alpha3') . ' = :code')
                ->bind(':code', $code, ParameterType::STRING);
            $this->database->setQuery($query, 0, 1);
            $name = $this->stringValue($this->database->loadResult());

            return $name !== '' ? $name : $code;
        } catch (\Throwable) {
            return $code;
        }
    }

    /**
     * Read bounded, printable plain text from an imported or remote field.
     *
     * Rejects non-string values, control characters and oversized input
     * instead of coercing malformed arrays or objects into location data.
     * Used for both administrator settings and Nominatim JSON responses.
     *
     * @param mixed $value Untrusted address or country field.
     * @return string Safe trimmed text or an empty string.
     */
    private function stringValue(mixed $value): string
    {
        if (!is_string($value) || strlen($value) > 255
            || preg_match('/[\x00-\x1F\x7F]/', $value) === 1) {
            return '';
        }

        return trim($value);
    }

    /**
     * Validate a numeric coordinate returned by the external geocoding service.
     * Rejects nonnumeric, nonfinite and out-of-range latitude or longitude values rather than storing invalid coordinates.
     *
     * @param mixed $value External coordinate value.
     * @param float $minimum Allowed lower boundary.
     * @param float $maximum Allowed upper boundary.
     * @return float|null Valid coordinate or null.
     */
    private function coordinate(mixed $value, float $minimum, float $maximum): ?float
    {
        if ((!is_int($value) && !is_float($value) && !is_string($value))
            || !is_numeric($value)) {
            return null;
        }

        $coordinate = (float) $value;

        return is_finite($coordinate) && $coordinate >= $minimum && $coordinate <= $maximum
            ? $coordinate
            : null;
    }
}
