<?php
/**
 * Joomla 5/6 regression checks for frontend team forms, admin IDs and statistics selectors.
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
        public function __construct(private array $values = []) {}
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Table {
    class TeamTable
    {
        public static array $bound = [];
        public static int $writes = 0;
        public function __construct(mixed $database) {}
        public function bind(array $data): bool
        {
            self::$bound = $data;
            return true;
        }
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
            \Joomla\Database\DatabaseInterface $database,
            int $selector
        ): \Joomla\Database\DatabaseInterface {
            ++self::$calls;
            return $database;
        }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Model/EditteamModel.php';
    require_once $root . '/admin/src/Model/ProjectteamsModel.php';
    require_once $root . '/modules/mod_sportsmanagement_teamstatistics_counter/src/Helper/TeamStatisticsCounterHelper.php';

    $siteModel = new \Diddipoeler\Component\SportsManagement\Site\Model\EditteamModel();
    $teamTable = \Diddipoeler\Component\SportsManagement\Administrator\Table\TeamTable::class;

    foreach ([
        ['id' => 0, 'name' => 'Not created'],
        ['id' => '12garbage', 'name' => 'Not created'],
        ['id' => '99999999999999999999999999', 'name' => 'Not created'],
        ['id' => -12, 'name' => 'Not created'],
        ['id' => true, 'name' => 'Not created'],
    ] as $invalid) {
        if ($siteModel->updItem($invalid) || $teamTable::$writes !== 0) {
            throw new \RuntimeException('Invalid frontend team ID was persisted.');
        }
    }

    if (!$siteModel->updItem([
        'id' => '12',
        'name' => 'Before',
        'request' => ['id' => 88, 'name' => 'Legacy', 'flag' => 0, 'disabled' => false],
        'jform' => [
            'id' => 99, 'name' => 'Native', 'published' => 0,
            'picture' => '', 'short_name' => 'N'
        ],
    ])) {
        throw new \RuntimeException('Joomla 5/6 native team form was not saved.');
    }

    $saved = $teamTable::$bound;
    if ($teamTable::$writes !== 1
        || ($saved['id'] ?? null) !== 12
        || ($saved['name'] ?? null) !== 'Native'
        || ($saved['published'] ?? null) !== 0
        || ($saved['picture'] ?? null) !== ''
        || ($saved['disabled'] ?? null) !== false
        || ($saved['flag'] ?? null) !== 0
        || array_key_exists('request', $saved)
        || array_key_exists('jform', $saved)) {
        throw new \RuntimeException('Native team fields or immutable record ID were lost.');
    }

    $adminClass = \Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectteamsModel::class;
    $admin = (new \ReflectionClass($adminClass))->newInstanceWithoutConstructor();
    $parseAdminIds = new \ReflectionMethod($admin, 'normaliseIds');
    $values = ['7', '007', ' 8 ', 8, true, -9, '12junk', '1e3',
        '999999999999999999999999', '009', [], null, 0, 9.5];
    if ($parseAdminIds->invoke($admin, $values) !== [7, 8, 9]
        || $parseAdminIds->invoke($admin, '7') !== []) {
        throw new \RuntimeException('Admin project-team selection accepted invalid IDs.');
    }

    $module = new \Diddipoeler\Module\SportsManagementTeamStatisticsCounter\Site\Helper\TeamStatisticsCounterHelper();
    $selectId = new \ReflectionMethod($module, 'positiveId');
    foreach ([
        ['12', 12], ['0012:club', 12], ['12:fußball', 12],
        ['12garbage', 0], ['12:bad:extra', 0], [0, 0],
        [true, 0], [-1, 0], [1.5, 0], [[], 0],
        ['9999999999999999999999999', 0]
    ] as [$value, $expected]) {
        if ($selectId->invoke(null, $value) !== $expected) {
            throw new \RuntimeException('Module ID parser accepted a malformed value.');
        }
    }

    $db = new class implements \Joomla\Database\DatabaseInterface {
        public int $queries = 0;
        public bool $throw = false;
        public mixed $lastQuery = null;
        public function createQuery(): object
        {
            return new class {
                public array $binds = [];
                public function select(string|array $columns): self { return $this; }
                public function from(string $table): self { return $this; }
                public function join(string $type, string $sql): self { return $this; }
                public function where(string $sql): self { return $this; }
                public function bind(string $name, mixed $value, mixed $type): self
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
        public function setQuery(object $query, int $offset = 0, int $limit = 0): void
        {
            $this->lastQuery = $query;
            ++$this->queries;
        }
        public function loadResult(): int
        {
            if ($this->throw) { throw new \RuntimeException('Private SQL details'); }
            return 0;
        }
    };

    $empty = ['project' => null, 'team' => null, 'stats' => []];
    foreach ([
        ['p' => '12garbage', 'teams' => '22'],
        ['p' => '12', 'teams' => '22garbage'],
        ['p' => '9999999999999999999999', 'teams' => '22'],
        ['p' => '12', 'teams' => true],
        ['p' => '12', 'teams' => ['22']],
        ['p' => 0, 'teams' => '22'],
    ] as $invalid) {
        if ($module->getData(new \Joomla\Registry\Registry($invalid), $db) !== $empty) {
            throw new \RuntimeException('Invalid module selectors did not return empty data.');
        }
    }
    if ($db->queries !== 0
        || \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::$calls !== 0) {
        throw new \RuntimeException('Invalid module selectors reached a database.');
    }

    $result = $module->getData(new \Joomla\Registry\Registry([
        'p' => '0012:project', 'teams' => '022:team',
    ]), $db);
    if ($result !== $empty || $db->queries !== 1
        || ($db->lastQuery->binds[':projectId'] ?? null) !== 12
        || ($db->lastQuery->binds[':teamId'] ?? null) !== 22) {
        throw new \RuntimeException('Valid module selectors lost integer query binding.');
    }

    $db->throw = true;
    if ($module->getData(new \Joomla\Registry\Registry([
        'p' => '12', 'teams' => '22',
    ]), $db) !== $empty
        || count(\Joomla\CMS\Log\Log::$messages) !== 1
        || !str_contains(\Joomla\CMS\Log\Log::$messages[0], 'Private SQL details')) {
        throw new \RuntimeException('Teamstatistics error handling did not protect public output.');
    }

    echo "Joomla 5/6 site/admin/teamstatistics input and failure checks OK\n";
}
