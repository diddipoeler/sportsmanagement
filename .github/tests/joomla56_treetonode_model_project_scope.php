<?php
/**
 * Joomla 5/6 model regression for scoped tournament-tree node mutations.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Factory {
    interface MVCFactoryInterface {}
}
namespace Joomla\Database {
    class ParameterType {
        public const INTEGER = 'integer';
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementListModel {
        public string $error = '';
        public function __construct($config = [], ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null) {}
        protected function administratorApplication(): object { return $GLOBALS['nodeGuardApp']; }
        protected function getDatabase(): object { return $GLOBALS['nodeGuardDb']; }
        public function setError(string $message): void { $this->error = $message; }
        public function getError(): string { return $this->error; }
    }
}
namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Model/TreetonodesModel.php';

    class NodeScopeQuery {
        public string $kind = 'select';
        public string $table = '';
        public array $conditions = [];
        public array $bindings = [];
        public array $ids = [];
        public function select(string|array $x): self { return $this; }
        public function from(string $table): self { $this->table = $table; return $this; }
        public function where(string $condition): self { $this->conditions[] = $condition; return $this; }
        public function whereIn(string $field, array $ids, mixed $type): self {
            $this->ids = $ids;
            return $this;
        }
        public function bind(string $key, mixed $value, mixed $type): self {
            $this->bindings[$key] = $value;
            return $this;
        }
        public function update(string $table): self {
            $this->kind = 'update';
            $this->table = $table;
            return $this;
        }
        public function set(string $assignment): self { return $this; }
        public function delete(string $table): self {
            $this->kind = 'delete';
            $this->table = $table;
            return $this;
        }
    }
    class NodeScopeDb {
        public ?NodeScopeQuery $query = null;
        public array $limits = [];
        public int $transactions = 0;
        public int $commits = 0;
        public int $rollbacks = 0;
        public int $updates = 0;
        public int $executions = 0;
        public int $inserts = 0;
        public int $treeId = 10;
        public int $projectId = 20;
        public int $depth = 3;
        public array $nodeIds = [101 => 1, 102 => 2];
        public array $teams = [301, 302];
        public bool $rejectUpdates = false;
        public function createQuery(): NodeScopeQuery { return new NodeScopeQuery(); }
        public function quoteName(string $name, ?string $alias = null): string { return $name; }
        public function setQuery(NodeScopeQuery $query, int $offset = 0, int $limit = 0): self {
            $this->query = $query;
            $this->limits[] = $limit;
            return $this;
        }
        public function loadObject(): ?object {
            $this->assertQuery();
            $bindings = $this->query->bindings;
            if ($this->query->table !== '#__sportsmanagement_treeto') {
                throw new \RuntimeException('Missing tree ownership lookup.');
            }
            return (($bindings[':nodeTreeId'] ?? 0) === $this->treeId
                && ($bindings[':nodeTreeProjectId'] ?? 0) === $this->projectId)
                ? (object) ['id' => $this->treeId, 'tree_i' => $this->depth]
                : null;
        }
        public function loadColumn(): array {
            $this->assertQuery();
            $q = $this->query;
            if ($q->table === '#__sportsmanagement_treeto_node') {
                if (array_key_exists(':removeTreeId', $q->bindings)) {
                    return ($q->bindings[':removeTreeId'] === $this->treeId)
                        ? array_keys($this->nodeIds) : [];
                }
                if (($q->bindings[':selectedTreeId'] ?? null) === $this->treeId) {
                    return array_values(array_intersect($q->ids, array_keys($this->nodeIds)));
                }
                return [];
            }
            if ($q->table === '#__sportsmanagement_project_team') {
                return ($q->bindings[':nodeTeamsProjectId'] ?? null) === $this->projectId
                    ? array_values(array_intersect($q->ids, $this->teams)) : [];
            }
            throw new \RuntimeException('Unexpected node data lookup ' . $q->table);
        }
        public function loadResult(): int {
            $this->assertQuery();
            $id = $this->query->bindings[':leafLookupNodeId'] ?? 0;
            return ($this->query->bindings[':leafLookupTreeId'] ?? null) === $this->treeId
                ? ($this->nodeIds[$id] ?? 0) : 0;
        }
        public function execute(): bool {
            $this->assertQuery();
            if (!in_array($this->query->kind, ['update', 'delete'], true)) {
                throw new \RuntimeException('Unexpected query execution.');
            }
            ++$this->executions;
            return true;
        }
        public function transactionStart(): void { ++$this->transactions; }
        public function transactionCommit(): void { ++$this->commits; }
        public function transactionRollback(): void { ++$this->rollbacks; }
        public function updateObject(string $table, object $row, string $primary): bool {
            if ($this->rejectUpdates) {
                return false;
            }
            ++$this->updates;
            return true;
        }
        public function insertObject(string $table, object $row): bool {
            ++$this->inserts;
            return true;
        }
        private function assertQuery(): void {
            if ($this->query === null) {
                throw new \RuntimeException('Missing database query.');
            }
        }
    }
    class NodeScopeApp {
        public object $identity;
        public function __construct() {
            $this->identity = new class {
                public bool $authorized = false;
                public function authorise(string $action, string $asset): bool {
                    return $this->authorized && $action === 'core.edit'
                        && $asset === 'com_sportsmanagement';
                }
            };
        }
        public function getIdentity(): object { return $this->identity; }
    }

    $GLOBALS['nodeGuardApp'] = new NodeScopeApp();
    $GLOBALS['nodeGuardDb'] = new NodeScopeDb();
    $app = $GLOBALS['nodeGuardApp'];
    $db = $GLOBALS['nodeGuardDb'];
    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\TreetonodesModel();
    $posted = ['treeto_id' => '10', 'project_id' => '20', 'team_id101' => '301'];

    $mutations = [
        static fn() => $model->storeshort([101], $posted),
        static fn() => $model->storeshortleaf([101], $posted + ['tree_i' => '3']),
        static fn() => $model->storefinishleaf($posted),
        static fn() => $model->setRemoveNode($posted),
    ];

    foreach ($mutations as $operation) {
        try {
            $operation();
            throw new \RuntimeException('Unauthorized tournament-node model write succeeded.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 403) {
                throw $e;
            }
        }
    }
    if ($db->transactions !== 0 || $db->updates !== 0 || $db->executions !== 0) {
        throw new \RuntimeException('Unauthorized node write reached persistence.');
    }

    $app->identity->authorized = true;

    // A node id, tree id and project id must all belong to the same bracket.
    foreach ([
        ['tree' => '11', 'project' => '20', 'cid' => [101], 'team' => '301'],
        ['tree' => '10', 'project' => '21', 'cid' => [101], 'team' => '301'],
        ['tree' => '10', 'project' => '20', 'cid' => [999], 'team' => '301'],
        ['tree' => '10', 'project' => '20', 'cid' => [101], 'team' => '999'],
        ['tree' => '10junk', 'project' => '20', 'cid' => [101], 'team' => '301'],
        ['tree' => '10', 'project' => '20junk', 'cid' => [101], 'team' => '301'],
        ['tree' => '10', 'project' => '20', 'cid' => ['101junk'], 'team' => '301'],
        ['tree' => '10', 'project' => '20', 'cid' => [true], 'team' => '301'],
        ['tree' => '10', 'project' => '20', 'cid' => [[101]], 'team' => '301'],
        ['tree' => '10', 'project' => '20', 'cid' => array_fill(0, 501, 101), 'team' => '301'],
    ] as $invalid) {
        $data = [
            'treeto_id' => $invalid['tree'],
            'project_id' => $invalid['project'],
            'team_id101' => $invalid['team'],
            'team_id999' => $invalid['team'],
        ];
        $before = [$db->transactions, $db->updates, $db->executions];
        if ($model->storeshort($invalid['cid'], $data) !== false
            || $before !== [$db->transactions, $db->updates, $db->executions]) {
            throw new \RuntimeException('Unscoped node/team assignment reached persistence.');
        }
    }
    foreach ([
        ['treeto_id' => '11', 'project_id' => '20'],
        ['treeto_id' => '10', 'project_id' => '21'],
        ['treeto_id' => '10', 'project_id' => '20junk'],
        ['treeto_id' => '10', 'project_id' => '20', 'tree_i' => '4'],
        ['treeto_id' => '10', 'project_id' => '20', 'tree_i' => '8'],
    ] as $invalid) {
        $data = $invalid + ['tree_i' => '3'];
        $before = [$db->transactions, $db->updates, $db->executions];
        if ($model->storeshortleaf([101], $data) !== false
            || $before !== [$db->transactions, $db->updates, $db->executions]) {
            throw new \RuntimeException('Foreign tree or invalid depth changed leaf state.');
        }
    }
    foreach ([999, '101junk', true] as $badId) {
        $before = $db->transactions;
        if ($model->storeshortleaf([$badId], $posted + ['tree_i' => '3']) !== false
            || $db->transactions !== $before) {
            throw new \RuntimeException('Invalid leaf node selected for mutation.');
        }
    }

    // Valid assignments can still be saved, cleared and made into leaves.
    if (!$model->storeshort([101, 101], $posted)
        || $db->updates !== 1 || $db->transactions !== 1 || $db->commits !== 1) {
        throw new \RuntimeException('Valid node/team save regressed.');
    }
    $clear = $posted;
    $clear['team_id101'] = 0;
    if (!$model->storeshort([101], $clear) || $db->updates !== 2) {
        throw new \RuntimeException('Clearing a node team regressed.');
    }
    if (!$model->storeshortleaf([101], $posted + ['tree_i' => '3'])
        || $db->commits !== 3 || $db->executions < 2) {
        throw new \RuntimeException('Scoped leaf propagation regressed.');
    }
    if (!$model->storefinishleaf($posted) || $db->updates !== 4) {
        throw new \RuntimeException('Valid tournament leaf completion failed.');
    }
    if ($model->storefinishleaf(['treeto_id' => '10', 'project_id' => '999']) !== false) {
        throw new \RuntimeException('Foreign-project leaf finalisation was accepted.');
    }

    // Legacy oversized trees must not produce unbounded SQL IN clauses.
    $oldNodes = $db->nodeIds;
    $db->nodeIds = array_fill_keys(range(1, 1001), 1);
    $before = [$db->transactions, $db->executions];
    if ($model->setRemoveNode($posted) !== false
        || $before !== [$db->transactions, $db->executions]
        || !in_array(1001, $db->limits, true)) {
        throw new \RuntimeException('Oversized tree deletion was accepted.');
    }
    $db->nodeIds = $oldNodes;

    if (!$model->setRemoveNode($posted) || $db->commits !== 4
        || $db->executions < 4) {
        throw new \RuntimeException('Valid scoped tree-node deletion failed.');
    }

    $db->rejectUpdates = true;
    $rollback = $db->rollbacks;
    if ($model->storeshort([101], $posted) !== false
        || $db->rollbacks !== $rollback + 1) {
        throw new \RuntimeException('Failed node team assignment was not rolled back.');
    }

    echo "Joomla 5/6 scoped tournament-tree node model mutations OK\n";
}
