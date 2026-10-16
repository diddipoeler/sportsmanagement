<?php
/**
 * Joomla 5/6 regression for tournament-tree match assignment scope and ACL.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    final class ParameterType {
        public const INTEGER = 'integer';
    }
}
namespace Joomla\CMS\Language {
    class Text {
        public static function _(string $name): string { return $name; }
    }
}
namespace Joomla\CMS\Router {
    class Route {
        public static function _(string $url, bool $x = true): string { return $url; }
    }
}
namespace Joomla\CMS\MVC\Factory {
    interface MVCFactoryInterface {}
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementListModel {
        public string $error = '';
        public function __construct($config = [], ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null) {}
        protected function administratorApplication(): object { return $GLOBALS['treeApp']; }
        public function getDatabase(): object { return $GLOBALS['treeDb']; }
        public function setError(string $message): void { $this->error = $message; }
        public function getError(): string { return $this->error; }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementFormController {
        protected object $app;
        public object $model;
        public bool $validToken = true;
        public int $models = 0;
        public ?array $redirect = null;
        public function setTestApp(object $app): void { $this->app = $app; }
        protected function checkToken(): void {
            if (!$this->validToken) {
                throw new \RuntimeException('Invalid CSRF token', 403);
            }
        }
        public function getModel($name = 'Treetomatchs', $prefix = 'Administrator', $config = []) {
            ++$this->models;
            return $this->model;
        }
        protected function setRedirect(string $url, string $message = '', string $type = 'message'): void {
            $this->redirect = ['url' => $url, 'message' => $message, 'type' => $type];
        }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Model/TreetomatchsModel.php';
    require_once $root . '/admin/src/Controller/TreetomatchController.php';

    class AssignmentQuery {
        public string $fromTable = '';
        public string $operation = 'select';
        public array $bindings = [];
        public array $in = [];
        public function select(mixed $selection): self { return $this; }
        public function from(string $table): self { $this->fromTable = $table; return $this; }
        public function join(string $kind, string $sql): self { return $this; }
        public function where(string $sql): self { return $this; }
        public function whereIn(string $field, array $ids, string $type): self {
            $this->in = $ids;
            return $this;
        }
        public function bind(string $key, mixed $value, string $type): self {
            $this->bindings[$key] = $value;
            return $this;
        }
        public function delete(string $table): self {
            $this->operation = 'delete';
            $this->fromTable = $table;
            return $this;
        }
        public function execute(): bool {
            ++$GLOBALS['treeDb']->deletes;
            return true;
        }
    }
    class AssignmentDb {
        public ?AssignmentQuery $active = null;
        public int $queries = 0;
        public int $starts = 0;
        public int $commits = 0;
        public int $rollbacks = 0;
        public int $deletes = 0;
        public int $inserts = 0;
        public int $node = 17;
        public int $tree = 31;
        public int $project = 7;
        public array $allowedMatches = [12, 13];
        public bool $failInserts = false;
        public array $lastLimits = [];
        public function createQuery(): AssignmentQuery {
            ++$this->queries;
            return new AssignmentQuery();
        }
        public function quoteName(string $name, ?string $alias = null): string { return $name; }
        public function setQuery(AssignmentQuery $query, int $offset = 0, int $limit = 0): self {
            $this->active = $query;
            $this->lastLimits[] = $limit;
            return $this;
        }
        public function loadResult(): int {
            $params = $this->active->bindings;
            if (!str_contains($this->active->fromTable, 'treeto_node')
                || !array_key_exists(':assignmentNodeId', $params)) {
                throw new \RuntimeException('Missing node/tree scoped lookup');
            }
            return $params[':assignmentNodeId'] === $this->node
                && $params[':assignmentTreeId'] === $this->tree
                && $params[':assignmentProjectId'] === $this->project
                ? $this->node : 0;
        }
        public function loadColumn(): array {
            $params = $this->active->bindings;
            if (!str_contains($this->active->fromTable, 'sportsmanagement_match')
                || ($params[':assignmentMatchProjectId'] ?? null) !== $this->project) {
                throw new \RuntimeException('Missing match-to-project scope lookup');
            }
            return array_values(array_intersect($this->active->in, $this->allowedMatches));
        }
        public function transactionStart(): void { ++$this->starts; }
        public function transactionCommit(): void { ++$this->commits; }
        public function transactionRollback(): void { ++$this->rollbacks; }
        public function insertObject(string $table, object $row): bool {
            if ($this->failInserts) { return false; }
            ++$this->inserts;
            return $table === '#__sportsmanagement_treeto_match';
        }
    }
    class TreeApp {
        public object $identity;
        public object $input;
        public array $posted = [];
        public function __construct() {
            $this->identity = new class {
                public bool $edit = false;
                public function authorise(string $action, string $asset): bool {
                    return $this->edit && $action === 'core.edit'
                        && $asset === 'com_sportsmanagement';
                }
            };
            $this->input = new class($this) {
                public object $post;
                public function __construct(private TreeApp $app) {
                    $this->post = new class($app) {
                        public function __construct(private TreeApp $app) {}
                        public function getArray(): array { return $this->app->posted; }
                        public function getInt(string $key, int $fallback = 0): int {
                            return (int) ($this->app->posted[$key] ?? $fallback);
                        }
                    };
                }
                public function getInt(string $key, int $fallback = 0): int { return 999; }
            };
        }
        public function getIdentity(): object { return $this->identity; }
        public function getInput(): object { return $this->input; }
    }
    $GLOBALS['treeDb'] = new AssignmentDb();
    $GLOBALS['treeApp'] = new TreeApp();
    $app = $GLOBALS['treeApp'];
    $db = $GLOBALS['treeDb'];
    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\TreetomatchsModel();
    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\TreetomatchController();
    $controller->setTestApp($app);
    $controller->model = $model;
    $valid = ['nid' => '17', 'tid' => '31', 'pid' => '7', 'node_matcheslist' => ['12', '13', '12']];
    $app->posted = $valid;

    foreach ([
        static fn() => $model->store($valid + ['id' => '17']),
        static fn() => $controller->save_matcheslist(),
    ] as $operation) {
        try {
            $operation();
            throw new \RuntimeException('Unprivileged tournament-tree edit accepted.');
        } catch (\RuntimeException $error) {
            if ($error->getCode() !== 403) { throw $error; }
        }
    }
    if ($db->queries !== 0 || $db->starts !== 0 || $controller->models !== 0) {
        throw new \RuntimeException('Unauthorized tournament-tree edit reached persistence.');
    }

    $app->identity->edit = true;
    $controller->validToken = false;
    try {
        $controller->save_matcheslist();
        throw new \RuntimeException('Invalid CSRF token accepted.');
    } catch (\RuntimeException $error) {
        if ($error->getCode() !== 403) { throw $error; }
    }

    $controller->validToken = true;
    $app->posted = $valid;
    $controller->save_matcheslist();
    if ($db->starts !== 1 || $db->commits !== 1 || $db->inserts !== 2
        || $db->deletes !== 1 || $controller->redirect['type'] !== 'message'
        || !in_array(500, $db->lastLimits, true)) {
        throw new \RuntimeException('Valid, bounded tournament-tree assignment failed.');
    }

    foreach ([
        ['nid' => '17junk'],
        ['nid' => '18'],
        ['tid' => '32'],
        ['pid' => '8'],
        ['node_matcheslist' => ['12', '99']],
        ['node_matcheslist' => ['12junk']],
        ['node_matcheslist' => [true]],
        ['node_matcheslist' => [[12]]],
        ['node_matcheslist' => '12'],
        ['node_matcheslist' => array_fill(0, 501, '12')],
    ] as $bad) {
        $before = [$db->starts, $db->deletes, $db->inserts];
        $payload = array_replace($valid, $bad);
        if ($model->store($payload + ['id' => $payload['nid']]) !== false
            || $before !== [$db->starts, $db->deletes, $db->inserts]) {
            throw new \RuntimeException('Invalid tournament-tree scope or match IDs modified the set.');
        }
    }
    // A pure GET node id must never become an implicit POST write target.
    $app->posted = ['tid' => '31', 'pid' => '7', 'node_matcheslist' => ['12']];
    $before = [$db->starts, $db->deletes];
    $controller->save_matcheslist();
    if ([$db->starts, $db->deletes] !== $before
        || $controller->redirect['type'] !== 'error') {
        throw new \RuntimeException('Missing POST target was substituted from GET.');
    }

    $app->posted = ['nid' => '17', 'tid' => '31', 'pid' => '7', 'node_matcheslist' => []];
    if ($model->store($app->posted + ['id' => '17']) !== true
        || $db->deletes !== 2 || $db->inserts !== 2) {
        throw new \RuntimeException('Legitimate clearing of a tree node failed.');
    }

    $db->failInserts = true;
    if ($model->store($valid + ['id' => '17']) !== false
        || $db->rollbacks !== 1) {
        throw new \RuntimeException('Failed assignment INSERT did not roll back.');
    }

    echo "Joomla 5/6 tournament-tree assignment scope and ACL OK\n";
}
