<?php
/**
 * Joomla 5/6 Google Calendar cached-event validation regression.
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

namespace Joomla\CMS\Cache {
    interface CacheControllerFactoryInterface {}
}

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Store test module configuration.
         *
         * @param array<string, mixed> $values Fake Joomla module values.
         */
        public function __construct(private array $values = [])
        {
        }

        /**
         * Read an individual module setting.
         *
         * @param string $key Configuration key.
         * @param mixed $default Fallback for missing keys.
         * @return mixed Configured value or fallback.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_google_calendar/src/Helper/GoogleCalendarHelper.php';

    final class CallbackCacheStub
    {
        /**
         * Return the simulated cached payload instead of making an HTTP call.
         *
         * @param mixed $result Value stored in the test callback cache.
         */
        public function __construct(private mixed $result)
        {
        }

        /**
         * Simulate Joomla's callback cache entry point without outbound IO.
         *
         * @param callable $callback Google Calendar loader callback.
         * @param mixed ...$args Callback arguments that are deliberately unused.
         * @return mixed Cached event data.
         */
        public function call(callable $callback, mixed ...$args): mixed
        {
            return $this->result;
        }
    }

    final class CallbackFactoryStub implements \Joomla\CMS\Cache\CacheControllerFactoryInterface
    {
        public int $calls = 0;
        public array $options = [];

        /**
         * Hold the fake callback cache response.
         *
         * @param mixed $payload Simulated cached events.
         */
        public function __construct(public mixed $payload)
        {
        }

        /**
         * Return a cache stub and record the selected cache lifetime.
         *
         * @param string $type Joomla cache controller type.
         * @param array<string, mixed> $options Cache configuration.
         * @return CallbackCacheStub Fake callback cache.
         */
        public function createCacheController(string $type, array $options): CallbackCacheStub
        {
            ++$this->calls;
            $this->options = $options;

            return new CallbackCacheStub($this->payload);
        }
    }

    $helper = new \Diddipoeler\Module\SportsManagementGoogleCalendar\Site\Helper\GoogleCalendarHelper();
    $application = new class implements \Joomla\CMS\Application\CMSApplicationInterface {};
    $normalise = new \ReflectionMethod($helper, 'normaliseRenderableEvents');

    $records = [
        (object) ['summary' => 'Valid', 'htmlLink' => 'https://calendar.example.test/event',
            'location' => 'Stadium', 'jsmStartIso' => '2026-11-02T12:00:00Z'],
        null,
        ['summary' => 'not an event'],
        (object) ['summary' => ['injected'], 'htmlLink' => ['javascript:evil()'],
            'location' => new \stdClass(), 'jsmDuration' => true],
        (object) ['summary' => 'third valid'],
    ];

    $clean = $normalise->invoke(null, $records, 2);
    if (count($clean) !== 2 || $clean[0]->summary !== 'Valid'
        || $clean[1]->summary !== '' || $clean[1]->htmlLink !== ''
        || $clean[1]->location !== '' || $clean[1]->jsmDuration !== '') {
        throw new \RuntimeException('Malformed cached Google Calendar data was rendered.');
    }
    if ($records[3]->summary !== ['injected']) {
        throw new \RuntimeException('Cached Google Calendar input was mutated.');
    }
    if ($normalise->invoke(null, 'not an array', 5) !== []) {
        throw new \RuntimeException('Scalar Google Calendar cache payload was accepted.');
    }

    $factory = new CallbackFactoryStub($records);
    $params = new \Joomla\Registry\Registry([
        'api_key' => 'test-key',
        'calendar_id' => 'test-calendar@example.test',
        'max_list_events' => '0002',
        'api_cache_time' => '0003',
    ]);
    $data = $helper->getData($params, (object) [], $application, $factory);
    if (($data['events'] ?? null) != $clean || $factory->calls !== 1
        || ($factory->options['lifetime'] ?? null) !== 3) {
        throw new \RuntimeException('Google Calendar module did not bound cached events.');
    }

    $invalid = $helper->getData(new \Joomla\Registry\Registry([
        'api_key' => ['invalid'],
        'calendar_id' => 'test',
    ]), (object) [], $application, $factory);
    if ($invalid !== ['events' => []] || $factory->calls !== 1) {
        throw new \RuntimeException('Invalid Google Calendar credential used the cache.');
    }

    echo "Google Calendar cached event validation passed.\n";
}
