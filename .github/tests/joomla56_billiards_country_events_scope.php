<?php
/**
 * Joomla 5/6 regression: billards roster ownership, admin country and event filters.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Model {
    class AdminModel {
        public string $error = '';
        public function setError(string $error): void { $this->error = $error; }
    }
}
namespace Joomla\CMS\Component {
    class ComponentHelper {
        public static function getParams(string $component): \Joomla\Registry\Registry {
            return new \Joomla\Registry\Registry();
        }
    }
}
namespace Joomla\CMS\Log {
    class Log {
        public const ERROR = 'error';
        public static array $entries = [];
        public static function add(string $message, mixed $level, string $category): void {
            self::$entries[] = $message;
        }
    }
}
namespace Joomla\CMS\Language {
    class Text { public static function _(string $key): string { return $key; } }
}
namespace Joomla\CMS\Date {
    class Date {
        public function __construct(mixed ...$params) {}
        public function toSql(): string { return '2026-10-14 00:00:00'; }
    }
}
namespace Joomla\CMS\Uri {
    class Uri { public static function root(): string { return 'https://sports.test/'; } }
}
namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}
namespace Joomla\Database {
    interface DatabaseInterface {}
    class ParameterType {
        public const INTEGER = 'integer';
        public const STRING = 'string';
    }
}
namespace Joomla\Registry {
    class Registry {
        public function __construct(private array $values = []) {}
        public function get(string $key, mixed $default = null): mixed {
            return $this->values[$key] ?? $default;
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementSiteApplicationResolver {
        public static ?\Joomla\CMS\Application\CMSApplicationInterface $app = null;
        public static function resolve(): \Joomla\CMS\Application\CMSApplicationInterface {
            return self::$app;
        }
    }
    class SportsManagementDatabaseResolver {
        public static int $calls = 0;
        public static bool $offline = false;
        public static function resolve(
            \Joomla\Database\DatabaseInterface $db,
            int $which
        ): \Joomla\Database\DatabaseInterface {
            ++self::$calls;
            if (self::$offline) {
                throw new \RuntimeException('PRIVATE SQL CREDENTIALS');
            }
            return $db;
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel extends \Joomla\CMS\MVC\Model\AdminModel {
        public static mixed $db = null;
        protected function getDatabase(): mixed { return self::$db; }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Model/EditmatchModel.php';
    require_once $root . '/admin/src/Model/TeamplayerModel.php';
    require_once $root . '/modules/mod_sportsmanagement_eventsranking/src/Helper/EventsRankingHelper.php';

    class MatchQueryStub {
        public string $table = '';
        public array $binds = [];
        public function select(string|array $columns): self { return $this; }
        public function from(string $table): self { $this->table = $table; return $this; }
        public function join(string $type, string $sql): self { return $this; }
        public function where(string $condition): self { return $this; }
        public function order(string $ordering): self { return $this; }
        public function bind(string $key, mixed $value, mixed $type): self {
            $this->binds[$key] = $value;
            return $this;
        }
    }
    class MatchDatabaseStub implements \Joomla\Database\DatabaseInterface {
        public int $queries = 0;
        public int $writes = 0;
        public bool $validMatchProject = false;
        public bool $failsPositions = false;
        public ?MatchQueryStub $last = null;
        public array $rows = [];
        public function createQuery(): MatchQueryStub { return new MatchQueryStub(); }
        public function quoteName(string $field, ?string $alias = null): string {
            return $field . ($alias === null ? '' : ' AS ' . $alias);
        }
        public function setQuery(MatchQueryStub $query, int $offset = 0, int $limit = 0): self {
            $this->last = $query;
            ++$this->queries;
            return $this;
        }
        public function loadResult(): int {
            return $this->validMatchProject ? 1 : 0;
        }
        public function loadObjectList(): array {
            if ($this->failsPositions) {
                throw new \RuntimeException('PRIVATE PROJECT SQL DETAILS');
            }
            return [(object) [
                'name' => 'COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_PLAYER',
                'id' => 34,
            ]];
        }
        public function insertObject(string $table, object $row): void {
            ++$this->writes;
            $this->rows[] = $row;
        }
    }
    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        public array $messages = [];
        public function getIdentity(): object { return (object) ['id' => 5]; }
        public function enqueueMessage(string $message, string $type = 'message'): void {
            $this->messages[] = [$message, $type];
        }
    };
    $db = new MatchDatabaseStub();
    $resolver = \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver::class;
    $resolver::$app = $app;
    $modelClass = \Diddipoeler\Component\SportsManagement\Site\Model\EditmatchModel::class;
    $match = (new \ReflectionClass($modelClass))->newInstanceWithoutConstructor();
    $injected = new \ReflectionProperty($match, 'injectedDatabase');
    $injected->setValue($match, $db);

    foreach ([
        ['id' => '31garbage', 'project_id' => 3],
        ['id' => 31, 'project_id' => '3garbage'],
        ['id' => 31, 'project_id' => '9999999999999999999999'],
        ['id' => 31, 'project_id' => 3, 'roster' => ['one']],
        ['id' => 31, 'project_id' => 3, 'roster' => ['1' => '5bad']],
        ['id' => 31, 'project_id' => 3, 'roster' => [1 => true]],
        ['id' => 31, 'project_id' => 3, 'rosterc' => ['31garbage']],
        ['id' => 31, 'project_id' => 3, 'roster' => '5'],
    ] as $invalid) {
        if ($match->updateRosterBillard($invalid) !== false
            || $db->queries !== 0 || $db->writes !== 0) {
            throw new \RuntimeException('Malformed billiards selection reached database.');
        }
    }

    if ($match->updateRosterBillard(['id' => 31, 'project_id' => 3, 'roster' => [1 => '7']]) !== false
        || $db->queries !== 1 || $db->writes !== 0
        || ($db->last->binds[':billardMatchId'] ?? null) !== 31
        || ($db->last->binds[':billardProjectId'] ?? null) !== 3) {
        throw new \RuntimeException('Billards roster was not scoped to match project.');
    }

    $db->validMatchProject = true;
    $db->failsPositions = true;
    if ($match->updateRosterBillard(['id' => 31, 'project_id' => 3, 'roster' => [1 => 7]]) !== false
        || $db->writes !== 0
        || end($app->messages)[0] !== 'JERROR_AN_ERROR_HAS_OCCURRED'
        || !str_contains(end(\Joomla\CMS\Log\Log::$entries), 'PRIVATE PROJECT SQL')) {
        throw new \RuntimeException('Project positions database failure leaked or inserted rows.');
    }

    $db->failsPositions = false;
    if ($match->updateRosterBillard([
        'id' => '0031', 'project_id' => '3',
        'roster' => [1 => '007', 2 => '0'],
    ]) !== true || $db->writes !== 1
        || $db->rows[0]->match_id !== 31
        || $db->rows[0]->teamplayer_id !== 7
        || $db->rows[0]->project_position_id !== 34
        || $db->rows[0]->ordering !== 1) {
        throw new \RuntimeException('Valid scoped billiards roster did not persist correctly.');
    }

    class CountryDbStub implements \Joomla\Database\DatabaseInterface {
        public int $queries = 0;
        public int $writes = 0;
        public int $starts = 0;
        public int $rollbacks = 0;
        public bool $failsRead = false;
        public bool $failsWrite = false;
        public int $step = 0;
        public function createQuery(): MatchQueryStub { return new MatchQueryStub(); }
        public function quoteName(string $name, ?string $alias = null): string {
            return $name . ($alias === null ? '' : ' AS ' . $alias);
        }
        public function setQuery(MatchQueryStub $query, int $offset = 0, int $limit = 0): self {
            ++$this->queries;
            return $this;
        }
        public function loadResult(): string {
            if ($this->failsRead) {
                throw new \RuntimeException('PRIVATE COUNTRY DB');
            }
            return 'DEU';
        }
        public function loadColumn(): array { return [7]; }
        public function transactionStart(): void { ++$this->starts; }
        public function transactionCommit(): void {}
        public function transactionRollback(): void { ++$this->rollbacks; }
        public function updateObject(string $table, object $row, string $key): void {
            if ($this->failsWrite) {
                throw new \RuntimeException('PRIVATE UPDATE QUERY');
            }
            ++$this->writes;
        }
    }
    $countryDb = new CountryDbStub();
    $base = \Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel::class;
    $base::$db = $countryDb;
    $adminClass = \Diddipoeler\Component\SportsManagement\Administrator\Model\TeamplayerModel::class;
    $admin = (new \ReflectionClass($adminClass))->newInstanceWithoutConstructor();
    $countryDb->failsRead = true;
    if ($admin->assignplayerscountry(1, 0, 7, 0, 12) !== false
        || $countryDb->starts !== 0
        || $countryDb->rollbacks !== 0
        || $admin->error !== 'Team-person countries could not be updated.'
        || !str_contains(end(\Joomla\CMS\Log\Log::$entries), 'PRIVATE COUNTRY DB')) {
        throw new \RuntimeException('Admin country query exception escaped.');
    }
    $countryDb->failsRead = false;
    $countryDb->failsWrite = true;
    if ($admin->assignplayerscountry(1, 0, 7, 0, 12) !== false
        || $countryDb->starts !== 1
        || $countryDb->rollbacks !== 1
        || !str_contains(end(\Joomla\CMS\Log\Log::$entries), 'PRIVATE UPDATE QUERY')) {
        throw new \RuntimeException('Admin write failure was not rolled back.');
    }
    $countryDb->failsWrite = false;
    if (!$admin->assignplayerscountry(1, 0, 7, 0, 12) || $countryDb->writes !== 1) {
        throw new \RuntimeException('Valid admin country assignment did not persist.');
    }

    $eventsClass = \Diddipoeler\Module\SportsManagementEventsRanking\Site\Helper\EventsRankingHelper::class;
    $events = new $eventsClass();
    $ids = new \ReflectionMethod($events, 'optionalId');
    foreach ([
        [12, 12], ['0012:league', 12], ['12:fußball', 12],
        ['12suffix', 0], ['12:bad:other', 0], ['1e3', 0],
        ['99999999999999999999999999', 0], [true, 0], [[], 0],
    ] as [$value, $expected]) {
        if ($ids->invoke(null, $value) !== $expected) {
            throw new \RuntimeException('Event ranking optional ID validation failed.');
        }
    }

    $empty = ['project' => null, 'eventtypes' => [], 'rankings' => []];
    $moduleResolver = \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::class;
    $originalCalls = $moduleResolver::$calls;
    foreach ([
        ['p' => []],
        ['p' => ['12garbage']],
        ['p' => [12], 'evid' => ['9999999999999999999999']],
        ['p' => [12], 'evid' => 'wrong'],
        ['p' => [12], 'division_id' => '9garbage'],
        ['p' => [12], 'mid' => ['9']],
        ['p' => [12], 'tid' => '999999999999999999999999'],
    ] as $params) {
        if ($events->getData(new \Joomla\Registry\Registry($params), $db) !== $empty
            || $moduleResolver::$calls !== $originalCalls) {
            throw new \RuntimeException('Invalid event rankings filter widened data access.');
        }
    }

    $moduleResolver::$offline = true;
    if ($events->getData(new \Joomla\Registry\Registry(['p' => [12]]), $db) !== $empty
        || !str_contains(end(\Joomla\CMS\Log\Log::$entries), 'PRIVATE SQL CREDENTIALS')) {
        throw new \RuntimeException('Unavailable event-ranking database leaked to frontend.');
    }

    echo "Joomla 5/6 billards roster, country and events filter guard OK\n";
}
