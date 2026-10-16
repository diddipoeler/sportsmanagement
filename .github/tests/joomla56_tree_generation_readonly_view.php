<?php
/**
 * Joomla 5/6 tournament node generation and read-only view security regression.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Registry {
    class Registry {
        public function __construct(private array $data = []) {}
        public function get(string $key, mixed $default = null): mixed {
            return $this->data[$key] ?? $default;
        }
    }
}
namespace Joomla\Database {
    final class ParameterType {
        public const INTEGER = 'integer';
    }
}
namespace Joomla\CMS\MVC\Factory {
    interface MVCFactoryInterface {}
}
namespace Joomla\CMS\Session {
    class Session {
        public static bool $valid = false;
        public static function checkToken(string $type): bool {
            return $type === 'post' && self::$valid;
        }
    }
}
namespace Joomla\CMS {
    class Factory {
        public static function getApplication(): object { return $GLOBALS['generationApp']; }
        public static function getDate(): object {
            return new class {
                public function toSql(): string { return '2026-10-16 12:00:00'; }
            };
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Table {
    class TreetoTable {
        public function __construct(object $db) {}
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel {
        public string $error = '';
        protected function administratorApplication(): object { return $GLOBALS['generationApp']; }
        protected function getDatabase(): object { return $GLOBALS['generationDb']; }
        public function setError(string $message): void { $this->error = $message; }
        public function getError(): string { return $this->error; }
    }
    class SportsManagementListModel {
        public string $error = '';
        public function __construct($config = [], ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null) {}
        protected function administratorApplication(): object { return $GLOBALS['generationApp']; }
        protected function getDatabase(): object { return $GLOBALS['generationDb']; }
        public function setError(string $message): void { $this->error = $message; }
        public function getError(): string { return $this->error; }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Model/TreetoModel.php';
    require_once $root . '/admin/src/Model/TreetonodesModel.php';

    // Regression: simply rendering the administrator view must not invoke
    // the legacy synchronization write, even for a read-only administrator.
    $viewSource = file_get_contents($root . '/admin/src/View/Treetonodes/HtmlView.php');
    if (!is_string($viewSource) || str_contains($viewSource, '$model->savenode(')) {
        throw new \RuntimeException('Administrator tournament-tree GET view still persists node data.');
    }

    final class GenerationQuery {
        public string $source = '';
        public array $bindings = [];
        public array $in = [];
        public function select(mixed $fields): self { return $this; }
        public function from(string $source): self { $this->source = $source; return $this; }
        public function where(string $condition): self { return $this; }
        public function whereIn(string $column, array $values, string $type): self {
            $this->in = $values;
            return $this;
        }
        public function bind(string $name, mixed $value, string $type): self {
            $this->bindings[$name] = $value;
            return $this;
        }
    }
    final class GenerationDb {
        public ?GenerationQuery $query = null;
        public int $existingNodes = 0;
        public int $existingMatch = 0;
        public int $transactions = 0;
        public int $commits = 0;
        public int $rollbacks = 0;
        public int $treeUpdates = 0;
        public int $nodeUpdates = 0;
        public int $nodeInserts = 0;
        public int $mappingInserts = 0;
        public bool $rejectUpdate = false;
        public int $rejectNodeInsert = 0;
        public bool $rejectMappingInsert = false;
        public array $limits = [];
        public function createQuery(): GenerationQuery { return new GenerationQuery(); }
        public function quoteName(string $name): string { return $name; }
        public function setQuery(GenerationQuery $query, int $offset = 0, int $limit = 0): self {
            $this->query = $query;
            $this->limits[] = $limit;
            return $this;
        }
        public function loadResult(): int {
            $query = $this->query ?? throw new \RuntimeException('Query missing.');
            $args = $query->bindings;

            if ($query->source === '#__sportsmanagement_treeto') {
                return ($args[':generationTreeId'] ?? 0) === 11
                    && ($args[':generationProjectId'] ?? 0) === 9
                    ? 11 : 0;
            }
            if ($query->source === '#__sportsmanagement_treeto_node') {
                return $this->existingNodes;
            }
            if ($query->source === '#__sportsmanagement_treeto_match') {
                return $this->existingMatch;
            }
            throw new \RuntimeException('Unexpected generation lookup.');
        }
        public function loadObject(): ?object {
            $query = $this->query ?? throw new \RuntimeException('Query missing.');
            return $query->source === '#__sportsmanagement_treeto'
                && ($query->bindings[':nodeTreeId'] ?? null) === 11
                && ($query->bindings[':nodeTreeProjectId'] ?? null) === 9
                ? (object) ['id' => 11, 'tree_i' => 3] : null;
        }
        public function loadColumn(): array {
            $query = $this->query ?? throw new \RuntimeException('Query missing.');
            if ($query->source !== '#__sportsmanagement_treeto_node'
                || ($query->bindings[':selectedTreeId'] ?? null) !== 11) {
                throw new \RuntimeException('Unscoped node lookup.');
            }
            return array_values(array_intersect($query->in, [101]));
        }
        public function transactionStart(): void { ++$this->transactions; }
        public function transactionCommit(): void { ++$this->commits; }
        public function transactionRollback(): void { ++$this->rollbacks; }
        public function updateObject(string $table, object $record, string $key): bool {
            if ($this->rejectUpdate) { return false; }
            if ($table === '#__sportsmanagement_treeto') {
                ++$this->treeUpdates;
            } elseif ($table === '#__sportsmanagement_treeto_node') {
                ++$this->nodeUpdates;
            } else {
                throw new \RuntimeException('Unexpected update table.');
            }
            return true;
        }
        public function insertObject(string $table, object $record): bool {
            if ($table === '#__sportsmanagement_treeto_node') {
                ++$this->nodeInserts;
                return $this->nodeInserts !== $this->rejectNodeInsert;
            }
            if ($table === '#__sportsmanagement_treeto_match') {
                if ($this->rejectMappingInsert) { return false; }
                ++$this->mappingInserts;
                return true;
            }
            throw new \RuntimeException('Unexpected INSERT table.');
        }
    }
    final class GenerationApp {
        public object $identity;
        public object $input;
        public array $post = [];
        public function __construct() {
            $this->identity = new class {
                public bool $edit = false;
                public int $id = 21;
                public function authorise(string $permission, string $asset): bool {
                    return $this->edit && $permission === 'core.edit'
                        && $asset === 'com_sportsmanagement';
                }
            };
            $this->input = new class($this) {
                public object $post;
                public function __construct(private GenerationApp $app) {
                    $this->post = new class($app) {
                        public function __construct(private GenerationApp $app) {}
                        public function get(string $key, mixed $default = null, string $filter = ''): mixed {
                            return $this->app->post[$key] ?? $default;
                        }
                        public function getInt(string $key, int $fallback = 0): int {
                            return (int) ($this->app->post[$key] ?? $fallback);
                        }
                    };
                }
            };
        }
        public function getIdentity(): object { return $this->identity; }
        public function getInput(): object { return $this->input; }
    }

    $GLOBALS['generationDb'] = new GenerationDb();
    $GLOBALS['generationApp'] = new GenerationApp();
    $db = $GLOBALS['generationDb'];
    $app = $GLOBALS['generationApp'];
    $tree = new \Diddipoeler\Component\SportsManagement\Administrator\Model\TreetoModel();
    $node = new \Diddipoeler\Component\SportsManagement\Administrator\Model\TreetonodesModel();
    $app->post = ['id' => '11', 'pid' => '9', 'jform' => ['tree_i' => '3']];

    try {
        $tree->setGenerateNode();
        throw new \RuntimeException('Unauthorized tournament-tree generation succeeded.');
    } catch (\RuntimeException $e) {
        if ($e->getCode() !== 403) { throw $e; }
    }
    if ($db->transactions !== 0) {
        throw new \RuntimeException('Unauthorized node generation started a transaction.');
    }

    $app->identity->edit = true;
    foreach ([
        ['id' => '11junk'], ['id' => '0'], ['id' => [11]],
        ['pid' => '10'], ['pid' => '9junk'], ['pid' => [9]],
        ['id' => '99999999999999999999999'],
    ] as $invalid) {
        $post = array_replace($app->post, $invalid);
        $app->post = $post;
        if ($tree->setGenerateNode() !== false || $db->transactions !== 0) {
            throw new \RuntimeException('Invalid tree/project generation reached persistence.');
        }
    }
    $app->post = ['id' => '11', 'pid' => '9', 'jform' => ['tree_i' => '3']];
    $db->existingNodes = 1;
    if ($tree->setGenerateNode() !== false || $db->transactions !== 0) {
        throw new \RuntimeException('Repeated bracket generation appended duplicate nodes.');
    }
    $db->existingNodes = 0;

    $db->rejectUpdate = true;
    if ($tree->setGenerateNode() !== false
        || $db->rollbacks !== 1 || $db->nodeInserts !== 0) {
        throw new \RuntimeException('Failed tree update did not roll back before INSERT.');
    }
    $db->rejectUpdate = false;
    $db->rejectNodeInsert = 3;
    if ($tree->setGenerateNode() !== false
        || $db->rollbacks !== 2 || $db->commits !== 0) {
        throw new \RuntimeException('Failed node INSERT did not roll back.');
    }
    $db->rejectNodeInsert = 0;
    $nodeInserts = $db->nodeInserts;
    if (!$tree->setGenerateNode() || $db->nodeInserts !== $nodeInserts + 15
        || $db->commits !== 1) {
        throw new \RuntimeException('Valid tree generation failed after guards.');
    }

    $app->post = ['treeto_id' => '11', 'project_id' => '9'];
    $value = (object) [
        'id' => 101, 'title' => 'Home', 'content' => 'Home',
        'team_id' => 0, 'roundcode' => 1, 'match_id' => 202,
    ];
    \Joomla\CMS\Session\Session::$valid = false;
    try {
        $node->savenode([$value]);
        throw new \RuntimeException('GET-style synchronization accepted without POST token.');
    } catch (\RuntimeException $e) {
        if ($e->getCode() !== 403) { throw $e; }
    }

    \Joomla\CMS\Session\Session::$valid = true;
    $app->identity->edit = false;
    try {
        $node->savenode([$value]);
        throw new \RuntimeException('Unprivileged tournament-node synchronization succeeded.');
    } catch (\RuntimeException $e) {
        if ($e->getCode() !== 403) { throw $e; }
    }
    $app->identity->edit = true;

    foreach ([
        ['treeto_id' => '12', 'project_id' => '9'],
        ['treeto_id' => '11', 'project_id' => '10'],
        ['treeto_id' => '11junk', 'project_id' => '9'],
    ] as $invalid) {
        $app->post = $invalid;
        if ($node->savenode([$value]) !== false || $db->nodeUpdates !== 0) {
            throw new \RuntimeException('Foreign tree synchronized from model.');
        }
    }
    $app->post = ['treeto_id' => '11', 'project_id' => '9'];
    if ($node->savenode([(object) ['id' => 999]]) !== false
        || $node->savenode([$value, $value]) !== false
        || $node->savenode(array_fill(0, 256, $value)) !== false) {
        throw new \RuntimeException('Invalid or oversized node list synchronized.');
    }
    if ($db->nodeUpdates !== 0) {
        throw new \RuntimeException('Invalid sync wrote nodes.');
    }
    if (!$node->savenode([$value]) || $db->nodeUpdates !== 1
        || $db->mappingInserts !== 1) {
        throw new \RuntimeException('Valid explicit node synchronization regressed.');
    }
    $db->rejectMappingInsert = true;
    if ($node->savenode([$value]) !== false || $db->rollbacks !== 3) {
        throw new \RuntimeException('Failed node/match synchronizer did not roll back.');
    }

    echo "Joomla 5/6 read-only bracket view, bounded tree generation and explicit sync OK\n";
}
