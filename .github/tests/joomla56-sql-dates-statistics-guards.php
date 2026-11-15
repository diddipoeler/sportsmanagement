<?php
/**
 * Joomla 5/6 regression for strict match dates, XML IDs and sport counters.
 *
 * Executes the real site, admin and module helpers with isolated Joomla
 * query stubs; PHP warnings fail the fixture on PHP 8.3 and PHP 8.4.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}

    final class ParameterType
    {
        public const INTEGER = 1;
    }
}

namespace Joomla\CMS\HTML {
    final class HTMLHelper
    {
        /**
         * Return the original SQL timestamp so fixture expectations are exact.
         * @param string $date Valid database match date.
         * @param string $format Joomla date presentation mask.
         * @param string $timezone Configured timezone.
         * @return string Original date supplied for display.
         */
        public static function date(string $date, string $format, string $timezone): string
        {
            return $date;
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return an untranslated Joomla language key in isolation.
         * @param string $key Joomla language key.
         * @return string Same key for stable assertions.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        /**
         * Supply a stable root URL for native sport icon generation.
         * @return string Deterministic Joomla root URL.
         */
        public static function root(): string { return 'https://example.test/'; }
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Store unmodified Joomla module parameter types.
         * @param array<string,mixed> $settings Module configuration map.
         */
        public function __construct(private array $settings) {}

        /**
         * Read a module parameter with Joomla Registry-style defaults.
         * @param string $key Parameter name.
         * @param mixed $default Value when missing.
         * @return mixed Original configured value.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->settings[$key] ?? $default;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        public static int $calls = 0;
        public static array $selectors = [];

        /**
         * Track a selected Joomla statistics connection without opening it.
         * @param \Joomla\Database\DatabaseInterface $db Injected database.
         * @param int $selector Primary or external database option.
         * @return \Joomla\Database\DatabaseInterface Injected database.
         */
        public static function resolve(
            \Joomla\Database\DatabaseInterface $db,
            int $selector
        ): \Joomla\Database\DatabaseInterface {
            ++self::$calls;
            self::$selectors[] = $selector;
            return $db;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Helper/MatchTimeHelper.php';
    require_once $root . '/admin/src/Service/XmlProjectPositionStatisticImportService.php';
    require_once $root . '/modules/mod_sportsmanagement_sports_type_statistics/src/Helper/SportsTypeStatisticsHelper.php';

    final class StatisticsGuardQuery
    {
        public string $table = '';
        public array $columns = [];
        public array $bindings = [];

        /**
         * Record selected columns for the runtime database spy.
         * @param string|array $columns Selected SQL expressions.
         * @return self Fluent query spy.
         */
        public function select(string|array $columns): self
        {
            $this->columns = (array) $columns;
            return $this;
        }

        /**
         * Record a scoped Joomla table reference.
         * @param string $table Quoted source table.
         * @return self Fluent query spy.
         */
        public function from(string $table): self
        {
            $this->table = $table;
            return $this;
        }

        /**
         * Accept a filtered SQL WHERE clause without executing it.
         * @param string $condition Filter expression.
         * @return self Fluent query spy.
         */
        public function where(string $condition): self { return $this; }

        /**
         * Accept a scoped project JOIN without querying a database.
         * @param string $type Join kind.
         * @param string $condition Join condition.
         * @return self Fluent query spy.
         */
        public function join(string $type, string $condition): self { return $this; }

        /**
         * Capture a bound numeric ID for later assertions.
         * @param string $key Parameter name.
         * @param mixed $value Bound parameter.
         * @param mixed $type Binding type.
         * @return self Fluent query spy.
         */
        public function bind(string $key, mixed $value, mixed $type): self
        {
            $this->bindings[$key] = $value;
            return $this;
        }
    }

    final class StatisticsGuardDatabase implements \Joomla\Database\DatabaseInterface
    {
        public int $queries = 0;
        public array $records = [];
        public mixed $existing = null;
        public mixed $sport = null;
        public mixed $projectCount = '0005';
        public mixed $leagueCount = '0002';
        public mixed $groundsFromTeams = ['00011', '11junk', ['12'], true, 12, '922337203685477580800'];
        public mixed $groundsFromMatches = ['12', 13, [], false, '13junk'];
        public array $inserted = [];
        public ?StatisticsGuardQuery $last = null;
        public bool $failInsert = false;

        /**
         * Create a new bound query without database I/O.
         * @return StatisticsGuardQuery New query spy.
         */
        public function createQuery(): StatisticsGuardQuery
        {
            return new StatisticsGuardQuery();
        }

        /**
         * Return the requested identifier unchanged for query assertions.
         * @param string $name Table or column name.
         * @param string|null $alias Optional query alias.
         * @return string Inspectable identifier.
         */
        public function quoteName(string $name, ?string $alias = null): string
        {
            return $name;
        }

        /**
         * Record query execution and retain its selected columns.
         * @param StatisticsGuardQuery $query Query under test.
         * @param int $offset Result offset.
         * @param int $limit Maximum rows.
         * @return void
         */
        public function setQuery(StatisticsGuardQuery $query, int $offset = 0, int $limit = 0): void
        {
            ++$this->queries;
            $this->records[] = $query;
            $this->last = $query;
        }

        /**
         * Return either a legacy sport metadata record or a corrupt object.
         * @return mixed Raw sport record.
         */
        public function loadObject(): mixed
        {
            return $this->sport ?? (object) [
                'id' => 7, 'name' => ['bad'], 'icon' => new \stdClass(),
            ];
        }

        /**
         * Simulate SQL count and existing relation record lookups.
         * @return mixed Configured COUNT or relation primary key.
         */
        public function loadResult(): mixed
        {
            if ($this->last?->table === '#__sportsmanagement_position_statistic') {
                return $this->existing;
            }

            $column = $this->last?->columns[0] ?? null;

            return $column === 'COUNT(DISTINCT p.league_id)'
                ? $this->leagueCount : $this->projectCount;
        }

        /**
         * Return malformed and valid stadium IDs from both venue sources.
         * @return mixed Raw SQL venue column result.
         */
        public function loadColumn(): mixed
        {
            return $this->last?->table === '#__sportsmanagement_project_team'
                ? $this->groundsFromTeams
                : $this->groundsFromMatches;
        }

        /**
         * Record one validated position/statistic insert or simulate failure.
         * @param string $table SQL relationship table.
         * @param object $row Validated link record.
         * @return bool Whether the fixture accepted this insertion.
         */
        public function insertObject(string $table, object $row): bool
        {
            if ($table !== '#__sportsmanagement_position_statistic') {
                throw new \RuntimeException('Importer targeted an unrelated table.');
            }

            if ($this->failInsert) {
                return false;
            }

            $this->inserted[] = $row;
            return true;
        }
    }

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $site = \Diddipoeler\Component\SportsManagement\Site\Helper\MatchTimeHelper::class;

        foreach ([
            '2026-11-15 14:03:21', '2024-02-29 12:00:00',
            '2026-11-15', '2026-11-15 14:03',
            '2026-11-15T14:03:21',
        ] as $valid) {
            if ($site::format((object) ['match_date' => $valid], [], []) !== $valid) {
                throw new \RuntimeException('Valid stored SQL fixture date changed: ' . $valid);
            }
        }

        foreach ([
            '', '0000-00-00 00:00:00', '2025-02-29 12:00:00',
            '2026-02-30 12:00:00', '2026-13-01 12:00:00',
            '2026-12-01 25:00:00', '2026-12-01 12:60:00',
            '2026-12-01 12:30:61', 'next Monday', '+4 hours',
            '2026-11-15 12:30:00junk', "2026-11-15 12:30:00\n",
            ' 2026-11-15 12:30:00', ['bad'],
        ] as $invalid) {
            if ($site::format((object) ['match_date' => $invalid], [], [])
                !== '--&nbsp;:&nbsp;--') {
                throw new \RuntimeException('Malformed SQL date was displayed as a fixture.');
            }
        }

        $admin = new \Diddipoeler\Component\SportsManagement\Administrator\Service\XmlProjectPositionStatisticImportService(
            $db = new StatisticsGuardDatabase()
        );
        $mapPositions = [5 => 12];
        $mapStats = [9 => 42];
        $record = (object) ['id' => '0007', 'position_id' => '0005', 'statistic_id' => '0009'];
        $result = $admin->import(['positionstatistic' => [$record]], $mapPositions, $mapStats);

        if ($db->queries !== 1 || count($db->inserted) !== 1
            || $db->inserted[0]->position_id !== 12
            || $db->inserted[0]->statistic_id !== 42
            || $db->last?->bindings !== [':positionId' => 12, ':statisticId' => 42]
            || !str_contains($result['messages']['Importing position statistic data:'], 'Created new')) {
            throw new \RuntimeException('Valid XML relation was not inserted correctly.');
        }

        $db->existing = '00014';
        $duplicate = $admin->import(['positionstatistic' => [$record]], $mapPositions, $mapStats);

        if (count($db->inserted) !== 1
            || !str_contains($duplicate['messages']['Importing position statistic data:'], 'Using existing')) {
            throw new \RuntimeException('Existing XML relation was inserted again.');
        }

        $badCases = [
            [(object) ['id' => 11, 'position_id' => '5junk', 'statistic_id' => 9], $mapPositions, $mapStats],
            [(object) ['id' => 11, 'position_id' => ['5'], 'statistic_id' => 9], $mapPositions, $mapStats],
            [(object) ['id' => 11, 'position_id' => 5, 'statistic_id' => true], $mapPositions, $mapStats],
            [(object) ['id' => 11, 'position_id' => 5.0, 'statistic_id' => 9], $mapPositions, $mapStats],
            [(object) ['id' => 11, 'position_id' => 5, 'statistic_id' => '999999999999999999999'], $mapPositions, $mapStats],
            [$record, [5 => '12junk'], $mapStats],
            [$record, $mapPositions, [9 => ['42']]],
        ];

        foreach ($badCases as [$badRecord, $positions, $stats]) {
            $queriesBefore = $db->queries;
            $writesBefore = count($db->inserted);
            $message = $admin->import(['positionstatistic' => [$badRecord]], $positions, $stats);

            if ($db->queries !== $queriesBefore || count($db->inserted) !== $writesBefore
                || !str_contains($message['messages']['Importing position statistic data:'], 'Skipping')) {
                throw new \RuntimeException('Malformed XML relation reached a database query.');
            }
        }

        $db->existing = '7junk';
        $admin->import(['positionstatistic' => [$record]], $mapPositions, $mapStats);

        if (count($db->inserted) !== 2) {
            throw new \RuntimeException('Corrupt relation primary key was partially cast.');
        }

        $db->existing = null;
        $db->failInsert = true;

        try {
            $admin->import(['positionstatistic' => [$record]], $mapPositions, $mapStats);
            throw new \RuntimeException('Position/statistic insertion failure was swallowed.');
        } catch (\RuntimeException $error) {
            if ($error->getCode() !== 500) {
                throw $error;
            }
        }

        $module = new \Diddipoeler\Module\SportsManagementSportsTypeStatistics\Site\Helper\SportsTypeStatisticsHelper();
        $resolver = \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::class;
        $hidden = array_fill_keys([
            'show_playgrounds', 'show_clubs', 'show_teams', 'show_players',
            'show_divisions', 'show_rounds', 'show_matches',
            'show_player_events', 'show_player_stats', 'show_seasons',
        ], 0);

        foreach ([
            ['sportstypes' => 7, 'cfg_which_database' => 2],
            ['sportstypes' => 7, 'cfg_which_database' => '0002'],
            ['sportstypes' => ['7']],
            ['sportstypes' => 7, 'cfg_which_database' => ['1']],
            ['sportstypes' => 7, 'cfg_which_database' => str_repeat('0', 130) . '1'],
        ] as $malformed) {
            $fixture = new StatisticsGuardDatabase();
            $calls = $resolver::$calls;
            $result = $module->getData(new \Joomla\Registry\Registry($malformed), $fixture);

            if ($result !== ['sportstype' => null, 'counts' => ['projects' => 0]]
                || $resolver::$calls !== $calls || $fixture->queries !== 0) {
                throw new \RuntimeException('Invalid sport/DB selectors reached the database.');
            }
        }

        $fixture = new StatisticsGuardDatabase();
        // array_replace() deliberately overrides the disabled baseline:
        // PHP's array union operator would keep show_playgrounds=0.
        $params = new \Joomla\Registry\Registry(array_replace($hidden, [
            'sportstypes' => '0007', 'cfg_which_database' => '0001',
            'show_leagues' => '0001', 'show_playgrounds' => 1,
        ]));
        $result = $module->getData($params, $fixture);

        if ($result['counts'] !== ['projects' => 5, 'leagues' => 2, 'playgrounds' => 3]
            || $result['sportstype']->name !== ''
            || $result['sportstype']->icon_url !== ''
            || $resolver::$selectors[array_key_last($resolver::$selectors)] !== 1
            || $fixture->queries !== 5) {
            throw new \RuntimeException('Sport statistics failed valid scoped counter filtering.');
        }

        $badProjectCount = new StatisticsGuardDatabase();
        $badProjectCount->projectCount = ['corrupt'];
        $result = $module->getData($params, $badProjectCount);

        if ($result['counts'] !== ['projects' => 0] || $badProjectCount->queries !== 2) {
            throw new \RuntimeException('Corrupt project count became a positive count.');
        }

        $badFlagDb = new StatisticsGuardDatabase();
        $badFlags = new \Joomla\Registry\Registry($hidden + [
            'sportstypes' => 7, 'show_leagues' => ['1'],
        ]);
        $badFlag = $module->getData($badFlags, $badFlagDb);

        if ($badFlag['counts'] !== ['projects' => 5] || $badFlagDb->queries !== 2) {
            throw new \RuntimeException('Malformed module show_* option enabled an SQL query.');
        }

        $badVenueDb = new StatisticsGuardDatabase();
        $badVenueDb->groundsFromTeams = 'not an array';
        $badVenueDb->groundsFromMatches = false;
        $venueless = $module->getData($params, $badVenueDb);

        if ($venueless['counts']['playgrounds'] !== 0) {
            throw new \RuntimeException('Malformed SQL venue columns generated fake playgrounds.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 SQL match dates, imported IDs and sport counter guards passed.\n";
}
