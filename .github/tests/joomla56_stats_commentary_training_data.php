<?php
/**
 * Joomla 5/6 statistics ranking, administrator commentary and training guards.
 *
 * Usage: php .github/tests/joomla56_stats_commentary_training_data.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\MVC\Factory {
    interface MVCFactoryInterface {}
}

namespace Joomla\Database {
    interface DatabaseInterface {}

    final class ParameterType
    {
        public const INTEGER = 'integer';
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return a predictable translation without bootstrapping Joomla.
         *
         * @param string $key Requested Joomla language constant.
         * @return string Stable translated label for assertions.
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
         * Capture privileged diagnostics to verify administrator error handling.
         *
         * @param string $message Internal warning or exception information.
         * @param int $priority Joomla logging severity.
         * @param string $category Component log channel.
         * @return void
         */
        public static function add(string $message, int $priority, string $category): void
        {
            self::$entries[] = compact('message', 'priority', 'category');
        }
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Store module configuration without an installed Joomla framework.
         *
         * @param array<string,mixed> $values Module settings for this fixture.
         */
        public function __construct(private array $values = [])
        {
        }

        /**
         * Return an option or the specified fallback value.
         *
         * @param string $key Name of the module parameter.
         * @param mixed $default Default for absent options.
         * @return mixed Configured option or default.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementProjectModel
    {
        public static mixed $statFixtures = [];

        /**
         * Supply artificial project statistics to the native ranking models.
         *
         * @param mixed $statId Selected statistic identifiers.
         * @param int $group Legacy statistic type selector.
         * @return mixed Nested fixture provider records.
         */
        protected function getProjectStats(mixed $statId, int $group): mixed
        {
            return self::$statFixtures;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel
    {
        public static ?object $database = null;
        public array $errors = [];

        /**
         * Return the injected fake Joomla database for commentary reads.
         *
         * @return object Database fixture.
         */
        protected function getDatabase(): object
        {
            return self::$database;
        }

        /**
         * Capture errors visible to the administrator commentary view.
         *
         * @param string $message Translated message supplied by the model.
         * @return void
         */
        public function setError(string $message): void
        {
            $this->errors[] = $message;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        /**
         * Reuse the supplied Joomla database for the training module fixture.
         *
         * @param \Joomla\Database\DatabaseInterface $database Mock fallback database.
         * @param int $selector Validated component database selection.
         * @return \Joomla\Database\DatabaseInterface Selected fixture database.
         */
        public static function resolve(
            \Joomla\Database\DatabaseInterface $database,
            int $selector
        ): \Joomla\Database\DatabaseInterface {
            return $database;
        }
    }
}

namespace {
    \define('_JEXEC', 1);

    /**
     * Assert a PHP 8 compatibility invariant with a targeted error message.
     *
     * @param bool $condition Whether the expected behaviour was observed.
     * @param string $message Regression diagnostic when false.
     * @return void
     */
    function assertStatsCompat(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    /**
     * Fail tests on PHP conversion notices, deprecations and warnings.
     *
     * @param int $level Native PHP warning severity.
     * @param string $message Runtime error text.
     * @return never Always raises an exception.
     */
    function throwStatsWarnings(int $level, string $message): never
    {
        throw new \ErrorException($message, 0, $level);
    }

    set_error_handler('throwStatsWarnings');

    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Model/SportsManagementStatsRankingModel.php';
    require_once $root . '/site/src/Model/StatsrankingteamsModel.php';
    require_once $root . '/admin/src/Model/MatchcommentaryModel.php';
    require_once $root . '/modules/mod_sportsmanagement_trainingsdata/src/Helper/TrainingsDataHelper.php';

    $rankingClass = \Diddipoeler\Component\SportsManagement\Site\Model\StatsrankingteamsModel::class;
    $ranking = (new \ReflectionClass($rankingClass))->newInstanceWithoutConstructor();

    $ranking->setStatid(['0007', 7, '8garbage', ['9'], true, '999999999999999999999999', 8]);
    assertStatsCompat($ranking->stat_id === [7, 8], 'Statistic selector coerced malformed IDs.');
    $ranking->setStatid('5|0005|12');
    assertStatsCompat($ranking->stat_id === [5, 12], 'Valid pipe-separated statistic IDs changed.');
    $ranking->setStatid(['0', 5]);
    assertStatsCompat($ranking->stat_id === 0, 'Legacy zero statistic selector changed.');
    $ranking->setOrder('DESC');
    assertStatsCompat(
        $ranking->setOrder(['bad']) === 'desc'
        && $ranking->setOrder('unsupported') === 'desc',
        'Unsupported sort values replaced a valid ranking order.'
    );

    \Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementProjectModel::$statFixtures = [
        [
            new class {
                public string $id = '0007';

                /**
                 * Return a sample player ranking to check valid statistic providers.
                 *
                 * @param mixed ...$args Project, division, team, page and order.
                 * @return array<int,string> Deterministic mock rows.
                 */
                public function getPlayersRanking(mixed ...$args): array
                {
                    return ['player'];
                }

                /**
                 * Return a sample team ranking for the same statistic provider.
                 *
                 * @param mixed ...$args Project and ranking sort settings.
                 * @return array<int,string> Deterministic mock rows.
                 */
                public function getTeamsRanking(mixed ...$args): array
                {
                    return ['team'];
                }
            },
            (object) ['id' => '7junk'],
            (object) ['id' => ['nested']],
            'invalid',
        ],
        [
            (object) ['id' => 0],
            (object) ['id' => '999999999999999999999999'],
        ],
    ];
    assertStatsCompat(
        array_keys($ranking->getProjectUniqueStats()) === [7]
        && $ranking->getPlayersStats() === [7 => ['player']]
        && $ranking->getTeamsStats() === [7 => ['team']],
        'Native ranking providers accepted malformed statistic IDs.'
    );

    $totals = $ranking->getTeamsTotal([
        7 => [
            (object) ['team_id' => '0042', 'total' => '3.5'],
            (object) ['team_id' => 42, 'total' => ['nested']],
            (object) ['team_id' => '42junk', 'total' => 500],
            (object) ['team_id' => '0043', 'total' => '-1.5'],
            null,
        ],
        '0008' => [
            (object) ['team_id' => 42, 'total' => 1.5],
            (object) ['team_id' => '9999999999999999999999999', 'total' => 100],
            (object) ['team_id' => 43, 'total' => INF],
            (object) ['team_id' => 43, 'total' => '9e999'],
        ],
        'invalid' => [(object) ['team_id' => 99, 'total' => 50]],
    ]);
    assertStatsCompat(
        $totals === [
            ['team_id' => 42, 'total' => 5.0, 7 => 3.5, 8 => 1.5],
            ['team_id' => 43, 'total' => -1.5, 7 => -1.5],
        ],
        'Team ranking converted invalid totals or team IDs.'
    );

    /**
     * Minimal Joomla SQL builder used to verify match-commentary ID binding.
     */
    final class StatsCommentaryQuery
    {
        public array $bindings = [];

        /**
         * Accept a selected SQL column expression for the fixture.
         *
         * @param string $expression Selected columns.
         * @return self Fluent query.
         */
        public function select(string $expression): self { return $this; }

        /**
         * Set the commentary source table in the fixture query.
         *
         * @param string $table Joomla quoted table identifier.
         * @return self Fluent query.
         */
        public function from(string $table): self { return $this; }

        /**
         * Add a scoped match condition to the fixture.
         *
         * @param string $expression Bound SQL where expression.
         * @return self Fluent query.
         */
        public function where(string $expression): self { return $this; }

        /**
         * Record the parameter bound by the Joomla commentary model.
         *
         * @param string $name SQL placeholder name.
         * @param mixed $value Prepared value.
         * @param mixed $type Joomla database parameter type.
         * @return self Fluent query.
         */
        public function bind(string $name, mixed $value, mixed $type): self
        {
            $this->bindings[$name] = [$value, $type];
            return $this;
        }

        /**
         * Preserve the sort expression without a database connection.
         *
         * @param string $column Chronological sort column.
         * @return self Fluent query.
         */
        public function order(string $column): self { return $this; }
    }

    /**
     * Count attempted queries and simulate malformed commentary driver rows.
     */
    final class StatsCommentaryDb
    {
        public int $queries = 0;
        public bool $failCreation = false;
        public ?StatsCommentaryQuery $query = null;

        /**
         * Produce a fluent query or an intentionally failing database driver.
         *
         * @return StatsCommentaryQuery Prepared query object.
         */
        public function createQuery(): StatsCommentaryQuery
        {
            ++$this->queries;

            if ($this->failCreation) {
                throw new \RuntimeException('Simulated internal SQL driver failure.');
            }

            return new StatsCommentaryQuery();
        }

        /**
         * Reproduce Joomla identifier quoting in an isolated fixture.
         *
         * @param string $column Raw SQL column name.
         * @return string Stable field identifier.
         */
        public function quoteName(string $column): string { return $column; }

        /**
         * Retain the completed query to inspect bound parameters.
         *
         * @param StatsCommentaryQuery $query Native Joomla query.
         * @return void
         */
        public function setQuery(StatsCommentaryQuery $query): void { $this->query = $query; }

        /**
         * Return one valid and two corrupt database result rows.
         *
         * @return array<int,mixed> Simulated driver result.
         */
        public function loadObjectList(): array
        {
            return [(object) ['id' => 4], null, ['nested']];
        }
    }

    $db = new StatsCommentaryDb();
    \Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel::$database = $db;
    $commentary = new \Diddipoeler\Component\SportsManagement\Administrator\Model\MatchcommentaryModel();

    foreach ([[], true, -1, 0, '42junk', '4.2', '99999999999999999999999', ' 4'] as $invalid) {
        assertStatsCompat(
            $commentary->getMatchCommentary($invalid) === [] && $db->queries === 0,
            'Invalid administrator match ID queried the database.'
        );
    }

    assertStatsCompat(
        count($commentary->getMatchCommentary('0004')) === 1
        && $db->query?->bindings[':matchId'] === [4, \Joomla\Database\ParameterType::INTEGER],
        'Valid padded match ID was not safely bound to the commentary query.'
    );

    $db->failCreation = true;
    assertStatsCompat(
        $commentary->getMatchCommentary(4) === []
        && $commentary->errors === ['JERROR_AN_ERROR_HAS_OCCURRED']
        && \Joomla\CMS\Log\Log::$entries !== [],
        'A failing Joomla query constructor exposed a private error to the administrator.'
    );

    /**
     * Standalone fluent training query builder for native module regressions.
     */
    final class StatsTrainingQuery
    {
        /**
         * Accept configured training columns.
         *
         * @param array<int,string> $columns Selected fields.
         * @return self Fluent query.
         */
        public function select(array $columns): self { return $this; }

        /**
         * Select the training data table for the module query.
         *
         * @param string $table Joomla training table name.
         * @return self Fluent query.
         */
        public function from(string $table): self { return $this; }

        /**
         * Restrict the training lookup by bound team ID.
         *
         * @param string $condition Bound where expression.
         * @return self Fluent query.
         */
        public function where(string $condition): self { return $this; }

        /**
         * Bind an integer team identifier for isolated query execution.
         *
         * @param string $name Named query placeholder.
         * @param mixed $value Bound team ID.
         * @param mixed $type Joomla database parameter type.
         * @return self Fluent query.
         */
        public function bind(string $name, mixed $value, mixed $type): self { return $this; }

        /**
         * Accept the expected weekday and training start ordering.
         *
         * @param array<int,string> $columns Training sort expressions.
         * @return self Fluent query.
         */
        public function order(array $columns): self { return $this; }
    }

    /**
     * Return mixed old-driver rows without a real Joomla database connection.
     */
    final class StatsTrainingDb implements \Joomla\Database\DatabaseInterface
    {
        /**
         * Create a fluent training statement.
         *
         * @return StatsTrainingQuery Module query fixture.
         */
        public function createQuery(): StatsTrainingQuery { return new StatsTrainingQuery(); }

        /**
         * Return one quoted Joomla SQL column.
         *
         * @param string $name Identifier being quoted.
         * @return string Stable field name.
         */
        public function quoteName(string $name): string { return $name; }

        /**
         * Accept the prepared training statement without executing it.
         *
         * @param StatsTrainingQuery $query Prepared SQL fixture.
         * @return void
         */
        public function setQuery(StatsTrainingQuery $query): void {}

        /**
         * Supply valid, out-of-bounds and malformed legacy training rows.
         *
         * @return array<int,mixed> Mixed driver results.
         */
        public function loadObjectList(): array
        {
            return [
                (object) [
                    'dayofweek' => '007',
                    'time_start' => '0003660',
                    'time_end' => '86400',
                    'place' => 'Gym',
                    'notes' => "Warmup\nPractice",
                ],
                (object) [
                    'dayofweek' => 99,
                    'time_start' => -10,
                    'time_end' => ['bad'],
                    'place' => ['nested'],
                    'notes' => false,
                ],
                'malformed row',
            ];
        }
    }

    $training = new \Diddipoeler\Module\SportsManagementTrainingsData\Site\Helper\TrainingsDataHelper();
    $rows = $training->getData(
        new \Joomla\Registry\Registry(['teams' => '0008', 'cfg_which_database' => 0]),
        new StatsTrainingDb()
    );

    assertStatsCompat(
        count($rows) === 2
        && $rows[0]->dayofweek === 7
        && $rows[0]->time_start === 3660
        && $rows[0]->time_end === 0
        && $rows[0]->place === 'Gym'
        && $rows[1]->dayofweek === 0
        && $rows[1]->time_start === 0
        && $rows[1]->time_end === 0
        && $rows[1]->place === ''
        && $rows[1]->notes === '',
        'TrainingsData retained invalid weekday, timestamp or text settings.'
    );

    restore_error_handler();
    echo "Joomla 5/6 statistics, commentary and training regressions passed.\n";
}
