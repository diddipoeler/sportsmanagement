<?php
/**
 * Joomla 5/6 site match, admin roster and playground-ticker input regressions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Model {
    class AdminModel {
        public ?string $error = null;
        protected function getDatabase(): mixed { return null; }
        public function setError(string $error): void { $this->error = $error; }
    }
}
namespace Joomla\CMS\Language {
    class Text { public static function _(string $text): string { return $text; } }
}
namespace Joomla\CMS\Log {
    class Log {
        public const ERROR = 'error';
        public static array $messages = [];
        public static function add(string $text, mixed $level, string $category): void {
            self::$messages[] = $text;
        }
    }
}
namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}
namespace Joomla\Database {
    interface DatabaseInterface {}
    class ParameterType { public const INTEGER = 'integer'; }
}
namespace Joomla\Registry {
    class Registry {
        public function __construct(private array $values = []) {}
        public function get(string $name, mixed $default = null): mixed {
            return $this->values[$name] ?? $default;
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Table {
    class MatchTable {
        public static array $bound = [];
        public static int $writes = 0;
        public function __construct(mixed $db) {}
        public function bind(array $record): bool {
            self::$bound = $record;
            return true;
        }
        public function check(): bool { return true; }
        public function store(): bool { ++self::$writes; return true; }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel {}
}
namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementDatabaseResolver {
        public static int $calls = 0;
        public static function resolve(
            \Joomla\Database\DatabaseInterface $fallback,
            int $selector
        ): \Joomla\Database\DatabaseInterface {
            ++self::$calls;
            return $fallback;
        }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Model/EditmatchModel.php';
    require_once $root . '/admin/src/Model/TeamplayerModel.php';
    require_once $root . '/modules/mod_sportsmanagement_playground_ticker/src/Helper/PlaygroundTickerHelper.php';

    $modelClass = \Diddipoeler\Component\SportsManagement\Site\Model\EditmatchModel::class;
    $model = (new \ReflectionClass($modelClass))->newInstanceWithoutConstructor();
    $table = \Diddipoeler\Component\SportsManagement\Administrator\Table\MatchTable::class;

    foreach ([['id' => 0], ['id' => null], ['id' => true], ['id' => -2],
        ['id' => '12junk'], ['id' => '1e3'], ['id' => []],
        ['id' => '999999999999999999999999999999']] as $invalid) {
        if ($model->updItem($invalid) !== false || $table::$writes !== 0) {
            throw new \RuntimeException('Invalid frontend match ID reached Table::store.');
        }
    }

    $ok = $model->updItem([
        'id' => '0041', 'preview' => 'top-level',
        'request' => ['id' => 999, 'preview' => 'legacy', 'cancel' => 0],
        'jform' => ['id' => 888, 'preview' => '', 'summary' => 'text', 'show_report' => false],
    ]);

    if ($ok !== true || $table::$writes !== 1
        || ($table::$bound['id'] ?? null) !== 41
        || ($table::$bound['preview'] ?? null) !== ''
        || ($table::$bound['cancel'] ?? null) !== 0
        || ($table::$bound['show_report'] ?? null) !== false
        || ($table::$bound['summary'] ?? null) !== 'text'
        || isset($table::$bound['request'], $table::$bound['jform'])) {
        throw new \RuntimeException('Match ID or native jform fields were not preserved.');
    }

    // Historical request objects need to retain raw summary/preview.
    $request = new class {
        public function getArray(): array {
            return ['id' => 600, 'cancel' => 0];
        }
        public function get(string $field, mixed $default = null, string $filter = ''): string {
            if ($filter !== 'raw') {
                throw new \RuntimeException('Raw preview/summary getter not used.');
            }
            return $field === 'preview' ? '<strong>Preview</strong>' : '<p>Summary</p>';
        }
    };
    if (!$model->updItem(['id' => 52, 'request' => $request])
        || $table::$writes !== 2 || $table::$bound['id'] !== 52
        || $table::$bound['preview'] !== '<strong>Preview</strong>'
        || $table::$bound['summary'] !== '<p>Summary</p>') {
        throw new \RuntimeException('Historical editmatch request object compatibility changed.');
    }

    $adminClass = \Diddipoeler\Component\SportsManagement\Administrator\Model\TeamplayerModel::class;
    $admin = (new \ReflectionClass($adminClass))->newInstanceWithoutConstructor();
    $normalise = new \ReflectionMethod($admin, 'normaliseIds');
    $ids = $normalise->invoke($admin, ['7', '007', ' 8 ', 8, true, -2, 0,
        '9bad', '1e3', 3.5, ['2'], '99999999999999999999999', '009']);
    if ($ids !== [7, 8, 9]) {
        throw new \RuntimeException('Admin team-player selection accepted malformed IDs.');
    }
    $idParser = new \ReflectionMethod($admin, 'positiveId');
    foreach ([
        [12, 12], ['0012', 12], ['0', 0], ['12:alias', 0],
        ['12garbage', 0], [false, 0], [-2, 0], [4.5, 0],
        ['999999999999999999999999', 0],
    ] as [$candidate, $expected]) {
        if ($idParser->invoke(null, $candidate) !== $expected) {
            throw new \RuntimeException('Admin roster relation ID parser error.');
        }
    }

    $tickerClass = \Diddipoeler\Module\SportsManagementPlaygroundTicker\Site\Helper\PlaygroundTickerHelper::class;
    $ticker = new $tickerClass();
    $projectId = new \ReflectionMethod($ticker, 'projectId');
    $unfiltered = new \ReflectionMethod($ticker, 'isUnfilteredProject');
    foreach ([
        ['12', 12], ['00012:arena', 12], ['12:fußball', 12],
        ['12bad', 0], ['1e3', 0], [false, 0], [-1, 0],
        [1.5, 0], ['99999999999999999999999', 0],
    ] as [$value, $expected]) {
        if ($projectId->invoke(null, $value) !== $expected) {
            throw new \RuntimeException('Playground ticker accepted malformed project ID.');
        }
    }
    foreach ([null, '', 0, '0', '000', ' 000 '] as $all) {
        if (!$unfiltered->invoke(null, $all)) {
            throw new \RuntimeException('Explicit unfiltered playground selection was lost.');
        }
    }
    foreach ([false, -1, 1.5, [], 'bad', '12suffix'] as $bad) {
        if ($unfiltered->invoke(null, $bad)) {
            throw new \RuntimeException('Malformed playground project became unfiltered.');
        }
    }

    $db = new class implements \Joomla\Database\DatabaseInterface {
        public int $queries = 0;
        public ?object $lastQuery = null;
        public function createQuery(): object {
            return new class {
                public array $binds = [];
                public array $joins = [];
                public function select(array $columns): self { return $this; }
                public function from(string $table): self { return $this; }
                public function join(string $type, string $clause): self {
                    $this->joins[] = [$type, $clause];
                    return $this;
                }
                public function where(string $clause): self { return $this; }
                public function bind(string $name, mixed $value, mixed $type): self {
                    $this->binds[$name] = $value;
                    return $this;
                }
            };
        }
        public function quoteName(string $name, ?string $alias = null): string {
            return $name . ($alias === null ? '' : ' AS ' . $alias);
        }
        public function setQuery(object $query): void {
            ++$this->queries;
            $this->lastQuery = $query;
        }
        public function loadObjectList(): array { return []; }
    };
    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        public function getInput(): object {
            return new class { public function getInt(string $name, int $default = 0): int {
                return $default;
            }};
        }
    };
    $resolver = \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::class;

    foreach (['wrong', '12suffix', '-1', '1e3', '99999999999999999999999999'] as $invalid) {
        $out = $ticker->getData(new \Joomla\Registry\Registry(['p' => $invalid]), $app, $db);
        if ($out !== [] || $db->queries !== 0 || $resolver::$calls !== 0) {
            throw new \RuntimeException('Invalid ticker project selected all playgrounds.');
        }
    }
    $ticker->getData(new \Joomla\Registry\Registry(['p' => '0012:club']), $app, $db);
    if ($db->queries !== 1 || $resolver::$calls !== 1
        || ($db->lastQuery->binds[':projectId'] ?? null) !== 12) {
        throw new \RuntimeException('Valid project slug not bound to ticker query.');
    }

    echo "Joomla 5/6 match editor, roster IDs and ticker selectors OK\n";
}
