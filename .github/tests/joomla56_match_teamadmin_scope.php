<?php
/**
 * Regression checks for project-scoped frontend team-administrator rights.
 *
 * Usage: php .github/tests/joomla56_match_teamadmin_scope.php
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
    final class ParameterType
    {
        public const INTEGER = 'integer';
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementProjectModel
    {
        protected int $projectId = 0;
        public static ?object $database = null;
        public function __construct(mixed $config = [], mixed $factory = null) {}
        public function getDatabase(): object { return self::$database; }
        public function siteApplication(): object
        {
            return new class {
                public function getIdentity(): object { return (object) ['id' => 0]; }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Model/ResultsAccessModel.php';

    final class MatchAdminQuery
    {
        public array $conditions = [];
        public array $binds = [];
        public function select(mixed $value): self { return $this; }
        public function from(string $value): self { return $this; }
        public function join(string $mode, string $clause): self { return $this; }
        public function where(string $value): self
        {
            $this->conditions[] = $value;
            return $this;
        }
        public function bind(string|array $names, mixed $value, string $type): self
        {
            foreach ((array) $names as $name) {
                $this->binds[$name] = $value;
            }

            return $this;
        }
    }

    final class MatchAdminDb
    {
        public ?MatchAdminQuery $query = null;
        public int $result = 1;
        public bool $unavailable = false;
        public function createQuery(): MatchAdminQuery { return new MatchAdminQuery(); }
        public function quoteName(string $name): string { return $name; }
        public function setQuery(MatchAdminQuery $query): void { $this->query = $query; }
        public function loadResult(): int
        {
            if ($this->unavailable) {
                throw new \RuntimeException('database unavailable');
            }

            return $this->result;
        }
    }

    $db = new MatchAdminDb();
    \Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementProjectModel::$database = $db;
    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\ResultsAccessModel();
    $model->setProjectId(71);

    if (!$model->isMatchAdmin(99, 23)) {
        throw new \RuntimeException('Project-scoped match admin query failed.');
    }

    $clause = implode(' ', $db->query?->conditions ?? []);
    $expected = '((pt1.admin = :homeAdminId AND pt1.project_id = :homeProjectId)'
        . ' OR (pt2.admin = :awayAdminId AND pt2.project_id = :awayProjectId))';

    if (!str_contains($clause, $expected)
        || ($db->query?->binds[':matchId'] ?? null) !== 99
        || ($db->query?->binds[':homeAdminId'] ?? null) !== 23
        || ($db->query?->binds[':awayAdminId'] ?? null) !== 23
        || ($db->query?->binds[':homeProjectId'] ?? null) !== 71
        || ($db->query?->binds[':awayProjectId'] ?? null) !== 71) {
        throw new \RuntimeException('Team admin permission must be paired with its own project team.');
    }

    $db->result = 0;
    if ($model->isMatchAdmin(99, 23)) {
        throw new \RuntimeException('Nonmatching team admin was accepted.');
    }
    $db->unavailable = true;
    if ($model->isMatchAdmin(99, 23)) {
        throw new \RuntimeException('Database failure must deny team-admin access.');
    }
    $db->unavailable = false;

    if ($model->isMatchAdmin(0, 23) || $model->isMatchAdmin(99, -1)) {
        throw new \RuntimeException('Invalid IDs must deny team-admin access.');
    }

    $model->setProjectId(0);
    $db->result = 1;
    if (!$model->isMatchAdmin(99, 23)) {
        throw new \RuntimeException('Legacy project-less team-admin check regressed.');
    }
    $fallback = implode(' ', $db->query?->conditions ?? []);
    if (!str_contains($fallback, '(pt1.admin = :homeAdminId OR pt2.admin = :awayAdminId)')
        || isset($db->query?->binds[':homeProjectId'])) {
        throw new \RuntimeException('Project-less team-admin lookup must not use a fake project.');
    }

    echo "Joomla 5/6 team admin and project binding OK\n";
}
