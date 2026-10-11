<?php
/**
 * Joomla 5/6 regression checks for results project ACL, administrator state ACL and module comment bounds.
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
    interface DatabaseInterface {}
    final class ParameterType { public const INTEGER = 'integer'; }
}
namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementProjectModel
    {
        protected int $projectId = 0;
        public static ?object $database = null;
        public static ?object $project = null;
        public static ?object $application = null;

        public function __construct($config = [], ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null) {}
        public function getProject(): ?object { return self::$project; }
        protected function getDatabase(): object { return self::$database; }
        protected function siteApplication(): object { return self::$application; }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    class QueryStub
    {
        public string $selected = '';
        public array $where = [];
        public array $whereIn = [];
        public array $binds = [];
        public array $sorting = [];

        public function select(mixed $value): self { $this->selected = (string) $value; return $this; }
        public function from(string $value): self { return $this; }
        public function join(string $mode, string $condition): self { return $this; }
        public function where(string $value): self { $this->where[] = $value; return $this; }
        public function whereIn(string $key, array $ids, mixed $type): self
        {
            $this->whereIn[$key] = $ids;
            return $this;
        }
        public function bind(string|array $key, mixed $value, mixed $type): self
        {
            foreach ((array) $key as $name) {
                $this->binds[$name] = $value;
            }
            return $this;
        }
        public function order(string|array $order): self
        {
            $this->sorting = (array) $order;
            return $this;
        }
    }

    class DbStub implements \Joomla\Database\DatabaseInterface
    {
        public ?QueryStub $query = null;
        public int $limit = -1;
        public bool $explode = false;
        public array $matchProject = [11 => 71, 12 => 71, 99 => 72];
        public array $rows = [];

        public function createQuery(): QueryStub { return new QueryStub(); }
        public function quoteName(string $name, string $alias = ''): string { return $name; }
        public function setQuery(QueryStub $query, int $offset = 0, int $limit = -1): self
        {
            $this->query = $query;
            $this->limit = $limit;
            return $this;
        }
        public function loadResult(): int
        {
            if ($this->explode) {
                throw new \RuntimeException('Simulated database outage');
            }

            // The scoped COUNT returns only IDs whose rounds belong to project 71.
            if (str_contains($this->query->selected, 'COUNT(DISTINCT')) {
                $ids = $this->query->whereIn['m.id'] ?? [];
                $project = $this->query->binds[':selectedProjectId'] ?? 0;
                return count(array_filter($ids, fn (int $id): bool =>
                    ($this->matchProject[$id] ?? 0) === $project));
            }

            return 0;
        }
        public function loadObjectList(): array { return $this->rows; }
    }

    require_once $root . '/site/src/Model/ResultsAccessModel.php';

    $db = new DbStub();
    $identity = new class {
        public int $id = 5;
        public function authorise(string $action, string $asset): bool { return true; }
        public function getAuthorisedGroups(): array { return []; }
    };
    $app = new class($identity) {
        public function __construct(private object $identity) {}
        public function getIdentity(): object { return $this->identity; }
    };

    $base = \Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementProjectModel::class;
    $base::$database = $db;
    $base::$project = (object) ['id' => 71, 'admin' => 5, 'editor' => 0, 'editorgroup' => 0];
    $base::$application = $app;

    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\ResultsAccessModel();
    $model->setProjectId(71);

    if (!$model->canEditMatches([11, 12, 11])) {
        throw new \RuntimeException('Authorized editor cannot edit matches of own project.');
    }
    if (($db->query->binds[':selectedProjectId'] ?? null) !== 71
        || ($db->query->whereIn['m.id'] ?? []) !== [11, 12]) {
        throw new \RuntimeException('Matching project was not enforced with bound query values.');
    }
    if ($model->canEditMatches([11, 99])
        || $model->canEditMatches([11, 404])
        || $model->canEditMatches([])) {
        throw new \RuntimeException('Foreign, nonexistent or empty match IDs were accepted.');
    }
    $db->explode = true;
    if ($model->canEditMatches([11])) {
        throw new \RuntimeException('Database failure must deny frontend writes.');
    }
    $db->explode = false;
    $model->setProjectId(0);
    if ($model->canEditMatches([11])) {
        throw new \RuntimeException('Missing project ID must deny frontend writes.');
    }

    // The action checked by the native members controller must be configurable.
    $access = new \DOMDocument();
    if (!$access->load($root . '/admin/access.xml')) {
        throw new \RuntimeException('Invalid administrator ACL XML.');
    }
    $xpath = new \DOMXPath($access);
    if ($xpath->query('/access/section[@name="component"]/action[@name="core.edit.state"]')->length !== 1) {
        throw new \RuntimeException('Component-level state ACL was not declared.');
    }

    require_once $root . '/modules/mod_sportsmanagement_liveticker/src/Helper/LivetickerHelper.php';

    $ticker = new \Diddipoeler\Module\SportsManagementLiveticker\Site\Helper\LivetickerHelper();
    $db->rows = [(object) ['match_id' => 11, 'event_time' => 45]];
    $result = $ticker->getListCommentary([
        (object) ['match_id' => 11],
        (object) ['match_id' => 12],
        (object) ['match_id' => 11],
    ], $db);

    if ($db->limit !== 1000 || ($db->query->whereIn['match_id'] ?? []) !== [11, 12]
        || !str_contains($db->query->sorting[0] ?? '', 'event_time')
        || count($result[11] ?? []) !== 1) {
        throw new \RuntimeException('Liveticker commentary limit, order or grouping regressed.');
    }
    $db->query = null;
    $empty = $ticker->getListCommentary([(object) ['match_id' => 0]], $db);
    if ($empty !== [] || $db->query !== null) {
        throw new \RuntimeException('No commentary query should run without positive match IDs.');
    }

    echo "Joomla 5/6 project-scoped results, component ACL and liveticker comments OK\n";
}
