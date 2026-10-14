<?php
/**
 * Joomla 5/6 contract for club merge IDs, roster country and UEFA errors.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Controller {
    class FormController {}
}
namespace Joomla\CMS\Language {
    class Text {
        public static function _(string $text): string { return $text; }
    }
}
namespace Joomla\CMS\Log {
    class Log {
        public const ERROR = 'error';
        public static array $entries = [];
        public static function add(string $text, mixed $level, string $category): void {
            self::$entries[] = $text;
        }
    }
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
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel {
        public static mixed $db = null;
        public string $error = '';
        public function setError(string $message): void { $this->error = $message; }
        protected function getDatabase(): mixed { return self::$db; }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementDatabaseResolver {
        public static int $calls = 0;
        public static bool $fail = false;
        public static function resolve(
            \Joomla\Database\DatabaseInterface $database, int $selector
        ): \Joomla\Database\DatabaseInterface {
            ++self::$calls;
            if (self::$fail) {
                throw new \RuntimeException('PRIVATE EXTERNAL DB CONNECTION STRING');
            }
            return $database;
        }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Controller/EditclubController.php';
    require_once $root . '/admin/src/Model/TeamplayerModel.php';
    require_once $root . '/modules/mod_sportsmanagement_uefawertung/src/Helper/UefaWertungHelper.php';

    $club = new \Diddipoeler\Component\SportsManagement\Site\Controller\EditclubController();
    $merge = new \ReflectionMethod($club, 'normalizeMergeTeams');

    foreach ([
        [[7, '007', ' 8 ', '9suffix', '1e3', true, false, -2, 2.5,
            ['10'], '9999999999999999999999999999', 8], '7,8'],
        [[], ''],
        [null, ''],
        [['000', '0', ' 0 ', '12'], '12'],
        [['12:alias', '13;14', '11junk', '00015'], '15'],
        [['12', '13', '12', '0014'], '12,13,14'],
    ] as [$selected, $expected]) {
        if ($merge->invoke($club, $selected) !== $expected) {
            throw new \RuntimeException('Club merge teams accepted an invalid selector.');
        }
    }

    $database = new class implements \Joomla\Database\DatabaseInterface {
        public int $queries = 0;
        public int $writes = 0;
        public int $starts = 0;
        public int $commits = 0;
        public ?object $lastQuery = null;
        public bool $failQuery = false;
        public array $updatedRows = [];
        public string $country = 'DEU';
        public function createQuery(): object {
            return new class {
                public array $binds = [];
                public function select(string|array $columns): self { return $this; }
                public function from(string $table): self { return $this; }
                public function join(string $type, string $condition): self { return $this; }
                public function where(string $condition): self { return $this; }
                public function whereIn(string $column, array $values, mixed $type): self { return $this; }
                public function bind(string $key, mixed $value, mixed $type): self {
                    $this->binds[$key] = $value;
                    return $this;
                }
                public function group(string $column): self { return $this; }
                public function order(string|array $order): self { return $this; }
            };
        }
        public function quoteName(string $name, ?string $alias = null): string {
            return $name . ($alias === null ? '' : ' AS ' . $alias);
        }
        public function setQuery(object $query, int $offset = 0, int $limit = 0): self {
            ++$this->queries;
            $this->lastQuery = $query;
            return $this;
        }
        public function loadResult(): string {
            if ($this->failQuery) {
                throw new \RuntimeException('PRIVATE DB PASSWORD');
            }
            return $this->country;
        }
        public function loadColumn(): array { return ['7', '007', '8suffix', 0, true]; }
        public function loadObjectList(): array { return []; }
        public function transactionStart(): void { ++$this->starts; }
        public function transactionCommit(): void { ++$this->commits; }
        public function transactionRollback(): void {}
        public function updateObject(string $table, object $row, string $column): void {
            ++$this->writes;
            $this->updatedRows[] = $row;
        }
    };

    $adminName = \Diddipoeler\Component\SportsManagement\Administrator\Model\TeamplayerModel::class;
    $admin = (new \ReflectionClass($adminName))->newInstanceWithoutConstructor();
    $base = \Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel::class;
    $base::$db = $database;
    foreach ([
        [1, 0, '5garbage', 0, 9],
        [1, 0, '999999999999999999999999', 0, 9],
        [1, 0, 5, 0, '9garbage'],
        [1, 0, 5, 0, true],
        ['1junk', 0, 5, 0, 9],
        [4, 0, 5, 0, 9],
        [0, 0, 5, 0, 9],
    ] as $params) {
        if ($admin->assignplayerscountry(...$params) !== false
            || $database->queries !== 0
            || $database->writes !== 0) {
            throw new \RuntimeException('Invalid country assignment queried or updated persons.');
        }
    }
    if (!$admin->assignplayerscountry(1, 0, '005', 0, '009')
        || $database->queries !== 2
        || $database->writes !== 1
        || $database->starts !== 1
        || $database->commits !== 1
        || $database->updatedRows[0]->id !== 7
        || $database->updatedRows[0]->country !== 'DEU') {
        throw new \RuntimeException('Valid country assignment or duplicate person filtering failed.');
    }

    $moduleName = \Diddipoeler\Module\SportsManagementUefaWertung\Site\Helper\UefaWertungHelper::class;
    $uefa = new $moduleName();
    $season = new \ReflectionMethod($uefa, 'positiveSeasonId');
    foreach ([
        [12, 12], ['0012', 12], ['012:fußball', 12],
        [true, 0], [0, 0], [-9, 0], [1.5, 0], [[], 0],
        ['12junk', 0], ['1e3', 0], ['12:bad:other', 0],
        ['999999999999999999999999999', 0]
    ] as [$value, $expected]) {
        if ($season->invoke(null, $value) !== $expected) {
            throw new \RuntimeException('UEFA season selection accepted malformed ID.');
        }
    }

    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        public array $messages = [];
        public function enqueueMessage(string $message, string $type = 'message'): void {
            $this->messages[] = $message;
        }
    };
    $resolver = \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::class;
    $empty = ['seasons' => [], 'rankings' => []];

    foreach (['12garbage', '1e3', '99999999999999999999999', '-12'] as $invalid) {
        $before = $resolver::$calls;
        if ($uefa->getData(new \Joomla\Registry\Registry(['s' => $invalid]), $app, $database) !== $empty
            || $before !== $resolver::$calls) {
            throw new \RuntimeException('Invalid UEFA season reached database.');
        }
    }
    $resolver::$fail = true;
    if ($uefa->getData(new \Joomla\Registry\Registry(['s' => '012:season']), $app, $database) !== $empty
        || end($app->messages) !== 'SportsManagement UEFA rankings could not be loaded.'
        || !str_contains(end(\Joomla\CMS\Log\Log::$entries), 'PRIVATE EXTERNAL DB')) {
        throw new \RuntimeException('UEFA resolver failure leaked private configuration.');
    }
    $resolver::$fail = false;
    $database->failQuery = true;
    if ($uefa->getData(new \Joomla\Registry\Registry(['s' => 12]), $app, $database) !== $empty
        || end($app->messages) !== 'SportsManagement UEFA rankings could not be loaded.'
        || !str_contains(end(\Joomla\CMS\Log\Log::$entries), 'PRIVATE DB PASSWORD')) {
        throw new \RuntimeException('UEFA SQL error exposed database details.');
    }

    echo "Joomla 5/6 club merge, roster country and UEFA safeguards OK\n";
}
