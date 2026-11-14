<?php
/**
 * Test portable, project-bound Joomla 5/6 round selection without site writes.
 *
 * Exercises the native ProjectRoundReader using an isolated database double.
 * Validates public read semantics, bound UTC date filters, Joomla aliases
 * and backward compatibility for the old persistence parameter.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType {
        public const INTEGER = 1;
        public const STRING = 2;
    }
}
namespace {
    \define('_JEXEC', 1);
    require_once \dirname(__DIR__, 2) . '/site/src/Model/ProjectRoundReader.php';

    $db = new class implements \Joomla\Database\DatabaseInterface {
        public ?object $last = null;
        public ?object $record = null;
        public array $rows = [];
        public int $reads = 0;
        public int $writes = 0;

        /**
         * Construct a fluent in-memory query for each round selection.
         *
         * @return object Query with selected columns, joins and bindings.
         */
        public function createQuery(): object
        {
            return new class {
                public array $selects = [];
                public array $where = [];
                public array $joins = [];
                public array $binds = [];
                public array $sort = [];

                /**
                 * Collect all SQL selection expressions for portability tests.
                 *
                 * @param mixed $sql Requested columns.
                 * @return self Current query.
                 */
                public function select(mixed $sql): self {
                    array_push($this->selects, ...(array) $sql);
                    return $this;
                }

                /** @return self Current query source. */
                public function from(string $table): self { return $this; }

                /**
                 * Track join predicates for project ownership checks.
                 *
                 * @param string $type SQL join type.
                 * @param string $expression Join clause.
                 * @return self Current query.
                 */
                public function join(string $type, string $expression): self {
                    $this->joins[] = $expression;
                    return $this;
                }

                /**
                 * Record bound and unbound SQL conditions.
                 *
                 * @param string $expression Query condition.
                 * @return self Current query.
                 */
                public function where(string $expression): self {
                    $this->where[] = $expression;
                    return $this;
                }

                /**
                 * Record parameter names with their supplied value and type.
                 *
                 * @param string $key Joomla SQL parameter placeholder.
                 * @param mixed $value Parameter value.
                 * @param int $type Bound type.
                 * @return self Current query.
                 */
                public function bind(string $key, mixed $value, int $type): self {
                    $this->binds[$key] = [$value, $type];
                    return $this;
                }

                /**
                 * Store ascending or descending round sorting.
                 *
                 * @param string $sort Order expression.
                 * @return self Current query.
                 */
                public function order(string $sort): self {
                    $this->sort[] = $sort;
                    return $this;
                }
            };
        }

        /**
         * Return an SQL identifier for structural assertions.
         *
         * @param string $name Column or table.
         * @return string Quoted name.
         */
        public function quoteName(string $name): string { return $name; }

        /**
         * Capture each project-scoped SQL read.
         *
         * @param object $query Fluent query fixture.
         * @param int $offset Pagination offset.
         * @param int $limit Pagination limit.
         * @return void
         */
        public function setQuery(object $query, int $offset = 0, int $limit = 0): void {
            $this->last = $query;
            ++$this->reads;
        }

        /** @return object|null The configured round fixture. */
        public function loadObject(): ?object { return $this->record === null ? null : clone $this->record; }

        /** @return array Round list fixture copied for presentation. */
        public function loadObjectList(): array {
            return array_map(static fn (object $item): object => clone $item, $this->rows);
        }

        /**
         * Reject all project updates from site-side round readers.
         *
         * @param string $table Database table.
         * @param object $record Write data.
         * @param string $key Record key.
         * @return never Read-only assertion failure.
         */
        public function updateObject(string $table, object $record, string $key): never {
            ++$this->writes;
            throw new \RuntimeException('A site round lookup attempted to update project state.');
        }
    };

    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\ProjectRoundReader($db, 12);
    $db->rows = [(object) ['id' => 4, 'alias' => 'opening', 'roundcode' => 1]];
    $rounds = $model->getRounds('DESC');
    if (count($rounds) !== 1 || $rounds[0]->id !== '4:opening'
        || $db->last->sort !== ['r.roundcode DESC']
        || !isset($db->last->binds[':roundListProjectId'])
        || str_contains(implode(' ', $db->last->selects), 'CONCAT_WS')) {
        throw new \RuntimeException('Round listing retained MySQL-specific slug SQL or lost project scope.');
    }

    $unslugged = $model->getRounds('invalid', false);
    if ($unslugged[0]->id !== 4 || $db->last->sort !== ['r.roundcode ASC']) {
        throw new \RuntimeException('Unslugged round order or ASC fallback changed.');
    }

    $db->record = (object) ['id' => 4, 'alias' => 'opening', 'roundcode' => 1];
    $foreign = (object) ['id' => 90, 'current_round' => 2, 'current_round_auto' => 1, 'auto_time' => 7200];
    if ($model->getCurrentRound($foreign, true) !== null) {
        throw new \RuntimeException('Foreign-project round state was accepted.');
    }

    $project = (object) ['id' => 12, 'current_round' => 2, 'current_round_auto' => 1, 'auto_time' => 7200];

    foreach ([0, 1, 2, 3, 4] as $mode) {
        $project->current_round_auto = $mode;
        $round = $model->getCurrentRound($project, true);

        if ($round?->id !== 4 || $round->round_slug !== '4:opening'
            || $model->getCurrentRoundId($project, true) !== 4
            || $db->writes !== 0) {
            throw new \RuntimeException('Site round reader must resolve matching rounds without persistence.');
        }

        $joined = implode(' ', $db->last->joins);
        if ($mode >= 3 && !str_contains($joined, 'm.round_id = r.id')) {
            throw new \RuntimeException('Automatic round match join was lost.');
        }

        $query = $db->last;
        $selection = implode(' ', $query->selects);
        $conditions = implode(' ', $query->where);
        if (str_contains($selection, 'CONCAT_WS')
            || str_contains($conditions, 'INTERVAL')
            || ($query->binds[':autoRoundProjectId'] ?? null) !== [12, \Joomla\Database\ParameterType::INTEGER]) {
            throw new \RuntimeException('Automatic round query lost portable typed project binding.');
        }

        if ($mode === 1 || $mode === 2 || $mode === 3) {
            if (!isset($query->binds[':roundCutoffAfter']) || !str_contains($query->binds[':roundCutoffAfter'][0], ':')) {
                throw new \RuntimeException('Automatic round used an unbound UTC after-cutoff.');
            }
        }

        if ($mode === 4 && !isset($query->binds[':roundCutoffBefore'])) {
            throw new \RuntimeException('Automatic round used an unbound UTC before-cutoff.');
        }
    }

    $missing = new \Diddipoeler\Component\SportsManagement\Site\Model\ProjectRoundReader($db, -3);
    $oldReads = $db->reads;
    if ($missing->getRounds() !== [] || $missing->getCurrentRoundId($project) !== 0
        || $db->reads !== $oldReads) {
        throw new \RuntimeException('Invalid project identifier was allowed to query rounds.');
    }

    echo "Joomla 5/6 project round reader portability and read-only access OK\n";
}
