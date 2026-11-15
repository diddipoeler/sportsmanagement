<?php
/**
 * PHP 8.3/8.4 Joomla 5/6 legacy time, live-match and UEFA data regression.
 *
 * Calls actual first-party implementations with Joomla service stubs and
 * converts all PHP warnings to test failures.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}

namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType
    {
        public const INTEGER = 1;
        public const STRING = 2;
    }
}

namespace Joomla\CMS\HTML {
    final class HTMLHelper
    {
        /**
         * Stub the Joomla date formatter for a stable fixture.
         * @param string $date Match date.
         * @param string $format Configured date format.
         * @param string $timezone Date timezone.
         * @return string Fixed fixture time.
         */
        public static function date(string $date, string $format, string $timezone): string
        {
            return '12:00';
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return a stable translation identifier in isolation.
         * @param string $key Joomla language key.
         * @return string Same language key.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 2;
        public static array $messages = [];

        /**
         * Record a diagnostic without a real Joomla file system.
         * @param string $message Logged summary.
         * @param int $level Logging severity.
         * @param string $category Joomla category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$messages[] = $message;
        }
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Store Joomla-like module parameters for a test.
         * @param array<string,mixed> $data Input parameter map.
         */
        public function __construct(private array $data) {}

        /**
         * Retrieve a module setting without converting its type.
         * @param string $name Parameter name.
         * @param mixed $default Fallback value.
         * @return mixed Raw value.
         */
        public function get(string $name, mixed $default = null): mixed
        {
            return $this->data[$name] ?? $default;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        /**
         * Use the supplied fixture database for UEFA ranking tests.
         * @param \Joomla\Database\DatabaseInterface $db Injected test driver.
         * @param int $selector Current database selection.
         * @return \Joomla\Database\DatabaseInterface Same test driver.
         */
        public static function resolve(
            \Joomla\Database\DatabaseInterface $db,
            int $selector
        ): \Joomla\Database\DatabaseInterface {
            if ($selector !== 0) {
                throw new \RuntimeException('Unexpected UEFA database selection.');
            }

            return $db;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/helpers/sportsmanagement.php';
    require_once $root . '/site/src/Helper/MatchTimeHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_uefawertung/src/Helper/UefaWertungHelper.php';

    final class UefaQueryStub
    {
        public array $bound = [];

        /**
         * Accept query selection arguments.
         * @param mixed $selection SQL columns.
         * @return self Fluent test query.
         */
        public function select(mixed $selection): self { return $this; }

        /**
         * Accept a Joomla table selector.
         * @param string $table SQL table name.
         * @return self Fluent test query.
         */
        public function from(string $table): self { return $this; }

        /**
         * Accept a SQL bound filter.
         * @param string $filter SQL expression.
         * @return self Fluent test query.
         */
        public function where(string $filter): self { return $this; }

        /**
         * Accept a bound season ID or name.
         * @param string $name Placeholder.
         * @param mixed $value Bound value.
         * @param mixed $type Joomla binding type.
         * @return self Fluent test query.
         */
        public function bind(string $name, mixed $value, mixed $type): self
        {
            $this->bound[$name] = $value;
            return $this;
        }

        /**
         * Accept SQL group selection.
         * @param string $column Group name.
         * @return self Fluent test query.
         */
        public function group(string $column): self { return $this; }

        /**
         * Accept SQL sort criteria.
         * @param mixed $sort Order expression.
         * @return self Fluent test query.
         */
        public function order(mixed $sort): self { return $this; }

        /**
         * Accept selected seasons for coefficient ranking lookup.
         * @param string $column Column name.
         * @param array $seasons Valid season names.
         * @param mixed $type Joomla binding type.
         * @return self Fluent test query.
         */
        public function whereIn(string $column, array $seasons, mixed $type): self
        {
            $this->bound['seasonSet'] = $seasons;
            return $this;
        }
    }

    final class UefaDatabaseStub implements \Joomla\Database\DatabaseInterface
    {
        public mixed $season = '2025';
        public mixed $column = ['2024', '2025'];
        public mixed $rows = [];
        public int $queries = 0;
        public array $selections = [];
        public ?UefaQueryStub $current = null;

        /**
         * Make an isolated Joomla-style bound query.
         * @return UefaQueryStub Fresh query object.
         */
        public function createQuery(): UefaQueryStub { return new UefaQueryStub(); }

        /**
         * Use predictable SQL identifiers in this fixture.
         * @param string $name Column/table name.
         * @return string Unmodified identifier.
         */
        public function quoteName(string $name): string { return $name; }

        /**
         * Count queries without making an SQL connection.
         * @param UefaQueryStub $query Joomla-style query.
         * @param int $offset Result offset.
         * @param int $limit Row limit.
         * @return void
         */
        public function setQuery(UefaQueryStub $query, int $offset = 0, int $limit = 0): void
        {
            ++$this->queries;
            $this->selections[] = $query;
            $this->current = $query;
        }

        /**
         * Return an arbitrary raw season result, including nested values.
         * @return mixed Untrusted database value.
         */
        public function loadResult(): mixed { return $this->season; }

        /**
         * Return arbitrary raw season column results.
         * @return mixed Untrusted season names.
         */
        public function loadColumn(): mixed { return $this->column; }

        /**
         * Return arbitrary raw coefficient records.
         * @return mixed Untrusted country/season/points rows.
         */
        public function loadObjectList(): mixed { return $this->rows; }
    }

    set_error_handler(static function (int $level, string $message): never {
        throw new \ErrorException($message, 0, $level);
    });

    try {
        $legacy = new class extends \sportsmanagementHelper {};
        foreach ([
            ['0:00:00', 0], ['01:02:03', 3723],
            ['0001:02:03', 3723], ['120:59:59', 435599],
            ['bad', 0], ['01:60:01', 0], ['1:01:90', 0],
            [' 1:02:03', 0], ["1:02:03\n", 0],
            [['1:02:03'], 0], [false, 0], [INF, 0],
            [str_repeat('9', 66) . ':01:01', 0],
        ] as [$raw, $expected]) {
            if (\sportsmanagementHelper::time_to_sec($raw) !== $expected) {
                throw new \RuntimeException('Legacy match time parser converted malformed input.');
            }
        }

        foreach ([
            [0, '0:00:00'], [1, '0:00:01'], [3723, '1:02:03'],
            ['0003723', '1:02:03'], [435599, '120:59:59'],
            [-1, '0:00:00'], ['2junk', '0:00:00'], [true, '0:00:00'],
            [[], '0:00:00'], [new \stdClass(), '0:00:00'],
            [PHP_INT_MAX . '0', '0:00:00'],
        ] as [$raw, $expected]) {
            if ($legacy->sec_to_time($raw) !== $expected) {
                throw new \RuntimeException('Legacy interval formatter failed on malformed input.');
            }
        }

        if ($legacy->isMootools12()) {
            throw new \RuntimeException('Obsolete Joomla 1.5 MooTools plugin was reenabled.');
        }

        $time = \Diddipoeler\Component\SportsManagement\Site\Helper\MatchTimeHelper::class;
        $game = (object) [
            'match_date' => date('Y-m-d H:i:s', time() - 65),
            'team1_result' => '2',
            'team2_result' => 2,
        ];
        $config = [
            'mark_now_playing' => true,
            'mark_now_playing_alt_text' => 'Playing %ACTUALTIME%',
            'mark_now_playing_alt_actual_time' => 'Minute %MINUTE%',
            'mark_now_playing_text' => 'Live!',
        ];
        $project = (object) [
            'game_regular_time' => '0090', 'game_parts' => '02',
            'halftime' => '015', 'allow_add_time' => true, 'add_time' => '0030',
        ];
        $live = $time::format($game, $config, [], $project);

        if (!str_contains($live, 'Live!')
            || !str_contains($live, 'Minute')
            || !str_contains($live, 'Playing')) {
            throw new \RuntimeException('Valid live-match project timings were changed.');
        }

        foreach ([
            ['game_regular_time' => ['bad']],
            ['game_regular_time' => '90junk'],
            ['game_regular_time' => true],
            ['game_regular_time' => PHP_INT_MAX . '0'],
            ['game_regular_time' => -4],
            ['game_regular_time' => 0, 'halftime' => 80],
        ] as $invalidSettings) {
            $broken = (object) array_merge(
                ['game_regular_time' => 90, 'game_parts' => 2, 'halftime' => 15],
                $invalidSettings
            );

            if ($time::format($game, $config, [], $broken) !== '12:00') {
                throw new \RuntimeException('Invalid live-match durations produced a false live label.');
            }
        }

        $noisyProject = (object) [
            'game_regular_time' => 90, 'game_parts' => ['bad'],
            'halftime' => ['bad'], 'allow_add_time' => true,
            'add_time' => ['bad'],
        ];
        if (!str_contains($time::format($game, $config, [], $noisyProject), 'Live!')) {
            throw new \RuntimeException('Malformed optional project periods blocked valid live play.');
        }

        $uefa = new \Diddipoeler\Module\SportsManagementUefaWertung\Site\Helper\UefaWertungHelper();
        $database = new UefaDatabaseStub();
        $database->column = ['2025', ['bad'], '2024', false, null, 2024, '2025'];
        $database->rows = [
            (object) ['country' => 'DEU', 'season' => '2024', 'points' => '2.5'],
            (object) ['country' => 'DEU', 'season' => '2025', 'points' => 3.5],
            (object) ['country' => 'FRA', 'season' => '2024', 'points' => '1'],
            (object) ['country' => 'FRA', 'season' => '2025', 'points' => INF],
            (object) ['country' => ['bad'], 'season' => '2024', 'points' => 2],
            (object) ['country' => 'ITA', 'season' => ['2024'], 'points' => 2],
            (object) ['country' => 'ESP', 'season' => '2024', 'points' => ['2']],
            (object) ['country' => 'ESP', 'season' => '2025', 'points' => '3'],
            (object) ['country' => 'NLD', 'season' => '2024', 'points' => '1e999'],
            new \stdClass(),
            ['country' => 'SVK', 'season' => '2025', 'points' => 3],
            (object) ['country' => "Bad\nCountry", 'season' => '2025', 'points' => 2],
        ];
        $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
            public array $messages = [];

            /**
             * Record a frontend module error message.
             * @param string $message Generic Joomla text.
             * @param string $type Message category.
             * @return void
             */
            public function enqueueMessage(string $message, string $type): void
            {
                $this->messages[] = [$message, $type];
            }
        };
        $settings = new \Joomla\Registry\Registry(['s' => '012', 'cfg_which_database' => '000']);
        $payload = $uefa->getData($settings, $app, $database);

        if ($payload['seasons'] !== ['2024', '2025']
            || count($payload['rankings']) !== 1
            || $payload['rankings'][0]['country'] !== 'DEU'
            || $payload['rankings'][0]['points'] !== ['2024' => 2.5, '2025' => 3.5]
            || $payload['rankings'][0]['total'] !== 6.0
            || $database->queries !== 3 || $app->messages !== []) {
            throw new \RuntimeException('UEFA ranking failed to filter malformed database records.');
        }

        foreach ([['bad'], new \stdClass(), "2025\nBad", str_repeat('x', 129)] as $brokenSeason) {
            $invalid = new UefaDatabaseStub();
            $invalid->season = $brokenSeason;
            $empty = $uefa->getData($settings, $app, $invalid);

            if ($empty !== ['seasons' => [], 'rankings' => []] || $invalid->queries !== 1) {
                throw new \RuntimeException('Malformed UEFA season name reached ranking query.');
            }
        }

        $invalid = new UefaDatabaseStub();
        $invalid->column = [['bad'], new \stdClass(), false];
        if ($uefa->getData($settings, $app, $invalid) !== ['seasons' => [], 'rankings' => []]
            || $invalid->queries !== 2) {
            throw new \RuntimeException('Malformed UEFA season list reached country points query.');
        }

        if ($app->messages !== [] || \Joomla\CMS\Log\Log::$messages !== []) {
            throw new \RuntimeException('Expected invalid UEFA rows triggered an error message.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 legacy time, match periods and UEFA row validation passed.\n";
}
