<?php
/**
 * PHP 8.3/8.4 regression checks for the old Google Calendar module facade.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Store a legacy module setting array for standalone tests.
         * @param array<string,mixed> $values Module parameters.
         */
        public function __construct(private array $values) {}

        /**
         * Retrieve a legacy module parameter or its default.
         * @param string $key Requested key.
         * @param mixed $default Value when missing.
         * @return mixed Configured value.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $messages = [];

        /**
         * Collect generic runtime error messages without writing to disk.
         * @param string $message Error summary.
         * @param int $type Joomla log severity.
         * @param string $category Joomla log category.
         * @return void
         */
        public static function add(string $message, int $type, string $category): void
        {
            self::$messages[] = [$message, $type, $category];
        }
    }
}

namespace Diddipoeler\Module\SportsManagementGoogleCalendar\Site\Helper {
    final class GoogleCalendarHelper
    {
        public static array $calls = [];

        /**
         * Return a harmless fixture for a mocked Google Calendar API request.
         * @param string $apiKey Configured key.
         * @param string $calendarId Configured calendar.
         * @param int $maxEvents Validated event limit.
         * @return array Fixture events.
         */
        public function loadNextEvents(string $apiKey, string $calendarId, int $maxEvents): array
        {
            self::$calls[] = [$apiKey, $calendarId, $maxEvents];
            return [(object) ['summary' => 'Sport']];
        }

        /**
         * Simulate the native duration delegate for legacy template callers.
         * @param object $event Fixture event.
         * @return string Fake duration.
         */
        public static function duration(object $event): string { return '90 minutes'; }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_google_calendar/helper.php';

    $native = \Diddipoeler\Module\SportsManagementGoogleCalendar\Site\Helper\GoogleCalendarHelper::class;
    $helper = new \ModJSMGoogleCalendarHelper(['api_key' => 'api-key', 'calendar_id' => 'club@example.org']);

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        foreach ([
            [1, 1], [0, 1], [-4, 1], [2501, 2500],
            ['0002', 2], ['0000', 1], ['2500', 2500],
            [null, 5], [true, 5], [false, 5], [1.5, 5],
            [['1'], 5], [new \stdClass(), 5], ['5junk', 5],
            [' 5', 5], ['1e3', 5], ['-1', 5],
            [str_repeat('9', 150), 5],
            [str_repeat('9', 20), 5],
        ] as [$raw, $expected]) {
            $before = count($native::$calls);
            $events = $helper->nextEvents($raw);

            if ($events !== [(object) ['summary' => 'Sport']]) {
                // Object identity is irrelevant in a delegated fetch: inspect
                // just the event shape and the outgoing limit below instead.
                if (count($events) !== 1 || ($events[0]->summary ?? null) !== 'Sport') {
                    throw new \RuntimeException('Legacy event results changed.');
                }
            }

            if (count($native::$calls) !== $before + 1
                || $native::$calls[$before] !== ['api-key', 'club@example.org', $expected]) {
                throw new \RuntimeException('Invalid or overflowed legacy calendar count.');
            }
        }

        if (\ModJSMGoogleCalendarHelper::duration((object) []) !== '90 minutes') {
            throw new \RuntimeException('Legacy duration helper is not delegated.');
        }

        foreach ([
            ['api_key' => ['bad'], 'calendar_id' => 'club@example.org'],
            ['api_key' => str_repeat('k', 257), 'calendar_id' => 'club@example.org'],
            ['api_key' => 'valid', 'calendar_id' => str_repeat('x', 1025)],
            ['api_key' => "key\nbad", 'calendar_id' => 'club@example.org'],
            ['api_key' => 'valid', 'calendar_id' => ['bad']],
            ['api_key' => '', 'calendar_id' => 'club@example.org'],
        ] as $settings) {
            $invalid = new \ModJSMGoogleCalendarHelper($settings);
            $before = count($native::$calls);

            if ($invalid->nextEvents(5) !== [] || count($native::$calls) !== $before) {
                throw new \RuntimeException('Malformed credentials reached the Google API adapter.');
            }
        }

        if (\Joomla\CMS\Log\Log::$messages !== []) {
            throw new \RuntimeException('Valid or rejected settings generated an unexpected error.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 legacy Google Calendar count and credential regressions passed.\n";
}
