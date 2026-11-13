<?php
/**
 * Standalone regression for strict admin external database access, names and calendar layout.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Database {
    /** Minimal Joomla database interface for isolated connection tests. */
    interface DatabaseInterface {}

    /** Joomla parameter binding constants for the external profile query. */
    final class ParameterType {
        public const INTEGER = 'integer';
        public const STRING = 'string';
    }

    /** Configurable external driver factory without network access. */
    final class DatabaseFactory {
        public static int $calls = 0;
        public static ?DatabaseInterface $connection = null;
        public static bool $fail = false;

        /**
         * Return an isolated driver or simulate a failed connection.
         * @param string $name Joomla database driver name.
         * @param array $options Connection configuration.
         * @return DatabaseInterface Simulated external database.
         */
        public function getDriver(string $name, array $options): DatabaseInterface {
            ++self::$calls;
            if (self::$fail) {
                throw new \RuntimeException('password=private SQL credentials');
            }
            return self::$connection;
        }
    }
}
namespace Joomla\CMS\Component {
    /** Configured component parameters used by the admin resolver. */
    final class ComponentHelper {
        public static array $values = [];

        /**
         * Return the current SportsManagement settings.
         * @param string $option Component identifier.
         * @return object Parameter registry fixture.
         */
        public static function getParams(string $option): object {
            return new class {
                /**
                 * Read a configured component setting.
                 * @param string $name Component parameter key.
                 * @param mixed $default Default setting.
                 * @return mixed Stored parameter.
                 */
                public function get(string $name, mixed $default = null): mixed {
                    return \Joomla\CMS\Component\ComponentHelper::$values[$name] ?? $default;
                }
            };
        }
    }
}
namespace Joomla\CMS\Log {
    /** Collect Joomla security diagnostics without sensitive logging. */
    final class Log {
        public const ERROR = 1;
        public const INFO = 2;
        public static array $messages = [];

        /**
         * Record a Joomla log message for security assertions.
         * @param string $message Log entry.
         * @param int $priority Joomla severity.
         * @param string $category Component logging group.
         * @return void
         */
        public static function add(string $message, int $priority, string $category): void {
            self::$messages[] = $message;
        }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Helper/SportsManagementDatabaseResolver.php';
    require_once $root . '/site/src/Helper/PersonNameFormatter.php';

    $fallback = new class implements \Joomla\Database\DatabaseInterface {};
    $external = new class implements \Joomla\Database\DatabaseInterface {
        public static array $profiles = [];

        /**
         * Return a fluent profile query builder.
         * @return self Query builder fixture.
         */
        public function createQuery(): self { return $this; }
        /**
         * Quote a table or field, without connecting to a server.
         * @param string $name Field identifier.
         * @param string|null $alias Optional table alias.
         * @return string Quoted identifier.
         */
        public function quoteName(string $name, ?string $alias = null): string { return $name; }
        /**
         * Select the external entitlement profile columns.
         * @param mixed $columns SQL selected columns.
         * @return self Query builder.
         */
        public function select(mixed $columns): self { return $this; }
        /**
         * Specify the server profile table.
         * @param string $table Quoted profile table.
         * @return self Query builder.
         */
        public function from(string $table): self { return $this; }
        /**
         * Add a bounded query condition.
         * @param string $condition SQL condition.
         * @return self Query builder.
         */
        public function where(string $condition): self { return $this; }
        /**
         * Bind a typed external profile value.
         * @param string $name Placeholder name.
         * @param mixed $value Bound value.
         * @param mixed $type SQL parameter type.
         * @return self Query builder.
         */
        public function bind(string $name, mixed $value, mixed $type): self { return $this; }
        /**
         * Prepare the profile query.
         * @param mixed $query Query instance.
         * @return void
         */
        public function setQuery(mixed $query): void {}
        /**
         * Return the saved server access profile.
         * @param string $index Profile index key.
         * @return array Profile rows.
         */
        public function loadAssocList(string $index): array { return self::$profiles; }
    };

