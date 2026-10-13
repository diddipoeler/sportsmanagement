<?php
/**
 * Runtime regressions for Joomla 5/6 site referees, admin player validation and Google Calendar.
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
        public const INTEGER = 'integer';
        public const STRING = 'string';
    }
}

namespace Joomla\CMS\Date {
    final class Date
    {
        public static function getInstance(): self { return new self(); }
        public function toISO8601(): string { return '2026-10-13T00:00:00+00:00'; }
    }
}

namespace Joomla\Http {
    final class HttpFactory
    {
        public static array $options = [];
        public static array $adapters = [];
        public static ?object $http = null;

        public function getHttp(array $options = [], array $adapters = []): object
        {
            self::$options = $options;
            self::$adapters = $adapters;

            return self::$http;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Service/MatchReadService.php';
    require_once $root . '/admin/src/Service/PlayerPersistenceService.php';
    require_once $root . '/modules/mod_sportsmanagement_google_calendar/src/Helper/GoogleCalendarHelper.php';

    final class MatchQueryStub
    {
        public array $selects = [];
        public array $binds = [];

        public function select(array|string $columns): self
        {
            array_push($this->selects, ...(array) $columns);
            return $this;
        }

        public function from(string $table): self { return $this; }
        public function join(string $kind, string $clause): self { return $this; }
        public function where(string $condition): self { return $this; }
        public function order(string $condition): self { return $this; }
        public function bind(string $key, mixed $value, string $type): self
        {
            $this->binds[$key] = $value;
            return $this;
        }
    }

    final class MatchDatabaseStub implements \Joomla\Database\DatabaseInterface
    {
        public ?MatchQueryStub $lastQuery = null;
        public int $loads = 0;

        public function createQuery(): MatchQueryStub { return new MatchQueryStub(); }
        public function quoteName(string $column, ?string $alias = null): string
        {
            return $alias === null ? $column : $column . ' AS ' . $alias;
        }
        public function setQuery(MatchQueryStub $query): self
        {
            $this->lastQuery = $query;
            return $this;
        }
        public function loadObjectList(): array
        {
            ++$this->loads;
            return [
                (object) ['id' => 7, 'alias' => 'referee-a', 'firstname' => 'Jana'],
                (object) ['id' => 9, 'alias' => '', 'firstname' => 'Max'],
            ];
        }
    }

    $db = new MatchDatabaseStub();
    $reader = new \Diddipoeler\Component\SportsManagement\Site\Service\MatchReadService($db);
    if ($reader->getReferees(0) !== [] || $db->loads !== 0) {
        throw new \RuntimeException('Invalid match IDs must not reach referee queries.');
    }

    $referees = $reader->getReferees(42);
    if ($db->loads !== 1
        || array_map(static fn(object $row): string => $row->person_slug, $referees) !== ['7:referee-a', '9:']
        || !in_array('p.alias', $db->lastQuery?->selects ?? [], true)
        || str_contains(implode(' ', $db->lastQuery?->selects ?? []), 'CONCAT_WS')
        || ($db->lastQuery?->binds[':matchId'] ?? null) !== 42) {
        throw new \RuntimeException('Referee slugs must be portable and scoped to the requested match.');
    }

    $player = new \Diddipoeler\Component\SportsManagement\Administrator\Service\PlayerPersistenceService($db);
    $ids = new \ReflectionMethod($player, 'normaliseIds');
    if ($ids->invoke($player, [1, '002', 2, '3junk', '1e3', 1.5, true, [], '999999999999999999999999', '-1', '0']) !== [1, 2]
        || $ids->invoke($player, ['00011', 12, '11', 12]) !== [11, 12]) {
        throw new \RuntimeException('Player season assignment IDs must be strictly validated.');
    }

    $dates = new \ReflectionMethod($player, 'normaliseDate');
    foreach ([
        '2024-02-29' => '2024-02-29',
        '29.02.2024' => '2024-02-29',
        '29-02-2024' => '2024-02-29',
        '2026-10-13' => '2026-10-13',
    ] as $input => $expected) {
        if ($dates->invoke($player, $input) !== $expected) {
            throw new \RuntimeException('Valid player date changed: ' . $input);
        }
    }

    foreach (['2023-02-29', '31.04.2026', '29.02.2023', '2026-01-01junk', 'next monday', '0000-00-00'] as $invalid) {
        if ($dates->invoke($player, $invalid) !== null) {
            throw new \RuntimeException('Impossible or ambiguous player date accepted: ' . $invalid);
        }
    }

    final class CalendarHttpStub
    {
        public int $status = 200;
        public string $body = '{"items":[]}';
        public string $lastUrl = '';
        public int $timeout = 0;

        public function get(string $url, array $headers, int $timeout): object
        {
            $this->lastUrl = $url;
            $this->timeout = $timeout;

            return new class($this->status, $this->body) {
                public function __construct(private int $status, private string $body) {}
                public function getStatusCode(): int { return $this->status; }
                public function getBody(): string { return $this->body; }
            };
        }
    }

    $http = new CalendarHttpStub();
    \Joomla\Http\HttpFactory::$http = $http;
    $calendar = new \Diddipoeler\Module\SportsManagementGoogleCalendar\Site\Helper\GoogleCalendarHelper();

    if ($calendar->loadNextEvents('secret-key', 'test@example.test', 4) !== []
        || \Joomla\Http\HttpFactory::$options['follow_location'] !== false
        || $http->timeout !== 10
        || !str_contains($http->lastUrl, 'test%40example.test/events?key=secret-key')
        || !str_contains($http->lastUrl, 'maxResults=4')) {
        throw new \RuntimeException('Google Calendar API requests must reject redirects and preserve valid options.');
    }

    foreach ([301, 302, 307] as $redirect) {
        $http->status = $redirect;

        try {
            $calendar->loadNextEvents('secret-key', 'test@example.test', 4);
        } catch (\RuntimeException $error) {
            if ($error->getCode() === 0) {
                continue;
            }
            throw $error;
        }

        throw new \RuntimeException('Google Calendar accepted an HTTP redirect: ' . $redirect);
    }

    $http->status = 200;
    $http->body = '{"error":{"message":"invalid key"}}';

    try {
        $calendar->loadNextEvents('secret-key', 'test@example.test', 4);
        throw new \RuntimeException('Google Calendar accepted an API error payload.');
    } catch (\UnexpectedValueException) {
        // Expected: a proxy may rewrite Google API errors to HTTP 200.
    }

    echo "Joomla 5/6 referee, player and Google Calendar runtime regressions OK.\n";
}
