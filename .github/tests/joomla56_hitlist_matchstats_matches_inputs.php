<?php
/**
 * Joomla 5/6 hit lists, administrator match statistics and Matches module guards.
 *
 * Run: php .github/tests/joomla56_hitlist_matchstats_matches_inputs.php
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
        public const INTEGER = 'integer';
    }
}

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface
    {
        public function isClient(string $client): bool;
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Store simulated Joomla module parameters for regression cases.
         *
         * @param array<string,mixed> $values Module option values.
         */
        public function __construct(private array $values = [])
        {
        }

        /**
         * Return the current option, falling back when it is absent.
         *
         * @param string $name Module option name.
         * @param mixed $default Missing-option fallback.
         * @return mixed Stored or default option.
         */
        public function get(string $name, mixed $default = null): mixed
        {
            return $this->values[$name] ?? $default;
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Keep the Joomla language key visible in isolated assertions.
         *
         * @param string $key Requested translation key.
         * @return string Unchanged translation key.
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
        public const ERROR = 1;
        public static array $entries = [];

        /**
         * Record internal query diagnostics without producing PHP output.
         *
         * @param string $message Private diagnostic text.
         * @param int $priority Joomla error severity.
         * @param string $category Component logging channel.
         * @return void
         */
        public static function add(string $message, int $priority, string $category): void
        {
            self::$entries[] = compact('message', 'priority', 'category');
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementModel
    {
        public static ?object $database = null;

        /**
         * Supply the configured Joomla fake database for hit-list queries.
         *
         * @return object Query and data fixture.
         */
        protected function getDatabase(): object
        {
            return self::$database;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel
    {
        public static ?object $database = null;
        public array $errors = [];

        /**
         * Return the fake Joomla administrator database.
         *
         * @return object Database fixture.
         */
        protected function getDatabase(): object
        {
            return self::$database;
        }

        /**
         * Record a translated error shown by the administrator editor.
         *
         * @param string $message Safe Joomla error message.
         * @return void
         */
        public function setError(string $message): void
        {
            $this->errors[] = $message;
        }

        /**
         * Supply an inert table-factory fallback for the native models.
         *
         * @param string $type Requested table type.
         * @param string $prefix Legacy component class prefix.
         * @param array $config Optional table configuration.
         * @return null No test table was requested.
         */
        public function getTable($type = '', $prefix = '', $config = [])
        {
            return null;
        }
    }
}

namespace {
    \define('_JEXEC', 1);

    /**
     * Convert PHP warnings into test exceptions so nested data cannot pass.
     *
     * @param int $severity PHP notice, warning or deprecation severity.
     * @param string $message Diagnostic message from PHP.
     * @return never Always throws to stop an unsafe test.
     */
    function failOnLegacyWarning(int $severity, string $message): never
    {
        throw new \ErrorException($message, 0, $severity);
    }

    /**
     * Verify a migration invariant with a clear failure message.
     *
     * @param bool $condition Assertion result.
     * @param string $message Context when the assertion is false.
     * @return void
     */
    function assertLegacyGuard(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    /**
     * Reproduce a fluent Joomla query without any database connection.
     */
    final class LegacyQuery
    {
        public array $bindings = [];

        /**
         * Accept an array or string SELECT expression.
         *
         * @param array|string $columns Requested columns.
         * @return self This fluent query.
         */
        public function select(array|string $columns): self { return $this; }

        /**
         * Select the legacy SportsManagement table.
         *
         * @param string $name Quoted table name.
         * @return self This fluent query.
         */
        public function from(string $name): self { return $this; }

        /**
         * Record a query restriction for deterministic tests.
         *
         * @param string $condition Bound SQL condition.
         * @return self This fluent query.
         */
        public function where(string $condition): self { return $this; }

        /**
         * Record ordering of matches or hit-list records.
         *
         * @param string $order Sort expression.
         * @return self This fluent query.
         */
        public function order(string $order): self { return $this; }

        /**
         * Capture the integer parameter bound by the administrator model.
         *
         * @param string $key Named query placeholder.
         * @param mixed $value Bound parameter value.
         * @param mixed $type Joomla database parameter type.
         * @return self This fluent query.
         */
        public function bind(string $key, mixed $value, mixed $type): self
        {
            $this->bindings[$key] = [$value, $type];
            return $this;
        }
    }

    /**
     * Simulate successful and failed Joomla database reads for three models.
     */
    final class LegacyDatabase implements \Joomla\Database\DatabaseInterface
    {
        public int $queries = 0;
        public bool $fail = false;
        public mixed $rows = [];
        public int $limit = -1;
        public ?LegacyQuery $query = null;

        /**
         * Construct one fake Joomla query or simulate a provider failure.
         *
         * @return LegacyQuery Fluent query fixture.
         */
        public function createQuery(): LegacyQuery
        {
            ++$this->queries;

            if ($this->fail) {
                throw new \RuntimeException('database password=SECRET');
            }

            return new LegacyQuery();
        }

        /**
         * Preserve SQL identifier quoting and optional table aliases.
         *
         * @param string $name Raw column or table name.
         * @param string|null $alias Optional SQL alias.
         * @return string Quoted identifier placeholder.
         */
        public function quoteName(string $name, ?string $alias = null): string
        {
            return $name . ($alias !== null ? ' ' . $alias : '');
        }

        /**
         * Store query and pagination for assertions without executing SQL.
         *
         * @param LegacyQuery $query Prepared native Joomla query.
         * @param int $offset First requested row.
         * @param int $limit Maximum number of returned rows.
         * @return void
         */
        public function setQuery(LegacyQuery $query, int $offset = 0, int $limit = 0): void
        {
            $this->query = $query;
            $this->limit = $limit;
        }

        /**
         * Return mixed database records, including malformed values.
         *
         * @return mixed Simulated legacy driver result.
         */
        public function loadObjectList(): mixed
        {
            return $this->rows;
        }
    }

    /**
     * Provide a Joomla site application stub without a live CMS.
     */
    final class LegacySiteApplication implements \Joomla\CMS\Application\CMSApplicationInterface
    {
        /**
         * Confirm that matches rendering runs only in the frontend.
         *
         * @param string $client Joomla application type.
         * @return bool Whether this is the site application.
         */
        public function isClient(string $client): bool
        {
            return $client === 'site';
        }
    }

    set_error_handler('failOnLegacyWarning');

    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Model/HitlistModel.php';
    require_once $root . '/admin/src/Model/MatchstatisticModel.php';
    require_once $root . '/admin/src/Model/MatchstaffstatisticModel.php';
    require_once $root . '/modules/mod_sportsmanagement_matches/src/Helper/MatchesHelper.php';

    $db = new LegacyDatabase();
    \Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementModel::$database = $db;
    $hitlist = new \Diddipoeler\Component\SportsManagement\Site\Model\HitlistModel();
    $hitlistClass = \Diddipoeler\Component\SportsManagement\Site\Model\HitlistModel::class;
    $hitlistClass::$_success_text = [];

    foreach ([['team'], new \stdClass(), 'team; DROP TABLE matches', "tea\nm"] as $table) {
        $hitlist->getSportsmanagementHits([], 5, $table);
    }

    foreach ([['5'], true, '5garbage', '-2', new \stdClass()] as $maxHits) {
        $hitlist->getSportsmanagementHits([], $maxHits, 'team');
    }

    assertLegacyGuard($db->queries === 0, 'Invalid hit-list table or limit reached SQL.');

    $db->rows = [(object) ['name' => 'United', 'hits' => 42], ['malformed'], false];
    $hitlist->getSportsmanagementHits([], '0002', 'team');
    assertLegacyGuard(
        $db->queries === 1
        && $db->limit === 2
        && count($hitlistClass::$_success_text['COM_SPORTSMANAGEMENT_HITLIST_TEAM'] ?? []) === 1,
        'Valid hit-list request or malformed driver records changed.'
    );

    $hitlist->getSportsmanagementHits([], '99999999999999999999', 'club');
    assertLegacyGuard($db->limit === 500, 'Oversized hit-list limit was not bounded.');

    $db->fail = true;
    $hitlist->getSportsmanagementHits([], 2, 'project');
    assertLegacyGuard(
        ($hitlistClass::$_success_text['COM_SPORTSMANAGEMENT_HITLIST_PROJECT'] ?? null) === []
        && count(\Joomla\CMS\Log\Log::$entries) === 1
        && !str_contains(\Joomla\CMS\Log\Log::$entries[0]['message'], 'SECRET'),
        'Hit-list SQL failure leaked credentials or aborted the page.'
    );

    $db = new LegacyDatabase();
    \Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel::$database = $db;
    $models = [
        [new \Diddipoeler\Component\SportsManagement\Administrator\Model\MatchstatisticModel(), 'getMatchStatsInput', 'teamplayer_id'],
        [new \Diddipoeler\Component\SportsManagement\Administrator\Model\MatchstaffstatisticModel(), 'getMatchStaffStatsInput', 'team_staff_id'],
    ];

    foreach ($models as [$model, $method, $relation]) {
        foreach ([['15'], true, -1, 0, '15junk', '99999999999999999999999'] as $badMatch) {
            $before = $db->queries;
            $result = $model->{$method}($badMatch, 7, 8);
            assertLegacyGuard($db->queries === $before && $result === [7 => [], 8 => []],
                'Malformed administrator match ID started SQL.');
        }

        assertLegacyGuard($model->{$method}() === [0 => []],
            'Legacy empty statistic editor response structure changed.');

        $db->rows = [
            (object) [
                'projectteam_id' => '0007',
                $relation => '0005',
                'statistic_id' => '003',
                'value' => '12.5',
            ],
            (object) [
                'projectteam_id' => '007',
                $relation => [5],
                'statistic_id' => 3,
                'value' => 4,
            ],
            (object) [
                'projectteam_id' => 99,
                $relation => 5,
                'statistic_id' => 3,
                'value' => 999,
            ],
            (object) [
                'projectteam_id' => 8,
                $relation => 5,
                'statistic_id' => 4,
                'value' => ['unsafe'],
            ],
            (object) [
                'projectteam_id' => 8,
                $relation => 6,
                'statistic_id' => 5,
                'value' => 0,
            ],
            false,
        ];

        $result = $model->{$method}('0015', '7', 8);
        assertLegacyGuard(
            $result === [7 => [5 => [3 => '12.5']], 8 => [6 => [5 => 0]]]
            && $db->query?->bindings[':matchId'] === [15, \Joomla\Database\ParameterType::INTEGER],
            'Match statistics accepted foreign teams, nested values or malformed IDs.'
        );

        $db->fail = true;
        $result = $model->{$method}(15, 7, 8);
        assertLegacyGuard(
            $result === [7 => [], 8 => []]
            && $model->errors === ['JERROR_AN_ERROR_HAS_OCCURRED'],
            'Administrator statistics DB failure was not contained.'
        );
        $db->fail = false;
    }

    $app = new LegacySiteApplication();
    $module = (object) ['id' => 7];
    $matches = new \Diddipoeler\Module\SportsManagementMatches\Site\Helper\MatchesHelper();

    foreach ([['unexpected'], 'yes', '2', '1garbage', 'false', new \stdClass()] as $badFlag) {
        $data = $matches->getData(new \Joomla\Registry\Registry(['ishd_update' => $badFlag]), $app, $module);
        assertLegacyGuard(
            $data === ['matches' => [], 'legacy_update_requested' => false],
            'Malformed Matches legacy update flag was incorrectly enabled.'
        );
    }

    foreach ([true, 1, '1', '0001'] as $enabled) {
        $data = $matches->getData(new \Joomla\Registry\Registry(['ishd_update' => $enabled]), $app, $module);
        assertLegacyGuard(
            $data === ['matches' => [], 'legacy_update_requested' => true],
            'Valid Matches update flag was lost.'
        );
    }

    restore_error_handler();
    echo "Joomla 5/6 hit list, administrator statistics and Matches flags passed.\n";
}
