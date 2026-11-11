<?php
/**
 * Joomla 5/6 regressions for person editor, administrator IDs and random player.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Model {
    class AdminModel
    {
        public ?string $error = null;
        /**
         * Return no database because administrator model storage is stubbed.
         * @return mixed No actual connection in this isolated test.
         */
        protected function getDatabase(): mixed { return null; }
        /**
         * Capture a model error so the test can inspect failure reporting.
         * @param string $message Error to expose through the test double.
         * @return void
         */
        public function setError(string $message): void { $this->error = $message; }
    }
}
namespace Joomla\CMS\Language {
    class Text
    {
        /**
         * Return the requested language key without loading translations.
         * @param string $key Translation key passed to the stub.
         * @return string Unmodified key for predictable assertions.
         */
        public static function _(string $key): string { return $key; }
    }
}
namespace Joomla\CMS\Log {
    class Log
    {
        public const ERROR = 'error';
        public static array $messages = [];
        /**
         * Record an error message in the test log instead of writing a file.
         * @param string $message Diagnostic text.
         * @param mixed $level Requested log severity.
         * @param string $category Joomla logging category.
         * @return void
         */
        public static function add(string $message, mixed $level, string $category): void
        {
            self::$messages[] = $message;
        }
    }
}
namespace Joomla\Database {
    interface DatabaseInterface {}
    class ParameterType { public const INTEGER = 'integer'; }
}
namespace Joomla\Registry {
    class Registry
    {
        /**
         * Initialise the lightweight fixture without Joomla dependencies.
         * @return void
         * @param array $items Registry defaults held in memory.
         */
        public function __construct(private array $items = []) {}
        /**
         * Read a fixture parameter or provide the specified default.
         * @param string $key Requested setting.
         * @param mixed $default Value to use if the key is absent.
         * @return mixed Stored setting or default.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->items[$key] ?? $default;
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Table {
    class PersonTable
    {
        public static array $bound = [];
        public static int $writes = 0;
        /**
         * Initialise the lightweight fixture without Joomla dependencies.
         * @return void
         * @param mixed $db Database service ignored by the fake table.
         */
        public function __construct(mixed $db) {}
        /**
         * Capture the submitted person fields for later write assertions.
         * @param array $data Form data passed to the fake table.
         * @return bool True to simulate a successful binding.
         */
        public function bind(array $data): bool { self::$bound = $data; return true; }
        /**
         * Accept a fixture row after binding so save logic can proceed.
         * @return bool True for this permissive test fixture.
         */
        public function check(): bool { return true; }
        /**
         * Count a simulated database write without persisting records.
         * @return bool True to simulate a successful write.
         */
        public function store(): bool { ++self::$writes; return true; }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementListModel {}
}
namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementDatabaseResolver
    {
        public static int $calls = 0;
        /**
         * Record the requested connection and return the injected database.
         * @param \Joomla\Database\DatabaseInterface $database Fallback connection.
         * @param int $which Requested database selector.
         * @return \Joomla\Database\DatabaseInterface Same injected connection.
         */
        public static function resolve(
            \Joomla\Database\DatabaseInterface $database, int $which
        ): \Joomla\Database\DatabaseInterface {
            ++self::$calls;
            return $database;
        }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Model/EditpersonModel.php';
    require_once $root . '/admin/src/Model/PlayersModel.php';
    require_once $root . '/modules/mod_sportsmanagement_randomplayer/src/Helper/RandomPlayerHelper.php';

    $site = new \Diddipoeler\Component\SportsManagement\Site\Model\EditpersonModel();
    $table = \Diddipoeler\Component\SportsManagement\Administrator\Table\PersonTable::class;
    foreach ([
        ['id' => 0], ['id' => -3], ['id' => 3.2],
        ['id' => '3garbage'], ['id' => ['3']],
        ['id' => '99999999999999999999999999999'],
    ] as $invalid) {
        if ($site->updItem($invalid) || $table::$writes !== 0) {
            throw new \RuntimeException('Malformed frontend person ID was saved.');
        }
    }

    if (!$site->updItem([
        'id' => '0015', 'firstname' => 'Old',
        'request' => [
            'id' => 77, 'firstname' => 'Legacy',
            'published' => 0, 'flag' => false,
        ],
        'jform' => [
            'id' => 88, 'firstname' => 'Native',
            'picture' => '', 'published' => 0, 'nickname' => '',
        ],
    ])) {
        throw new \RuntimeException('Joomla native person form could not be saved.');
    }

    $bound = $table::$bound;
    if ($table::$writes !== 1 || ($bound['id'] ?? null) !== 15
        || ($bound['firstname'] ?? null) !== 'Native'
        || ($bound['picture'] ?? null) !== ''
        || ($bound['published'] ?? null) !== 0
        || ($bound['flag'] ?? null) !== false
        || ($bound['nickname'] ?? null) !== ''
        || isset($bound['request']) || isset($bound['jform'])) {
        throw new \RuntimeException('Person form lost cleared values or trusted nested IDs.');
    }

    $adminName = \Diddipoeler\Component\SportsManagement\Administrator\Model\PlayersModel::class;
    $ids = new \ReflectionMethod($adminName, 'normaliseSelectedIds');
    $values = [7, '007', ' 8 ', 8, -2, 0, true, 2.5,
        ['9'], '11junk', '1e3', '009',
        '99999999999999999999999999999', null];
    if ($ids->invoke(null, $values) !== [7, 8, 9]
        || $ids->invoke(null, '7') !== []) {
        throw new \RuntimeException('Invalid administrator persons IDs accepted.');
    }

    $module = new \Diddipoeler\Module\SportsManagementRandomPlayer\Site\Helper\RandomPlayerHelper();
    $season = new \ReflectionMethod($module, 'positiveId');
    foreach ([
        [12, 12], ['0012', 12], ['12:season', 12], ['012:fußball', 12],
        [0, 0], [-3, 0], [true, 0], [2.5, 0],
        ['12suffix', 0], ['12:bad:other', 0], ['1e3', 0],
        ['999999999999999999999999999999', 0],
    ] as [$input, $expected]) {
        if ($season->invoke(null, $input) !== $expected) {
            throw new \RuntimeException('Invalid RandomPlayer season ID normalization.');
        }
    }

    // Project and team multi-selects must accept the same Unicode aliases
    // and zero-padded IDs as the season selector without losing uniqueness.
    $selection = new \ReflectionMethod($module, 'normaliseIds');
    if ($selection->invoke($module, ['0012:fußball', '12', '0007:équipe', '0', '999999999999999999999']) !== [12, 7]
        || $selection->invoke($module, '0005:crème,5;0010:team') !== [5, 10]
        || $selection->invoke($module, ['abc', '5:bad:alias', true, 1.5]) !== []) {
        throw new \RuntimeException('RandomPlayer project/team selector normalization mismatch.');
    }

    $db = new class implements \Joomla\Database\DatabaseInterface {
        public int $queries = 0;
        public ?object $last = null;
        /**
         * Build a chainable query fixture that records prepared bindings.
         * @return object Lightweight query builder for the assertions.
         */
        public function createQuery(): object
        {
            return new class {
                public array $binds = [];
                /**
                 * Accept selected columns while preserving fluent query construction.
                 * @param array $fields Requested result columns.
                 * @return self Query fixture for further clauses.
                 */
                public function select(array $fields): self { return $this; }
                /**
                 * Accept the table reference for a simulated SELECT query.
                 * @param string $table Quoted table identifier.
                 * @return self Query fixture for further clauses.
                 */
                public function from(string $table): self { return $this; }
                /**
                 * Accept a relational join in the simulated database query.
                 * @param string $type SQL join type.
                 * @param string $condition Join condition.
                 * @return self Query fixture for further clauses.
                 */
                public function join(string $type, string $condition): self { return $this; }
                /**
                 * Accept an integer filter without querying a real database.
                 * @param string $field Identifier being filtered.
                 * @param array $values Selected IDs.
                 * @param string $type Bound value type.
                 * @return self Query fixture for further clauses.
                 */
                public function whereIn(string $field, array $values, string $type): self { return $this; }
                /**
                 * Accept a predicate while preserving the query builder chain.
                 * @param string $condition SQL predicate text.
                 * @return self Query fixture for further clauses.
                 */
                public function where(string $condition): self { return $this; }
                /**
                 * Accept grouped column names for the simulated team lookup.
                 * @param array $columns Names of grouped columns.
                 * @return self Query fixture for further clauses.
                 */
                public function group(array $columns): self { return $this; }
                /**
                 * Record a named query parameter for integer-binding assertions.
                 * @param string $name Placeholder name.
                 * @param mixed $value Value to bind.
                 * @param string $type Declared parameter type.
                 * @return self Query fixture for further clauses.
                 */
                public function bind(string $name, mixed $value, string $type): self
                {
                    $this->binds[$name] = $value;
                    return $this;
                }
            };
        }
        /**
         * Build a readable quoted-name surrogate for the stub query.
         * @param string $name Column or table identifier.
         * @param string|null $alias Optional SQL alias.
         * @return string Fixture identifier with optional alias.
         */
        public function quoteName(string $name, ?string $alias = null): string
        {
            return $name . ($alias === null ? '' : ' AS ' . $alias);
        }
        /**
         * Save the query and increment the count of database accesses.
         * @param object $query Prepared query fixture.
         * @return self Database fixture for subsequent loading.
         */
        public function setQuery(object $query): self
        {
            $this->last = $query;
            ++$this->queries;
            return $this;
        }
        /**
         * Return an empty selection to exercise no-player scenarios.
         * @return array No eligible players in the fixture.
         */
        public function loadObjectList(): array { return []; }
    };

    $resolver = \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::class;
    foreach ([
        ['p' => ['7'], 's' => '10bad'],
        ['p' => ['7'], 's' => -1],
        ['p' => ['7'], 's' => ['12']],
        ['p' => ['7'], 's' => '9999999999999999999999'],
        ['p' => ['bad'], 's' => 10],
    ] as $values) {
        if ($module->getData(new \Joomla\Registry\Registry($values), $db) !== []) {
            throw new \RuntimeException('Invalid RandomPlayer selection unexpectedly returned players.');
        }
    }
    if ($db->queries !== 0 || $resolver::$calls !== 0) {
        throw new \RuntimeException('Invalid RandomPlayer selectors accessed database.');
    }

    if ($module->getData(new \Joomla\Registry\Registry([
        'p' => ['7'], 'teams' => ['22'], 's' => '0012:season',
    ]), $db) !== []) {
        throw new \RuntimeException('Empty RandomPlayer database returned a player.');
    }
    if ($db->queries !== 1 || $resolver::$calls !== 1
        || ($db->last->binds[':eligibleSeason'] ?? null) !== 12
        || ($db->last->binds[':eligiblePlayerSeason'] ?? null) !== 12) {
        throw new \RuntimeException('Valid RandomPlayer season not bound as integer.');
    }

    echo "Joomla 5/6 person form, admin IDs and RandomPlayer selectors OK\n";
}
