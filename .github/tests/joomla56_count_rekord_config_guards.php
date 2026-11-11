<?php
/**
 * Joomla 5/6 record-counter module configuration and query failure regressions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Initialise the fake module Registry with the supplied defaults.
         * @param array<string,mixed> $data Raw module options.
         * @return void
         */
        public function __construct(private array $data = []) {}
        /**
         * Read a simulated module setting without loading Joomla.
         * @param string $key Requested option name.
         * @param mixed $default Fallback when the key is missing.
         * @return mixed Stored value or supplied fallback.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->data[$key] ?? $default;
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Format the translation key and supplied values for assertions.
         * @param string $key Language key for the record counter.
         * @param mixed ...$args Substituted translated values.
         * @return string Readable simulated translated text.
         */
        public static function sprintf(string $key, mixed ...$args): string
        {
            return $key . ': ' . implode(', ', $args);
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $errors = [];

        /**
         * Record a Joomla log event without writing to disk.
         * @param string $message Logged diagnostic text.
         * @param int $level Severity of the event.
         * @param string $category Joomla component logging category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$errors[] = [$message, $level, $category];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        public static int $selector = -1;
        public static bool $fail = false;

        /**
         * Select the fake component database and record the selector.
         * @param \\Joomla\\Database\\DatabaseInterface $fallback Primary database.
         * @param int $selector Module database selector.
         * @return \\Joomla\\Database\\DatabaseInterface Same fixture database.
         * @throws \\RuntimeException When the test requests a failure.
         */
        public static function resolve(
            \Joomla\Database\DatabaseInterface $fallback,
            int $selector
        ): \Joomla\Database\DatabaseInterface {
            self::$selector = $selector;

            if (self::$fail) {
                throw new \RuntimeException('private SQLSTATE password=secret');
            }

            return $fallback;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_count_rekord/src/Helper/CountRekordHelper.php';

    final class RecordDatabase implements \Joomla\Database\DatabaseInterface
    {
        public int $queries = 0;
        public mixed $result = 40;
        public bool $fail = false;

        /**
         * Return a fluent fake SQL query and count its execution.
         * @return object Query builder fixture.
         * @throws \\RuntimeException When failure simulation is enabled.
         */
        public function createQuery(): object
        {
            ++$this->queries;

            if ($this->fail) {
                throw new \RuntimeException('private match query password=secret');
            }

            return new class {
                /**
                 * Accept a SQL select expression in the lightweight query fixture.
                 * @param string $s Selected query expression.
                 * @return self Fluent query fixture.
                 */
                public function select(string $s): self { return $this; }
                /**
                 * Accept a source table name in the lightweight query fixture.
                 * @param string $s Table identifier.
                 * @return self Fluent query fixture.
                 */
                public function from(string $s): self { return $this; }
            };
        }

        /**
         * Return a table name for the simulated Joomla SQL query.
         * @param string $name Table identifier.
         * @return string Unmodified identifier.
         */
        public function quoteName(string $name): string { return $name; }
        /**
         * Accept the prepared record-counter query without database I/O.
         * @param object $query Created query fixture.
         * @return void
         */
        public function setQuery(object $query): void {}
        /**
         * Return the configured match count used by the regression.
         * @return mixed Simulated record count, including invalid values.
         */
        public function loadResult(): mixed { return $this->result; }
    }

    $helper = new \Diddipoeler\Module\SportsManagementCountRekord\Site\Helper\CountRekordHelper();
    $normalize = new \ReflectionMethod($helper, 'nonNegativeInt');

    foreach ([
        [0, 0], [12, 12], ['0007', 7], ['000', 0],
        [-1, 0], ['-1', 0], [true, 0], [false, 0],
        [['1'], 0], [[], 0], ['7bad', 0], ['2.5', 0],
        ['999999999999999999999999', 0],
    ] as [$value, $expected]) {
        if ($normalize->invoke(null, $value) !== $expected) {
            throw new \RuntimeException('Invalid CountRekord numeric setting: ' . var_export($value, true));
        }
    }

    $db = new RecordDatabase();
    $module = (object) ['module' => '../evil'];

    foreach ([['jsm_stat_spielpaarungen' => ['1']], ['jsm_stat_spielpaarungen' => '1junk']] as $params) {
        if ($helper->getData(new \Joomla\Registry\Registry($params), $module, $db) !== []
            || $db->queries !== 0) {
            throw new \RuntimeException('Malformed record-counter activation queried the database.');
        }
    }

    $rows = $helper->getData(new \Joomla\Registry\Registry([
        'jsm_stat_spielpaarungen' => '1',
        'jsm_stat_paarungen' => '00100',
        'cfg_which_database' => '0007',
    ]), $module, $db);

    if (count($rows) !== 1
        || $db->queries !== 1
        || $rows[0]->anzahl !== 40
        || $rows[0]->anzahlbis !== 100
        || $rows[0]->anzahldiff !== 60
        || $rows[0]->image !== 'modules/mod_sportsmanagement_count_rekord/images/matches.png'
        || \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::$selector !== 7) {
        throw new \RuntimeException('Valid module configuration or fixed icon path regressed.');
    }

    // Legacy database adapters may return malformed count values. Avoid
    // array-to-integer conversion warnings and keep the target difference
    // predictable instead of exposing query details to frontend visitors.
    $db->result = ['invalid-count'];
    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $badCount = $helper->getData(new \Joomla\Registry\Registry([
            'jsm_stat_spielpaarungen' => 1,
            'jsm_stat_paarungen' => 5,
        ]), $module, $db);

        if (count($badCount) !== 1
            || $badCount[0]->anzahl !== 0
            || $badCount[0]->anzahlbis !== 5
            || $badCount[0]->anzahldiff !== 5) {
            throw new \RuntimeException('Malformed record count changed frontend target display.');
        }
    } finally {
        restore_error_handler();
    }

    $db->result = 40;

    $db->fail = true;

    if ($helper->getData(new \Joomla\Registry\Registry([
        'jsm_stat_spielpaarungen' => 1,
        'cfg_which_database' => 'bad',
    ]), $module, $db) !== []
        || \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::$selector !== 0
        || count(\Joomla\CMS\Log\Log::$errors) !== 1
        || !str_contains(\Joomla\CMS\Log\Log::$errors[0][0], 'password=secret')) {
        throw new \RuntimeException('Record-counter query failure was not handled privately.');
    }

    echo "Joomla 5/6 record counter configuration and query guards passed.\n";
}
