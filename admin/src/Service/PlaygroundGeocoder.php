<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Http\HttpFactory;
use Joomla\Database\DatabaseInterface;

/**
 * Small Nominatim client used by the administrator playground editor.
 *
 * The service deliberately returns normalized data instead of mutating the
 * playground record so views/models can decide which values to apply.
 */
final class PlaygroundGeocoder
{
    public function __construct(private DatabaseInterface $database)
    {
    }

    /**
     * @return array{state:string, latitude:?float, longitude:?float}|null
     */
    public function geocode(object $playground): ?array
    {
        $parts = [];

        foreach (['address', 'location', 'zipcode'] as $field) {
            $value = trim((string) ($playground->{$field} ?? ''));

            if ($value !== '') {
                $parts[] = $value;
            }
        }

        $country = $this->getCountryName((string) ($playground->country ?? ''));

        if ($country !== '') {
            $parts[] = $country;
        }

        $query = implode(', ', $parts);

        if ($query === '') {
            return null;
        }

        $url = 'https://nominatim.openstreetmap.org/search'
            . '?format=jsonv2&addressdetails=1&limit=1&q=' . rawurlencode($query);

        try {
            $response = HttpFactory::getHttp()->get(
                $url,
                [
                    'Accept' => 'application/json',
                    'User-Agent' => 'SportsManagement Joomla Extension',
                ],
                5
            );
        } catch (\Throwable) {
            return null;
        }

        if ((int) ($response->code ?? 200) >= 400) {
            return null;
        }

        $data = json_decode((string) ($response->body ?? ''), true);

        if (!is_array($data) || !isset($data[0]) || !is_array($data[0])) {
            return null;
        }

        $result = $data[0];
        $address = isset($result['address']) && is_array($result['address'])
            ? $result['address']
            : [];
        $countryCode = strtolower(trim((string) ($address['country_code'] ?? '')));
        $state = $countryCode === 'gb'
            ? trim((string) ($address['county'] ?? ''))
            : trim((string) ($address['state'] ?? ''));

        if ($state === '') {
            $state = trim((string) ($address['state_district'] ?? ''));
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

    private function getCountryName(string $code): string
    {
        $code = trim($code);

        if ($code === '') {
            return '';
        }

        try {
            $query = $this->database->getQuery(true)
                ->select($this->database->quoteName('name'))
                ->from($this->database->quoteName('#__sportsmanagement_countries'))
                ->where($this->database->quoteName('alpha3') . ' = ' . $this->database->quote($code));
            $this->database->setQuery($query, 0, 1);
            $name = trim((string) $this->database->loadResult());

            return $name !== '' ? $name : $code;
        } catch (\Throwable) {
            return $code;
        }
    }

    private function coordinate(mixed $value, float $minimum, float $maximum): ?float
    {
        if (!is_numeric($value)) {
            return null;
        }

        $coordinate = (float) $value;

        return $coordinate >= $minimum && $coordinate <= $maximum ? $coordinate : null;
    }
}
