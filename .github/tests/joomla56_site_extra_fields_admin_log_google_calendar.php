<?php
/**
 * Regression for frontend extra-field identifiers, backend error logging and Google Calendar cache.
 *
 * @version 5.6.0
 * @author SportsManagement contributors
 * @copyright Copyright (C) 2026 SportsManagement contributors
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Database {
    /** Stub Joomla database contract. */
    interface DatabaseInterface {}
    /** Typed query binding markers. */
    final class ParameterType {
        public const STRING = 'string';
        public const INTEGER = 'integer';
    }
}
namespace Joomla\CMS\Application {
    /** Joomla application type marker. */
    interface CMSApplicationInterface {}
}
namespace Joomla\CMS\Cache {
    /** Joomla cache controller type marker. */
    interface CacheControllerFactoryInterface {}
}
namespace Joomla\Registry {
    /** Standalone Registry preserving raw settings. */
    final class Registry {
        /**
         * Initialise module settings.
         * @param array $values Stored parameters.
         */
        public function __construct(private array $values = []) {}
        /**
         * Return the raw parameter or its default.
         * @param string $key Parameter name.
         * @param mixed $default Default value.
         * @return mixed Value from test configuration.
         */
        public function get(string $key, mixed $default = null): mixed {
            return array_key_exists($key, $this->values) ? $this->values[$key] : $default;
        }
    }
}
namespace Joomla\CMS\Log {
    /** Joomla error log fixture. */
    final class Log {
        public const ERROR = 1;
        public static array $messages = [];
        /**
         * Record a private error message.
         * @param string $message Log entry.
         * @param int $severity Log severity.
         * @param string $category Log group.
         * @return void
         */
        public static function add(string $message, int $severity, string $category): void {
            self::$messages[] = $message;
        }
    }
}
namespace Joomla\CMS\Language {
    /** Translation fixture. */
    final class Text {
        /**
         * Return an untranslated translation key.
         * @param string $key Language key.
         * @return string Same key.
         */
        public static function _(string $key): string { return $key; }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper {
    /** Isolated administrator application resolver. */
    final class SportsManagementAdministratorApplicationResolver {
        /**
         * Resolve the test backend application.
         * @return \Joomla\CMS\Application\CMSApplicationInterface Admin app.
         */
        public static function resolve(): \Joomla\CMS\Application\CMSApplicationInterface {
            return \ExtraFieldCalendarFixture::$app;
        }
    }
}
namespace {
    define('_JEXEC', 1);
    /** Store application fixtures across namespace boundaries. */
    final class ExtraFieldCalendarFixture {
        public static \Joomla\CMS\Application\CMSApplicationInterface $app;
    }
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Helper/ExtraFieldsReadHelper.php';
    require_once $root . '/admin/src/Helper/ExtraFieldsReadHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_google_calendar/src/Helper/GoogleCalendarHelper.php';

    $db = new class implements \Joomla\Database\DatabaseInterface {
        public int $queries = 0;
        /**
         * Create a fluent query fixture.
         * @return self Query builder.
         */
        public function createQuery(): self { return $this; }
        /**
         * Select matching columns.
         * @param mixed $data Selected columns.
         * @return self Query builder.
         */
        public function select(mixed $data): self { return $this; }
        /**
         * Set the queried extra-field definition table without opening a database connection.
         * @param string $data SQL table identifier.
         * @return self Query builder.
         */
        public function from(string $data): self { return $this; }
        /**
         * Add an exact-match predicate.
         * @param string $data SQL condition.
         * @return self Query builder.
         */
        public function where(string $data): self { return $this; }
        /**
         * Bind a query parameter.
         * @param string $name Query parameter name.
         * @param mixed $data Parameter value.
         * @param mixed $type Parameter type.
         * @return self Query builder.
         */
        public function bind(string $name, mixed $data, mixed $type): self { return $this; }
        /**
         * Quote a database identifier.
         * @param string $name Identifier.
         * @return string Quoted SQL identifier.
         */
        public function quoteName(string $name): string { return '[' . $name . ']'; }
        /**
         * Count test queries without executing SQL.
         * @param mixed $query Query object.
         * @return void
         */
        public function setQuery(mixed $query): void { ++$this->queries; }
        /**
         * Return a configured extra-field count.
         * @return int Matching field count.
         */
        public function loadResult(): int { return 1; }
    };

    $site = \Diddipoeler\Component\SportsManagement\Site\Helper\ExtraFieldsReadHelper::class;
    foreach ([' view', 'view ', "view\n", 'view/unsafe', str_repeat('a', 129)] as $bad) {
        if ($site::hasFields($db, $bad)) {
            throw new \RuntimeException('Invalid extra-field template was accepted.');
        }
    }
    if ($db->queries !== 0 || !$site::hasFields($db, 'view_name') || $db->queries !== 1) {
        throw new \RuntimeException('Valid extra-field template was rejected.');
    }

    ExtraFieldCalendarFixture::$app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        /**
         * Collect a generic administrator error.
         * @param string $message Joomla warning.
         * @param string $type Message class.
         * @return void
         */
        public function enqueueMessage(string $message, string $type): void {}
    };
    $admin = new \Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraFieldsReadHelper();
    (new \ReflectionMethod($admin, 'reportDatabaseError'))->invoke(
        $admin, new \RuntimeException('password=secret SELECT * FROM confidential')
    );
    $messages = \Joomla\CMS\Log\Log::$messages;
    if (count($messages) !== 1 || !str_contains($messages[0], 'RuntimeException')
        || str_contains($messages[0], 'secret') || str_contains($messages[0], 'SELECT')) {
        throw new \RuntimeException('Extra-field database error logged confidential information.');
    }

    $calendar = new \Diddipoeler\Module\SportsManagementGoogleCalendar\Site\Helper\GoogleCalendarHelper();
    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {};
    $cache = new class implements \Joomla\CMS\Cache\CacheControllerFactoryInterface {};
    foreach ([
        ['api_key' => str_repeat('a', 257), 'calendar_id' => 'valid@example.test'],
        ['api_key' => 'valid', 'calendar_id' => str_repeat('c', 1025)],
        ['api_key' => ['bad'], 'calendar_id' => 'valid@example.test'],
    ] as $settings) {
        if ($calendar->getData(new \Joomla\Registry\Registry($settings), (object) [], $app, $cache)
            !== ['events' => []]) {
            throw new \RuntimeException('Invalid calendar settings reached the cache.');
        }
    }

    $event = (object) [
        'htmlLink' => 'https://example.test/event',
        'summary' => str_repeat('x', 8193),
        'location' => 'Venue',
        'jsmStartIso' => ['bad'],
        'jsmEndIso' => '2026-11-13T12:00:00Z',
        'jsmDuration' => '1 hour',
    ];
    $normalise = new \ReflectionMethod($calendar, 'normaliseRenderableEvents');
    $safe = $normalise->invoke(null, [$event], 5);
    if (count($safe) !== 1 || $safe[0]->summary !== '' || $safe[0]->jsmStartIso !== ''
        || $safe[0]->location !== 'Venue' || $safe[0]->htmlLink !== 'https://example.test/event') {
        throw new \RuntimeException('Malformed cached calendar event data was not bounded.');
    }

    echo "Joomla 5/6 frontend extra fields, admin log privacy and calendar cache bounds: OK\n";
}
