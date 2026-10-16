<?php
/**
 * Joomla 5/6 tournament-tree controller ACL, scoped divisions and depth bounds.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    class Text {
        public static function _(string $key): string { return $key; }
    }
}
namespace Joomla\CMS\Router {
    class Route {
        public static function _(string $url, bool $x = true): string { return $url; }
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
namespace Joomla\Database {
    class ParameterType { public const INTEGER = 'integer'; }
}
namespace Joomla\CMS\MVC\Factory {
    interface MVCFactoryInterface {}
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Table {
    class TreetoTable {
        public function __construct(mixed $db) {}
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel {
        public string $error = '';
        public function setError(string $message): void { $this->error = $message; }
        public function getError(): string { return $this->error; }
        protected function getDatabase(): object { return $GLOBALS['treeGuardDb']; }
        protected function administratorApplication(): object { return $GLOBALS['treeGuardApp']; }
    }
    class SportsManagementListModel {
        public string $error = '';
        public function __construct($config = [], ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null) {}
        public function setError(string $message): void { $this->error = $message; }
        public function getError(): string { return $this->error; }
        protected function getDatabase(): object { return $GLOBALS['treeGuardDb']; }
        protected function administratorApplication(): object { return $GLOBALS['treeGuardApp']; }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementFormController {
        protected object $app;
        public mixed $model = null;
        public bool $tokenValid = true;
        public int $modelLoads = 0;
        public ?array $redirect = null;
        public function setTestApp(object $app): void { $this->app = $app; }
        protected function checkToken(): void {
            if (!$this->tokenValid) {
                throw new \RuntimeException('CSRF failed.', 403);
            }
        }
        public function getModel($name = 'Treeto', $prefix = 'Administrator', $config = []) {
            ++$this->modelLoads;
            return $this->model;
        }
        protected function setRedirect(string $url, string $message = '', string $type = 'message'): void {
            $this->redirect = ['url' => $url, 'message' => $message, 'type' => $type];
        }
    }
    class SportsManagementAdminController extends SportsManagementFormController {}
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    foreach ([
        'admin/src/Controller/TreetoController.php',
        'admin/src/Controller/TreetonodeController.php',
        'admin/src/Controller/TreetosController.php',
        'admin/src/Model/TreetoModel.php',
        'admin/src/Model/TreetonodesModel.php',
        'admin/src/Model/TreetosModel.php',
    ] as $file) {
        require_once $root . '/' . $file;
    }

    class TreeGuardInput {
        public object $post;
        public array $values = [];
        public function __construct() {
            $this->post = new class($this) {
                public function __construct(private TreeGuardInput $input) {}
                public function get(string $key, mixed $default = null, string $filter = ''): mixed {
                    return $this->input->values[$key] ?? $default;
                }
                public function getInt(string $key, int $default = 0): int {
                    return (int) ($this->input->values[$key] ?? $default);
                }
                public function getArray(): array { return $this->input->values; }
            };
        }
        public function getInt(string $key, int $default = 0): int {
            return (int) ($this->values[$key] ?? $default);
        }
    }
    class TreeGuardApp {
        public TreeGuardInput $input;
        public object $identity;
        public function __construct() {
            $this->input = new TreeGuardInput();
            $this->identity = new class {
                public array $allowed = [];
                public function authorise(string $action, string $asset): bool {
                    return $asset === 'com_sportsmanagement'
                        && in_array($action, $this->allowed, true);
                }
            };
        }
        public function getInput(): TreeGuardInput { return $this->input; }
        public function getIdentity(): object { return $this->identity; }
    }
    class TreeGuardQuery {
        public string $from = '';
        public array $in = [];
        public array $bindings = [];
        public function select(mixed $columns): self { return $this; }
        public function from(string $table): self { $this->from = $table; return $this; }
        public function where(string $condition): self { return $this; }
        public function whereIn(string $field, array $ids, string $type): self {
            $this->in = $ids;
            return $this;
        }
        public function bind(string $placeholder, mixed $value, string $type): self {
            $this->bindings[$placeholder] = $value;
            return $this;
        }
    }
    class TreeGuardDb {
        public int $transactions = 0;
        public int $commits = 0;
        public int $rollbacks = 0;
        public int $updates = 0;
        public int $nodeInserts = 0;
        public array $availableTrees = [11, 12];
        public array $availableDivisions = [3, 4];
        public int $projectId = 9;
        public bool $failUpdate = false;
        public ?TreeGuardQuery $active = null;
        public array $limits = [];
        public function createQuery(): TreeGuardQuery { return new TreeGuardQuery(); }
        public function quoteName(string $name): string { return $name; }
        public function setQuery(TreeGuardQuery $query, int $offset = 0, int $limit = 0): self {
            $this->active = $query;
            $this->limits[] = $limit;
            return $this;
        }
        public function loadColumn(): array {
            if ($this->active === null) { throw new \RuntimeException('No database query.'); }
            $tree = str_contains($this->active->from, 'sportsmanagement_treeto');
            if ($tree) {
                $id = $this->active->bindings[':treeProjectId'] ?? 0;
                $eligible = $this->availableTrees;
            } else {
                $id = $this->active->bindings[':divisionProjectId'] ?? 0;
                $eligible = $this->availableDivisions;
            }
            return $id === $this->projectId
                ? array_values(array_intersect($this->active->in, $eligible))
                : [];
        }
        public function transactionStart(): void { ++$this->transactions; }
        public function transactionCommit(): void { ++$this->commits; }
        public function transactionRollback(): void { ++$this->rollbacks; }
        public function updateObject(string $table, object $row, string $primaryKey): bool {
            if ($this->failUpdate) { return false; }
            ++$this->updates;
            return true;
        }
        public function insertObject(string $table, object $row): bool {
            ++$this->nodeInserts;
            return true;
        }
    }
    $GLOBALS['treeGuardApp'] = new TreeGuardApp();
    $GLOBALS['treeGuardDb'] = new TreeGuardDb();
    $app = $GLOBALS['treeGuardApp'];
    $db = $GLOBALS['treeGuardDb'];

    $treeController = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\TreetoController();
    $nodeController = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\TreetonodeController();
    $listController = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\TreetosController();
    foreach ([$treeController, $nodeController, $listController] as $controller) {
        $controller->setTestApp($app);
        $controller->model = new class {
            public int $calls = 0;
            public function storeshort(array $ids, array $data): bool { ++$this->calls; return true; }
            public function setGenerateNode(): bool { ++$this->calls; return true; }
            public function delete(array $ids): bool { ++$this->calls; return true; }
            public function storeshortleaf(array $ids, array $data): bool { ++$this->calls; return true; }
            public function storefinishleaf(array $data): bool { ++$this->calls; return true; }
            public function setRemoveNode(array $data): bool { ++$this->calls; return true; }
            public function save(array $data): bool { ++$this->calls; return true; }
            public function getError(): string { return ''; }
        };
    }
    $app->input->values = [
        'cid' => ['11', '12'],
        'project_id' => '9',
        'pid' => '9',
        'id' => '11',
        'treeto_id' => '11',
        'tree_i' => '4',
    ];

    $actions = [
        [$treeController, 'saveshort'],
        [$treeController, 'generatenode'],
        [$treeController, 'remove'],
        [$nodeController, 'saveshort'],
        [$nodeController, 'saveallleaf'],
        [$nodeController, 'savefinishleaf'],
        [$nodeController, 'removenode'],
        [$listController, 'save'],
    ];
    foreach ($actions as [$controller, $method]) {
        try {
            $controller->{$method}();
            throw new \RuntimeException($method . ' accepted unauthorized tournament-tree write.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 403) { throw $e; }
        }
    }
    foreach ([$treeController, $nodeController, $listController] as $controller) {
        if ($controller->modelLoads !== 0 || $controller->model->calls !== 0) {
            throw new \RuntimeException('Unauthorized tournament-tree action loaded its writer.');
        }
    }

    $app->identity->allowed = ['core.edit', 'core.create', 'core.delete'];
    $treeController->tokenValid = false;
    foreach ([$treeController, $nodeController, $listController] as $controller) {
        $controller->tokenValid = false;
    }
    foreach ($actions as [$controller, $method]) {
        try {
            $controller->{$method}();
            throw new \RuntimeException($method . ' accepted a missing CSRF token.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 403) { throw $e; }
        }
    }
    foreach ([$treeController, $nodeController, $listController] as $controller) {
        $controller->tokenValid = true;
    }

    foreach ($actions as [$controller, $method]) {
        $controller->{$method}();
    }
    if ($treeController->model->calls !== 3 || $nodeController->model->calls !== 4
        || $listController->model->calls !== 1) {
        throw new \RuntimeException('Allowed tournament-tree controller actions regressed.');
    }

    // Reject tampered IDs before calling a persistence model.
    foreach ([
        ['cid' => ['11junk']],
        ['cid' => array_fill(0, 501, '11')],
        ['cid' => [[11]]],
        ['cid' => ['9999999999999999999999']],
    ] as $invalid) {
        $app->input->values = array_replace($app->input->values, $invalid);
        foreach ([[$treeController, 'saveshort'], [$nodeController, 'saveshort']] as [$controller, $method]) {
            $before = $controller->model->calls;
            try {
                $controller->{$method}();
                throw new \RuntimeException('Invalid tournament-tree selection accepted.');
            } catch (\UnexpectedValueException $e) {
                if ($e->getCode() !== 400) { throw $e; }
            }
            if ($controller->model->calls !== $before) {
                throw new \RuntimeException('Invalid selection reached a write model.');
            }
        }
    }

    $app->input->values = [
        'id' => '11', 'pid' => '9', 'project_id' => '9',
        'jform' => ['tree_i' => 8], 'cid' => [11],
    ];
    $treeModel = new \Diddipoeler\Component\SportsManagement\Administrator\Model\TreetoModel();
    $start = $db->transactions;
    foreach (['8', '10000000', '3junk', -1, [2]] as $depth) {
        $app->input->values['jform']['tree_i'] = $depth;
        if ($treeModel->setGenerateNode() !== false || $db->transactions !== $start) {
            throw new \RuntimeException('Unbounded tournament tree generated database rows.');
        }
    }
    $app->identity->allowed = [];
    $app->input->values['jform']['tree_i'] = '3';
    try {
        $treeModel->setGenerateNode();
        throw new \RuntimeException('Tree model bypassed authorization.');
    } catch (\RuntimeException $e) {
        if ($e->getCode() !== 403) { throw $e; }
    }
    if ($db->transactions !== $start) {
        throw new \RuntimeException('Unauthorized node generation started a transaction.');
    }
    $app->identity->allowed = ['core.edit'];
    $app->input->values['jform']['tree_i'] = '7';
    if (!$treeModel->setGenerateNode() || $db->nodeInserts !== 255
        || $db->transactions !== $start + 1 || $db->commits !== 1) {
        throw new \RuntimeException('Allowed bounded tree generation failed.');
    }

    $nodeModel = new \Diddipoeler\Component\SportsManagement\Administrator\Model\TreetonodesModel();
    $before = $db->transactions;
    if ($nodeModel->storeshortleaf([11], ['treeto_id' => 11, 'tree_i' => 12]) !== false
        || $db->transactions !== $before) {
        throw new \RuntimeException('Leaf propagation accepts depth above seven.');
    }

    $listModel = new \Diddipoeler\Component\SportsManagement\Administrator\Model\TreetosModel();
    $payload = ['project_id' => '9', 'division_id11' => '3', 'division_id12' => '4'];
    if (!$listModel->storeshort([11, 12], $payload)
        || $db->updates !== 3 || $db->commits !== 2 || !in_array(500, $db->limits, true)) {
        throw new \RuntimeException('Valid tournament-tree division assignment failed.');
    }
    foreach ([
        [[11, 99], $payload],
        [[11], ['project_id' => '8', 'division_id11' => '3']],
        [[11], ['project_id' => '9', 'division_id11' => '999']],
        [['11junk'], $payload],
        [[true], $payload],
        [[11], ['project_id' => '9junk', 'division_id11' => '3']],
        [[11], ['project_id' => '9', 'division_id11' => '4junk']],
        [array_fill(0, 501, 11), $payload],
    ] as [$ids, $data]) {
        $before = [$db->transactions, $db->updates];
        if ($listModel->storeshort($ids, $data) !== false
            || $before !== [$db->transactions, $db->updates]) {
            throw new \RuntimeException('Invalid tree/division/project selection caused writes.');
        }
    }
    $app->identity->allowed = [];
    try {
        $listModel->storeshort([11], $payload);
        throw new \RuntimeException('Direct tree division model bypassed authorization.');
    } catch (\RuntimeException $e) {
        if ($e->getCode() !== 403) { throw $e; }
    }
    $app->identity->allowed = ['core.edit'];
    $db->failUpdate = true;
    if ($listModel->storeshort([11], $payload) !== false || $db->rollbacks !== 1) {
        throw new \RuntimeException('Failed tournament division update did not roll back.');
    }

    echo "Joomla 5/6 tournament tree ACL, scoped divisions, node depths OK\n";
}
