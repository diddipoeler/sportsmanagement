<?php
/**
 * Joomla 5/6 playground geocoder HTTP status and JSON data regression tests.
 *
 * Usage: php .github/tests/joomla56_playground_geocoder_validation.php
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType
    {
        public const STRING = 'string';
    }
}

namespace Joomla\Http {
    final class HttpFactory
    {
        public static ?object $http = null;
        public static function getHttp(): object
        {
            return self::$http;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Service/PlaygroundGeocoder.php';

    final class FakeGeocodeQuery
    {
        public array $binds = [];
        public function select(string $col): self { return $this; }
        public function from(string $table): self { return $this; }
        public function where(string $condition): self { return $this; }
        public function bind(string $placeholder, mixed $value, string $type): self
        {
            $this->binds[$placeholder] = $value;
            return $this;
        }
    }

    final class FakeGeocodeDatabase implements \Joomla\Database\DatabaseInterface
    {
        public ?FakeGeocodeQuery $query = null;
        public function createQuery(): FakeGeocodeQuery { return new FakeGeocodeQuery(); }
        public function quoteName(string $name): string { return $name; }
        public function setQuery(FakeGeocodeQuery $query, int $offset = 0, int $limit = 0): void
        {
            $this->query = $query;
        }
        public function loadResult(): string { return 'Germany'; }
    }

    final class FakeGeocodeHttp
    {
        public int $status = 200;
        public string $body = '[]';
        public bool $fail = false;
        public ?string $lastUrl = null;
        public function get(string $url, array $headers = [], int $timeout = 0): object
        {
            $this->lastUrl = $url;

            if ($this->fail) {
                throw new \RuntimeException('Simulated Nominatim timeout');
            }

            if ($timeout !== 5 || !str_starts_with($url, 'https://nominatim.openstreetmap.org/search?')) {
                throw new \RuntimeException('Geocoder changed HTTPS endpoint or timeout.');
            }

            return new class($this->status, $this->body) {
                public function __construct(private int $status, private string $body) {}
                public function getStatusCode(): int { return $this->status; }
                public function getBody(): string { return $this->body; }
            };
        }
    }

    $database = new FakeGeocodeDatabase();
    $http = new FakeGeocodeHttp();
    \Joomla\Http\HttpFactory::$http = $http;
    $geocoder = new \Diddipoeler\Component\SportsManagement\Administrator\Service\PlaygroundGeocoder($database);

    $playground = (object) ['address' => 'Allee 1', 'zipcode' => '10115', 'city' => 'Berlin', 'country' => 'DEU'];
    $http->body = json_encode([[
        'lat' => '52.52',
        'lon' => '13.405',
        'address' => ['country_code' => 'de', 'state' => 'Berlin'],
    ]], JSON_THROW_ON_ERROR);
    $result = $geocoder->geocode($playground);

    if ($result !== ['state' => 'Berlin', 'latitude' => 52.52, 'longitude' => 13.405]
        || ($database->query?->binds[':code'] ?? null) !== 'DEU'
        || !str_contains((string) $http->lastUrl, rawurlencode('Allee 1, Berlin, 10115, Germany'))) {
        throw new \RuntimeException('Valid Nominatim location or country lookup failed.');
    }

    foreach ([199, 301, 302, 400, 503] as $status) {
        $http->status = $status;
        if ($geocoder->geocode($playground) !== null) {
            throw new \RuntimeException('Geocoder accepted a non-success HTTP status ' . $status);
        }
    }

    $http->status = 200;
    $http->fail = true;
    if ($geocoder->geocode($playground) !== null) {
        throw new \RuntimeException('Geocoder failed to handle network outage.');
    }
    $http->fail = false;

    $http->body = json_encode([[
        'lat' => '999',
        'lon' => '-999',
        'address' => [
            'country_code' => ['gb'],
            'county' => ['London'],
            'state' => ['invalid'],
            'state_district' => (object) ['nested' => 'invalid'],
        ],
    ]], JSON_THROW_ON_ERROR);
    set_error_handler(static function (int $severity, string $message): never {
        throw new \RuntimeException('Warning caused by untrusted location data: ' . $message);
    });
    try {
        if ($geocoder->geocode($playground) !== null) {
            throw new \RuntimeException('Invalid Nominatim address became a state name.');
        }
    } finally {
        restore_error_handler();
    }

    $http->body = json_encode([[
        'lat' => '51.5',
        'lon' => '-0.13',
        'address' => ['country_code' => 'gb', 'county' => 'Greater London', 'state' => 'England'],
    ]], JSON_THROW_ON_ERROR);
    if ($geocoder->geocode($playground) !== [
        'state' => 'Greater London', 'latitude' => 51.5, 'longitude' => -0.13,
    ]) {
        throw new \RuntimeException('UK county field regression in geocoder.');
    }

    echo "Joomla 5/6 Nominatim HTTP and location validation OK\n";
}
