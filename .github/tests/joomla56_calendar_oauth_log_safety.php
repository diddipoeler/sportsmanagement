<?php
/**
 * Joomla 5/6 Google Calendar OAuth configuration and diagnostic safety tests.
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
        private array $settings = [];
        public function loadString(string $json): void {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($decoded)) {
                throw new \InvalidArgumentException('Configuration must be an object.');
            }
            $this->settings = $decoded;
        }
        public function get(string $key, mixed $default = null): mixed {
            return $this->settings[$key] ?? $default;
        }
    }
}

namespace Joomla\CMS\Log {
    class Log {
        public const WARNING = 300;
        public static array $messages = [];
        public static function add(string $message, int $level, string $category): void {
            self::$messages[] = $message;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Service/GoogleCalendarReadService.php';

    class Google_Client {
        public static int $constructions = 0;
        public static string $mode = 'ok';
        public static ?self $last = null;
        public string $clientId = '';
        public string $clientSecret = '';
        public string $token = '';

        public function __construct(array $options = []) {
            ++self::$constructions;
            self::$last = $this;
        }
        public function setApplicationName(string $name): void {}
        public function setClientId(string $id): void { $this->clientId = $id; }
        public function setClientSecret(string $secret): void { $this->clientSecret = $secret; }
        public function setScopes(array $scopes): void {}
        public function setAccessType(string $type): void {}
        public function fetchAccessTokenWithRefreshToken(string $token): array {
            $this->token = $token;
            if (self::$mode === 'throw') {
                throw new \RuntimeException('HTTP auth error: client-secret=' . $this->clientSecret . ' refresh=' . $token);
            }
            if (self::$mode === 'error') {
                return ['error' => 'refresh=' . $token];
            }
            return [];
        }
    }

    class Google_Service_Calendar {
        public object $events;
        public function __construct(Google_Client $client) {
            $this->events = new class {
                public function listEvents(string $address, array $params): never {
                    throw new \RuntimeException('API failed; token=refresh-private');
                }
                public function get(string $address, string $eventId): never {
                    throw new \RuntimeException('Event failed; client-secret=client-private');
                }
            };
        }
    }

    class CalendarQuery {
        public function select(mixed $columns): self { return $this; }
        public function from(string $table): self { return $this; }
        public function whereIn(string $name, array $values, mixed $type): self { return $this; }
        public function order(string $order): self { return $this; }
    }

    class CalendarDb implements \Joomla\Database\DatabaseInterface {
        public ?object $calendar = null;
        public function createQuery(): CalendarQuery { return new CalendarQuery(); }
        public function quoteName(string $name): string { return $name; }
        public function setQuery(CalendarQuery $query): void {}
        public function loadObjectList(): array {
            return $this->calendar !== null ? [$this->calendar] : [];
        }
    }

    class CalendarApp implements \Joomla\CMS\Application\CMSApplicationInterface {
        public function get(string $name): string { return '/tmp'; }
        public function getIdentity(): object {
            return new class {
                public function authorise(string $action, string $asset): bool { return true; }
            };
        }
    }

    $db = new CalendarDb();
    $reader = new \Diddipoeler\Component\SportsManagement\Site\Service\GoogleCalendarReadService(
        $db, new CalendarApp()
    );
    $create = new \ReflectionMethod($reader, 'createService');

    $config = [
        'client-id' => 'client-id',
        'client-secret' => 'client-private',
        'refreshToken' => 'refresh-private',
    ];
    $calendar = (object) [
        'id' => 17,
        'calendar_id' => 'my-calendar@example.org',
        'params' => json_encode($config, JSON_THROW_ON_ERROR),
        'color' => '#123456',
    ];

    $service = $create->invoke($reader, $calendar);
    if (!$service instanceof Google_Service_Calendar
        || Google_Client::$last?->clientId !== 'client-id'
        || Google_Client::$last?->clientSecret !== 'client-private'
        || Google_Client::$last?->token !== 'refresh-private') {
        throw new \RuntimeException('Valid calendar OAuth configuration was rejected.');
    }

    $startCount = Google_Client::$constructions;
    $badConfigs = [
        (object) ['id' => 17, 'calendar_id' => 'my-calendar@example.org', 'params' => ['bad']],
        (object) ['id' => 17, 'calendar_id' => ['bad'], 'params' => $calendar->params],
        (object) ['id' => 17, 'calendar_id' => 'my-calendar@example.org', 'params' =>
            json_encode(array_replace($config, ['client-id' => ['bad']]), JSON_THROW_ON_ERROR)],
        (object) ['id' => 17, 'calendar_id' => 'my-calendar@example.org', 'params' =>
            json_encode(array_replace($config, ['client-secret' => ['bad']]), JSON_THROW_ON_ERROR)],
        (object) ['id' => 17, 'calendar_id' => 'my-calendar@example.org', 'params' =>
            json_encode(array_replace($config, ['refreshToken' => (object) ['bad' => true]]), JSON_THROW_ON_ERROR)],
    ];

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });
    try {
        foreach ($badConfigs as $bad) {
            if ($create->invoke($reader, $bad) !== null) {
                throw new \RuntimeException('Malformed OAuth parameters were accepted.');
            }
        }
    } finally {
        restore_error_handler();
    }
    if (Google_Client::$constructions !== $startCount) {
        throw new \RuntimeException('Invalid OAuth parameters reached the Google client.');
    }

    $db->calendar = $calendar;
    foreach (['throw', 'error'] as $mode) {
        Google_Client::$mode = $mode;
        if ($create->invoke($reader, $calendar) !== null) {
            throw new \RuntimeException('Failed OAuth refresh created a calendar service.');
        }
    }

    Google_Client::$mode = 'ok';
    if ($reader->getEvent(17, 'private-event') !== null
        || $reader->getEvents([17], 100, 200) !== []) {
        throw new \RuntimeException('Google API failures did not fail closed.');
    }

    $logs = implode("\n", \Joomla\CMS\Log\Log::$messages);
    if (str_contains($logs, 'refresh-private')
        || str_contains($logs, 'client-private')
        || !str_contains($logs, 'RuntimeException')
        || !str_contains($logs, 'calendar 17')) {
        throw new \RuntimeException('Calendar diagnostics leaked secrets or lacked safe error context.');
    }

    echo "Joomla 5/6 Google Calendar OAuth parameter and log safety OK\n";
}
