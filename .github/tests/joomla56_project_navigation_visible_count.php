<?php
/**
 * Joomla 5/6 regression for published project navigation and match count failures.
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
        public const STRING = 'string';
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Retain simulated module options without loading Joomla.
         *
         * @param array<string,mixed> $settings Configured test options.
         */
        public function __construct(private array $settings = []) {}

        /**
         * Supply a stored option or its default to the module helper.
         *
         * @param string $key Requested module option.
         * @param mixed $default Default when not configured.
         * @return mixed Configured or default option value.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->settings[$key] ?? $default;
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return a stable translated string for match counter assertions.
         *
         * @param string $key Language key from the native module.
         * @param mixed ...$values Numeric formatted labels.
         * @return string Deterministic translated fixture string.
         */
        public static function sprintf(string $key, mixed ...$values): string
        {
            return $key . ': ' . implode(', ', $values);
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $entries = [];

        /**
         * Store test diagnostics in memory without writing server logs.
         *
         * @param string $message Component diagnostic text.
         * @param int $level Joomla message severity.
         * @param string $category Extension logging category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        /**
         * Return the injected fake database to avoid external connections.
         *
         * @param \Joomla\Database\DatabaseInterface $database Fake connection.
         * @param int $selector Validated SportsManagement selector.
         * @return \Joomla\Database\DatabaseInterface Same fake connection.
         */
        public static function resolve(
            \Joomla\Database\DatabaseInterface $database,
            int $selector = 0
        ): \Joomla\Database\DatabaseInterface {
            return $database;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/site/src/Helper/ProjectNavigationHelper.php';
    require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_count_rekord/src/Helper/CountRekordHelper.php';

    final class MigrationQuery
    {
        public array $conditions = [];
        public array $bindings = [];
        public array $orders = [];

        /**
         * Accept the SELECT expression used by a Joomla query builder.
         *
         * @param string $columns Quoted columns or count expression.
         * @return self Unchanged fluent query fixture.
         */
        public function select(string $columns): self { return $this; }

        /**
         * Accept the source Joomla table for project or match lookups.
         *
         * @param string $table Quoted table with optional alias.
         * @return self Unchanged fluent query fixture.
         */
        public function from(string $table): self { return $this; }

        /**
         * Capture a WHERE clause for public-project visibility assertions.
         *
         * @param string $condition SQL predicate.
         * @return self Current query for chaining.
         */
        public function where(string $condition): self
        {
            $this->conditions[] = $condition;
            return $this;
        }

        /**
         * Capture a typed SQL parameter without interpolating input.
         *
         * @param string $key Placeholder name.
         * @param mixed $value Bound scalar input.
         * @param mixed $type Joomla parameter type.
         * @return self Current query for chaining.
         */
        public function bind(string $key, mixed $value, mixed $type = null): self
        {
            $this->bindings[$key] = $value;
            return $this;
        }

        /**
         * Save the ordered project name and ID tie-breaker expressions.
         *
         * @param string $expression ORDER BY expression.
         * @return self Current query for chaining.
         */
        public function order(string $expression): self
        {
            $this->orders[] = $expression;
            return $this;
        }
    }

    final class MigrationDatabase implements \Joomla\Database\DatabaseInterface
    {
        public ?MigrationQuery $query = null;
        public int $calls = 0;

        /**
         * Set up a simulated project neighbour or match count database result.
         *
         * @param object|null $neighbour Candidate public or draft project.
         * @param mixed $count Raw COUNT(*) value including invalid cases.
         */
        public function __construct(private ?object $neighbour = null, private mixed $count = 0) {}

        /**
         * Create a new query fixture for Joomla model and module helpers.
         *
         * @return MigrationQuery Chainable simulated query.
         */
        public function createQuery(): MigrationQuery { return new MigrationQuery(); }

        /**
         * Preserve table aliases and column names for SQL predicate matching.
         *
         * @param string $name Table or column name.
         * @param string|null $alias Optional table alias.
         * @return string Deterministic quoted name.
         */
        public function quoteName(string $name, ?string $alias = null): string
        {
            return $alias === null ? $name : $name . ' ' . $alias;
        }

        /**
         * Store a query before the test fixture produces a row or aggregate.
         *
         * @param MigrationQuery $query Prepared query.
         * @param int $offset Optional starting row index.
         * @param int $limit Optional maximum row count.
         * @return self Database fixture with captured query.
         */
        public function setQuery(MigrationQuery $query, int $offset = 0, int $limit = 0): self
        {
            ++$this->calls;
            $this->query = $query;
            return $this;
        }

        /**
         * Simulate honoring the query's required published-project filter.
         *
         * @return object|null Public project row, or null for an unpublished row.
         */
        public function loadObject(): ?object
        {
            if (!in_array('p.published = 1', $this->query->conditions ?? [], true)
                || ($this->neighbour->published ?? 0) !== 1) {
                return null;
            }

            return clone $this->neighbour;
        }

        /**
         * Return an unmodified database aggregate for strict count validation.
         *
         * @return mixed Stored count, null, false or malformed data.
         */
        public function loadResult(): mixed { return $this->count; }
    }

    $navigation = \Diddipoeler\Component\SportsManagement\Site\Helper\ProjectNavigationHelper::class;
    $project = (object) ['league_id' => '0004', 'id' => '00012', 'name' => 'League'];
    $draft = new MigrationDatabase((object) ['id' => 13, 'published' => 0, 'name' => 'Draft']);

    if ($navigation::next($draft, $project) !== null || $draft->calls !== 1
        || !in_array('p.published = 1', $draft->query->conditions, true)
        || $draft->query->bindings[':navigationLeagueId'] !== 4
        || $draft->query->bindings[':navigationProjectId'] !== 12) {
        throw new \RuntimeException('Unpublished project can appear in frontend navigation.');
    }

    $published = new MigrationDatabase((object) [
        'id' => '00014', 'name' => 'League II', 'alias' => 'second', 'published' => 1,
    ]);
    $next = $navigation::next($published, $project);
    if (!is_object($next) || $next->slug !== '14:second'
        || $published->query->orders !== ['p.name ASC', 'p.id ASC']) {
        throw new \RuntimeException('Published project navigation and ID:alias routing changed.');
    }

    $invalid = new MigrationDatabase();
    if ($navigation::previous($invalid, (object) ['league_id' => '4oops', 'name' => 'League']) !== null
        || $invalid->calls !== 0) {
        throw new \RuntimeException('Invalid league ID reached the database.');
    }

    $counter = new \Diddipoeler\Module\SportsManagementCountRekord\Site\Helper\CountRekordHelper();
    $settings = new \Joomla\Registry\Registry([
        'jsm_stat_spielpaarungen' => 1,
        'jsm_stat_paarungen' => 20,
    ]);
    $module = (object) ['module' => 'mod_sportsmanagement_count_rekord'];

    foreach ([0, '00017', 21] as $value) {
        $db = new MigrationDatabase(null, $value);
        $rows = $counter->getData($settings, $module, $db);
        if (count($rows) !== 1 || $rows[0]->anzahl !== (int) $value) {
            throw new \RuntimeException('Valid zero, integer or padded decimal count was rejected.');
        }
    }

    foreach ([null, false, -1, -1.5, [], '3junk', '9223372036854775808'] as $value) {
        $db = new MigrationDatabase(null, $value);
        if ($counter->getData($settings, $module, $db) !== [] || $db->calls !== 1) {
            throw new \RuntimeException('Invalid SQL count was reported as zero matches.');
        }
    }

    if (count(\Joomla\CMS\Log\Log::$entries) !== 7) {
        throw new \RuntimeException('Invalid COUNT(*) return values were not logged safely.');
    }

    echo "Published navigation and strict database match count regressions OK\n";
}