    \Joomla\Database\DatabaseFactory::$connection = $external;
    \Joomla\CMS\Component\ComponentHelper::$values = [
        'cfg_which_database' => 0,
        'jsm_server_user' => 1,
        'jsm_user_serialnumber' => 'A-SECRET-1',
        'jsm_dbtype' => 'mysqli',
    ];
    $resolver = new \Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver();

    foreach (['1evil', 1.7, true, [], ' 1', -1] as $bad) {
        if ($resolver->resolve($bad, $fallback) !== $fallback) {
            throw new \RuntimeException('Noncanonical external selector activated a connection.');
        }
    }
    if (\Joomla\Database\DatabaseFactory::$calls !== 0) {
        throw new \RuntimeException('Malformed selector triggered external database factory.');
    }

    $from = gmdate('Y-m-d H:i:s', time() - 86400);
    $to = gmdate('Y-m-d H:i:s', time() + 86400);
    $profile = static function (mixed $enabled, string $serial, string $start, string $end): array {
        return [
            'jsmprofile.databaseaccess' => ['profile_value' => $enabled],
            'jsmprofile.serialnumber' => ['profile_value' => $serial],
            'jsmprofile.access_from' => ['profile_value' => $start],
            'jsmprofile.access_to' => ['profile_value' => $end],
        ];
    };

    foreach ([
        $profile('false', 'A-SECRET-1', $from, $to),
        $profile('not false', 'A-SECRET-1', $from, $to),
        $profile('true', '', $from, $to),
        $profile('true', '0', $from, $to),
        $profile('true', 'A-SECRET-1', '0000-00-00', $to),
        $profile('true', 'A-SECRET-1', 'tomorrow', $to),
        $profile('true', 'A-SECRET-1', $to, $from),
    ] as $bad) {
        $external::$profiles = $bad;
        if ($resolver->resolve(1, $fallback) !== $fallback) {
            throw new \RuntimeException('Invalid administrator database profile granted external access.');
        }
    }

    $external::$profiles = $profile('true', 'A-SECRET-1', $from, $to);
    if ($resolver->resolve('1', $fallback) !== $external) {
        throw new \RuntimeException('Valid external database entitlement was rejected.');
    }

    \Joomla\Database\DatabaseFactory::$fail = true;
    \Joomla\CMS\Log\Log::$messages = [];
    if ($resolver->resolve(1, $fallback) !== $fallback
        || count(\Joomla\CMS\Log\Log::$messages) !== 1
        || str_contains(\Joomla\CMS\Log\Log::$messages[0], 'private')
        || !str_contains(\Joomla\CMS\Log\Log::$messages[0], 'RuntimeException')) {
        throw new \RuntimeException('External connection errors exposed sensitive driver messages.');
    }

    $formatter = \Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter::class;
    if ($formatter::format(null, 'Ada', '', 'Lovelace', str_repeat('9', 10000)) !== 'Ada Lovelace'
        || $formatter::format(null, 'Ada', '', 'Lovelace', 3) !== 'Ada Lovelace') {
        throw new \RuntimeException('Invalid person name format selector altered output.');
    }

    $params = new class {
        /**
         * Supply the enabled link setting for Google Calendar rendering.
         * @param string $key Module parameter key.
         * @param mixed $default Default value.
         * @return mixed Configured value.
         */
        public function get(string $key, mixed $default = null): mixed { return $default; }
    };

    $events = [
        ['not an event'],
        (object) ['htmlLink' => ['nested'], 'summary' => ['invalid'], 'jsmDuration' => '1 hour'],
        (object) ['htmlLink' => 'javascript:alert(1)', 'summary' => 'Safe event', 'jsmDuration' => '2 hours'],
    ];
    ob_start();
    require $root . '/modules/mod_sportsmanagement_google_calendar/tmpl/default.php';
    $markup = (string) ob_get_clean();
    if (substr_count($markup, '<li class="event"') !== 2
        || str_contains($markup, 'Array') || str_contains($markup, '<a href="javascript:')
        || !str_contains($markup, 'Safe event')) {
        throw new \RuntimeException('Google Calendar layout emitted malformed cached event data.');
    }

    echo "Joomla 5/6 admin database entitlements, site person names and calendar layout: OK\n";
}
