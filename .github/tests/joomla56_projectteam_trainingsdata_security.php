<?php
/**
 * Joomla 5/6 regression test for site project-team, admin and module inputs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Controller {
    class FormController {
        protected mixed $app = null;
        public static bool $validToken = true;
        protected function checkToken(): void {
            if (!self::$validToken) { throw new \RuntimeException('Invalid CSRF token', 403); }
        }
        public function injectApplication(object $app): void { $this->app = $app; }
    }
}
namespace Joomla\CMS\MVC\Model {
    class AdminModel {
        public static mixed $database = null;
        public ?string $error = null;
        protected function getDatabase(): mixed { return self::$database; }
        public function setError(string $message): void { $this->error = $message; }
    }
}
namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}
namespace Joomla\CMS\Language {
    class Text { public static function _(string $key): string { return $key; } }
}
namespace Joomla\CMS\Log {
    class Log {
        public const ERROR = 'error';
        public static array $messages = [];
        public static function add(string $message, mixed $level, string $category): void {
            self::$messages[] = $message;
        }
    }
}
namespace Joomla\Database {
    interface DatabaseInterface {}
    class ParameterType { public const INTEGER = 'integer'; }
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
    class SportsManagementListModel {}
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Table {
    class ProjectteamTable {
        public static array $bound = [];
        public static int $writes = 0;
        public int $id = 0;
        public int $team_id = 0;
        public int $project_id = 0;
        public function __construct(mixed $db) {}
        public function load(int $id): bool {
            if ($id !== 41) { return false; }
            $this->id = 41; $this->project_id = 7; $this->team_id = 13;
            return true;
        }
        public function bind(array $data): bool { self::$bound = $data; return true; }
        public function check(): bool { return true; }
        public function store(): bool { ++self::$writes; return true; }
        public function getError(): string { return ''; }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementDatabaseResolver {
        public static function resolve(
            \Joomla\Database\DatabaseInterface $database,
            int $selector
        ): \Joomla\Database\DatabaseInterface {
            return $database;
        }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Controller/EditprojectteamController.php';
    require_once $root . '/site/src/Model/EditprojectteamModel.php';
    require_once $root . '/admin/src/Model/TeamplayersModel.php';
    require_once $root . '/modules/mod_sportsmanagement_trainingsdata/src/Helper/TrainingsDataHelper.php';

    $identity = new class {
        public bool $allowed = false;
        public function authorise(string $action, string $asset): bool {
            return $action === 'core.edit' && $asset === 'com_sportsmanagement' && $this->allowed;
        }
    };
    $controller = new \Diddipoeler\Component\SportsManagement\Site\Controller\EditprojectteamController();
    $controller->injectApplication(new class($identity) {
        public function __construct(private object $identity) {}
        public function getIdentity(): object { return $this->identity; }
    });

    foreach ([false, true] as $tokenValid) {
        \Joomla\CMS\MVC\Controller\FormController::$validToken = $tokenValid;
        try {
            $controller->save();
            throw new \RuntimeException('Project-team write bypassed ACL or CSRF.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 403) { throw $e; }
        }
    }
    $identity->allowed = true;
    (new \ReflectionMethod($controller, 'assertEditorPermission'))->invoke($controller);

    $db = new class implements \Joomla\Database\DatabaseInterface {
        public int $transactions = 0;
        public int $commits = 0;
        public array $updates = [];
        public function transactionStart(): void { ++$this->transactions; }
        public function transactionCommit(): void { ++$this->commits; }
        public function transactionRollback(): void {}
        public function updateObject(string $table, object $record, string $key): void {
            $this->updates[] = [$table, $record, $key];
        }
    };
    \Joomla\CMS\MVC\Model\AdminModel::$database = $db;
    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\EditprojectteamModel();

    foreach ([
        ['id' => '41junk'],
        ['id' => '9999999999999999999999999'],
        ['id' => 41, 'ptid' => 42],
        ['id' => 41, 'p' => 8],
        ['id' => 0]
    ] as $bad) {
        if ($model->updItem($bad) || $db->transactions !== 0) {
            throw new \RuntimeException('Invalid project-team relation reached database.');
        }
    }
    if (!$model->updItem([
        'id' => '41', 'ptid' => '41', 'p' => '7',
        'team_id' => 999, 'project_id' => 999, 'published' => -2,
        'request' => ['team_id' => 20],
        'jform' => ['team_id' => 21, 'id' => 22, 'picture' => '', 'start_points' => '3']
    ])) {
        throw new \RuntimeException('Native project-team jform saving failed.');
    }
    $tableClass = \Diddipoeler\Component\SportsManagement\Administrator\Table\ProjectteamTable::class;
    $bound = $tableClass::$bound;
    if ($tableClass::$writes !== 1 || $db->transactions !== 1 || $db->commits !== 1
        || $bound['id'] !== 41 || $bound['start_points'] !== '3' || $bound['picture'] !== ''
        || isset($bound['team_id']) || isset($bound['project_id']) || isset($bound['published'])
        || count($db->updates) !== 1 || $db->updates[0][0] !== '#__sportsmanagement_season_team_id'
        || $db->updates[0][1]->id !== 13) {
        throw new \RuntimeException('Project-team write changed unrelated relations or immutable IDs.');
    }

    $adminClass = \Diddipoeler\Component\SportsManagement\Administrator\Model\TeamplayersModel::class;
    $admin = (new \ReflectionClass($adminClass))->newInstanceWithoutConstructor();
    $ids = (new \ReflectionMethod($admin, 'normaliseIds'))->invoke($admin, [
        '7', '007', '17junk', -1, 0, true, ['12'],
        '9999999999999999999999999', '1e3', ' 18 ', 18, '0019'
    ]);
    if ($ids !== [7, 18, 19]) {
        throw new \RuntimeException('Admin relation IDs incorrectly accepted: ' . json_encode($ids));
    }

    $module = new \Diddipoeler\Module\SportsManagementTrainingsData\Site\Helper\TrainingsDataHelper();
    $trainingDb = new class implements \Joomla\Database\DatabaseInterface {
        public int $queries = 0;
        public bool $fail = false;
        public ?object $query = null;
        public function createQuery(): object {
            return new class {
                public array $binds = [];
                public function select(array $value): self { return $this; }
                public function from(string $value): self { return $this; }
                public function where(string $value): self { return $this; }
                public function order(array $value): self { return $this; }
                public function bind(string $key, mixed $value, mixed $type): self {
                    $this->binds[$key] = $value; return $this;
                }
            };
        }
        public function quoteName(string $name): string { return $name; }
        public function setQuery(object $query): void { $this->query = $query; ++$this->queries; }
        public function loadObjectList(): array {
            if ($this->fail) { throw new \RuntimeException('private SQL credentials'); }
            return [(object) ['place' => 'Gym']];
        }
    };

    foreach ([0, -1, false, 1.5, [], '12junk', '12:invalid:alias',
        '9999999999999999999999999', '1e3'] as $invalid) {
        if ($module->getData(new \Joomla\Registry\Registry(['teams' => $invalid]), $trainingDb) !== []) {
            throw new \RuntimeException('Invalid module team ID accepted.');
        }
    }
    if ($trainingDb->queries !== 0) {
        throw new \RuntimeException('Invalid module team selection queried SQL.');
    }
    $rows = $module->getData(new \Joomla\Registry\Registry(['teams' => '0012:fußball']), $trainingDb);
    if (count($rows) !== 1 || $trainingDb->queries !== 1
        || $trainingDb->query->binds[':teamId'] !== 12) {
        throw new \RuntimeException('Valid module ID alias not bound as integer.');
    }
    $trainingDb->fail = true;
    if ($module->getData(new \Joomla\Registry\Registry(['teams' => '12']), $trainingDb) !== []
        || count(\Joomla\CMS\Log\Log::$messages) !== 1) {
        throw new \RuntimeException('Private module DB error was not handled.');
    }

    echo "Joomla 5/6 project-team ACL, admin IDs and training module checks passed.\n";
}
