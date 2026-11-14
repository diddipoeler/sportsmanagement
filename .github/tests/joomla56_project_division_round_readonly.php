<?php
/**
 * Verify Joomla 5/6 project division isolation and read-only round resolution.
 *
 * Runs the native base project model against an isolated fake Joomla database
 * and checks bind parameters, foreign roots, malformed favourite IDs and
 * the absence of anonymous project writes.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    final class ParameterType
    {
        public const INTEGER = 'integer';
        public const STRING = 'string';
    }
}

namespace Joomla\CMS\MVC\Factory {
    interface MVCFactoryInterface
    {
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    abstract class SportsManagementModel
    {
        public object $database;

        /**
         * Return the synthetic database used by read-only model tests.
         *
         * @return object Query-tracking database double.
         */
        protected function getDatabase(): object
        {
            return $this->database;
        }
    }

    class ProjectScopeTestModel extends SportsManagementProjectModel
    {
        public object $project;

        /**
         * Return a synthetic project without database reads.
         *
         * Keeps the round selection under test independent from project joins.
         *
         * @return object Project fixture.
         */
        public function getProject(): ?object
        {
            return $this->project;
        }

        /**
         * Make the protected subtree reader callable by isolated tests.
         *
         * @param int $root Root division identifier.
         * @return array Division identifiers including descendants.
         */
        public function descendants(int $root): array
        {
            return $this->getDivisionTreeIds($root);
        }
    }
}

namespace {
    \define('_JEXEC', 1);
    require_once \dirname(__DIR__, 2) . '/site/src/Model/SportsManagementProjectModel.php';

    $assert = static function (bool $ok, string $reason): void {
        if (!$ok) {
            throw new \RuntimeException($reason);
        }
    };

    $database = new class {
        public array $queries = [];
        public array $divisions = [];
        public ?object $row = null;
        public int $writes = 0;

        /**
         * Allocate an isolated query with its own bound values.
         *
         * @return object Fluent fake Joomla query.
         */
        public function createQuery(): object
        {
            return new class {
                public array $bindings = [];
                public array $where = [];
                public array $order = [];

                /** @return self The query after selecting columns. */
                public function select(mixed $columns): self { return $this; }
                /** @return self The query after choosing the source table. */
                public function from(mixed $table): self { return $this; }
                /** @return self The query after adding an SQL join. */
                public function join(mixed $type, mixed $condition): self { return $this; }

                /**
                 * Remember each bound condition for project ownership assertions.
                 *
                 * @param string $expression SQL condition with a named placeholder.
                 * @return self Fluent query instance.
                 */
                public function where(string $expression): self
                {
                    $this->where[] = $expression;
                    return $this;
                }

                /**
                 * Track each typed named parameter independently.
                 *
                 * @param string $name Placeholder identifier.
                 * @param mixed $value SQL parameter value.
                 * @param string $type Parameter type.
                 * @return self Fluent query instance.
                 */
                public function bind(string $name, &$value, string $type): self
                {
                    $this->bindings[$name] = [$value, $type];
                    return $this;
                }

                /**
                 * Record read-only row ordering requested by the model.
                 *
                 * @param mixed $rule SQL order clause.
                 * @return self Fluent query instance.
                 */
                public function order(mixed $rule): self
                {
                    $this->order[] = $rule;
                    return $this;
                }
            };
        }

        /**
         * Format a column or table name for the query double.
         *
         * @param string $column SQL identifier.
         * @return string Quoted identifier.
         */
        public function quoteName(string $column): string { return '"' . $column . '"'; }

        /**
         * Capture each query submitted by a native project model.
         *
         * @param object $query Fluent query instance.
         * @param int $offset Result offset.
         * @param int $limit Result limit.
         * @return void
         */
        public function setQuery(object $query, int $offset = 0, int $limit = 0): void
        {
            $this->queries[] = $query;
        }

        /** @return object|null Synthetic division or round. */
        public function loadObject(): ?object { return $this->row; }
        /** @return array Synthetic project-owned division rows. */
        public function loadObjectList(): array { return $this->divisions; }

        /**
         * Fail whenever a public model attempts to write to the database.
         *
         * @param string $table Table being modified.
         * @param object $record Record with changed fields.
         * @param string $key Primary key.
         * @return never This method is forbidden in read-only requests.
         */
        public function updateObject(string $table, object $record, string $key): never
        {
            ++$this->writes;
            throw new \RuntimeException('Read-only frontend query attempted a database write.');
        }
    };

    $model = (new \ReflectionClass(
        \Diddipoeler\Component\SportsManagement\Site\Model\ProjectScopeTestModel::class
    ))->newInstanceWithoutConstructor();
    $model->database = $database;
    $model->project = (object) ['current_round' => 1, 'current_round_auto' => 1, 'auto_time' => 7200];
    $model->setProjectId(9);

    $assert($model->getDivision(0) === null && $database->queries === [], 'Invalid division touched the database.');
    $database->row = (object) ['id' => 12, 'project_id' => 9];
    $assert($model->getDivision(12)?->id === 12, 'Project-owned division did not load.');
    $divisionQuery = $database->queries[0];
    $assert(
        $divisionQuery->bindings[':divisionId'] === [12, \Joomla\Database\ParameterType::INTEGER]
        && $divisionQuery->bindings[':divisionProjectId'] === [9, \Joomla\Database\ParameterType::INTEGER]
        && count($divisionQuery->where) === 2,
        'Division query must bind both project and division IDs.'
    );

    $database->divisions = [
        (object) ['id' => 11, 'parent_id' => 10],
        (object) ['id' => 10, 'parent_id' => 0],
        (object) ['id' => 12, 'parent_id' => 11],
        (object) ['id' => 13, 'parent_id' => 13],
        null,
    ];
    $assert($model->descendants(77) === [], 'Unknown or foreign root must not become a fabricated division.');
    $assert($model->descendants(10) === [10, 11, 12], 'Project-owned division descendants changed.');
    $treeQuery = $database->queries[2];
    $assert($treeQuery->bindings[':divisionTreeProjectId'][0] === 9, 'Division tree must be project-bound.');

    $model->project = (object) ['fav_team' => '07,7, 23junk, +3, 0011, 0, 92233720368547758080, -5'];
    $assert($model->getFavTeams() === [7, 11], 'Favourite teams accepted partial, duplicate or overflowing IDs.');

    $model->project = (object) ['current_round' => 1, 'current_round_auto' => 1, 'auto_time' => 7200];
    $database->row = (object) ['id' => 4, 'roundcode' => 8, 'alias' => 'round'];
    $assert($model->getCurrentRound() === 4, 'Automatic round was not resolved.');
    $assert($model->getCurrentRoundNumber() === 8, 'Automatic round code was not resolved.');
    $assert($database->writes === 0, 'Frontend round retrieval modified project state.');

    $model->setProjectId(0);
    $count = count($database->queries);
    $assert($model->getDivision(12) === null && $model->descendants(10) === [], 'Missing project bypassed read guards.');
    $assert(count($database->queries) === $count, 'Missing project ID executed database queries.');

    echo "Joomla 5/6 project divisions, favourites and read-only round resolution: OK\n";
}
