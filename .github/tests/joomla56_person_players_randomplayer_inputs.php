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
        protected function getDatabase(): mixed { return null; }
        public function setError(string $message): void { $this->error = $message; }
    }
}
namespace Joomla\CMS\Language {
    class Text { public static function _(string $key): string { return $key; } }
}
namespace Joomla\CMS\Log {
    class Log
    {
        public const ERROR = 'error';
        public static array $messages = [];
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
        public function __construct(private array $items = []) {}
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
        public function __construct(mixed $db) {}
        public function bind(array $data): bool { self::$bound = $data; return true; }
        public function check(): bool { return true; }
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

    $db = new class implements \Joomla\Database\DatabaseInterface {
        public int $queries = 0;
        public ?object $last = null;
        public function createQuery(): object
        {
            return new class {
                public array $binds = [];
                public function select(array $fields): self { return $this; }
                public function from(string $table): self { return $this; }
                public function join(string $type, string $condition): self { return $this; }
                public function whereIn(string $field, array $values, string $type): self { return $this; }
                public function where(string $condition): self { return $this; }
                public function group(array $columns): self { return $this; }
                public function bind(string $name, mixed $value, string $type): self
                {
                    $this->binds[$name] = $value;
                    return $this;
                }
            };
        }
        public function quoteName(string $name, ?string $alias = null): string
        {
            return $name . ($alias === null ? '' : ' AS ' . $alias);
        }
        public function setQuery(object $query): self
        {
            $this->last = $query;
            ++$this->queries;
            return $this;
        }
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
