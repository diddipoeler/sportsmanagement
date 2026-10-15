<?php
/**
 * Joomla 5/6 Google Calendar pagination and feed-range regressions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}

namespace Joomla\Database {
    interface DatabaseInterface {}
    class ParameterType {
        public const INTEGER = 'integer';
    }
}

namespace Joomla\Registry {
    class Registry {
        private array $values = [];
        public function loadString(string $value): void {
            $this->values = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }
        public function get(string $key, mixed $default = null): mixed {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace Joomla\CMS\Log {
    class Log {
        public const WARNING = 300;
        public static function add(string $message, int $level, string $category): void {}
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Service/GoogleCalendarReadService.php';

    class Google_Client {
        public function __construct(array $options = []) {}
        public function setApplicationName(string $name): void {}
        public function setClientId(string $clientId): void {}
        public function setClientSecret(string $secret): void {}
        public function setScopes(array $scopes): void {}
        public function setAccessType(string $accessType): void {}
        public function fetchAccessTokenWithRefreshToken(string $token): array { return []; }
    }

    class Google_Service_Calendar {
        public object $events;
        public function __construct(Google_Client $client) {
            $this->events = \FakeGoogleEvents::$endpoint;
        }
    }

    class FakeGoogleCollection {
        public function __construct(private array $items, private mixed $token) {}
        public function getItems(): array { return $this->items; }
        public function getNextPageToken(): mixed { return $this->token; }
    }

    class FakeGoogleEvents {
        public static object $endpoint;
        public int $calls = 0;
        public array $options = [];

        public function __construct(public string $scenario) {}

        public function listEvents(string $calendar, array $options): FakeGoogleCollection {
            ++$this->calls;
            $this->options[] = $options;
            if ($calendar !== 'calendar@example.org') {
                throw new \RuntimeException('Wrong calendar address.');
            }

            if ($this->scenario === 'repeat') {
                // Repeating an opaque page cursor must not trigger an endless request.
                return new FakeGoogleCollection([], 'loop-token');
            }

            if ($this->scenario === 'empty') {
                // Different cursors, no valid events: the count(1000) limit never fires.
                return new FakeGoogleCollection(
                    [(object) ['id' => 'malformed-event']], 'cursor-' . $this->calls
                );
            }

            $items = [];
            for ($i = 0; $i < 1001; ++$i) {
                $items[] = (object) [
                    'id' => (string) ($i + 1),
                    'start' => (object) ['dateTime' => '2026-10-16T10:00:00Z'],
                    'end' => (object) ['dateTime' => '2026-10-16T11:00:00Z'],
                    'summary' => 'Fixture',
                ];
            }

            return new FakeGoogleCollection($items, 'next');
        }
    }

    class FakeCalendarDatabase implements \Joomla\Database\DatabaseInterface {
        public int $calls = 0;
        public function createQuery(): object {
            ++$this->calls;
            throw new \RuntimeException('Invalid time window must not reach the database.');
        }
    }

    class FakeCalendarApplication implements \Joomla\CMS\Application\CMSApplicationInterface {
        public function get(string $key): string { return '/tmp'; }
    }

    $db = new FakeCalendarDatabase();
    $reader = new \Diddipoeler\Component\SportsManagement\Site\Service\GoogleCalendarReadService(
        $db,
        new FakeCalendarApplication()
    );
    if ($reader->getEvents([12], 200, 100) !== []
        || $reader->getEvents([12], 100, 100) !== []
        || $db->calls !== 0) {
        throw new \RuntimeException('Invalid calendar windows caused a broad remote or database request.');
    }

    $calendar = (object) [
        'id' => 12,
        'calendar_id' => 'calendar@example.org',
        'color' => '#123456',
        'params' => json_encode([
            'client-id' => 'client',
            'client-secret' => 'secret',
            'refreshToken' => 'token',
        ], JSON_THROW_ON_ERROR),
    ];
    $fetch = new \ReflectionMethod($reader, 'fetchCalendarEvents');

    $loop = new FakeGoogleEvents('repeat');
    FakeGoogleEvents::$endpoint = $loop;
    $result = $fetch->invoke($reader, $calendar, 1000, 2000);
    if ($result !== [] || $loop->calls !== 2
        || ($loop->options[0]['timeMin'] ?? '') !== '1970-01-01T00:16:40+00:00'
        || ($loop->options[0]['timeMax'] ?? '') !== '1970-01-01T00:33:20+00:00'
        || ($loop->options[1]['pageToken'] ?? '') !== 'loop-token') {
        throw new \RuntimeException('Repeating Google Calendar page token was not stopped.');
    }

    $empty = new FakeGoogleEvents('empty');
    FakeGoogleEvents::$endpoint = $empty;
    if ($fetch->invoke($reader, $calendar, null, null) !== [] || $empty->calls !== 10) {
        throw new \RuntimeException('Google API pagination with invalid events exceeded the page limit.');
    }

    $valid = new FakeGoogleEvents('valid');
    FakeGoogleEvents::$endpoint = $valid;
    $events = $fetch->invoke($reader, $calendar, null, null);
    if (count($events) !== 1000 || $valid->calls !== 1
        || $events[0]['id'] !== '1'
        || $events[999]['id'] !== '1000') {
        throw new \RuntimeException('The historical 1000-event limit was not preserved.');
    }

    echo "Joomla 5/6 Google Calendar pagination and feed windows OK\n";
}
