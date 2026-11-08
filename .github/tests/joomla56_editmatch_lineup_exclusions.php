<?php
/**
 * Joomla 5/6 frontend lineup regression for excluded person identifiers.
 *
 * Confirms invalid filters cannot become unrelated roster IDs through PHP
 * coercion and that the team/project scope remains part of the lookup.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}

    final class ParameterType
    {
        public const INTEGER = 'integer';
    }
}

namespace {
    define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/site/src/Service/EditmatchLineupViewDataService.php';

    final class LineupQueryStub
    {
        public array $excluded = [];
        public array $conditions = [];
        public int $exclusionCalls = 0;

        /** Accept the list of fields used by the roster read service. */
        public function select(mixed $fields): self { return $this; }

        /** Accept the primary person table without database access. */
        public function from(string $table): self { return $this; }

        /** Accept the registered team/person joins from the query builder. */
        public function join(string $type, string $condition): self { return $this; }

        /** Remember the project and team restrictions for later assertions. */
        public function where(string $condition): self
        {
            $this->conditions[] = $condition;
            return $this;
        }

        /** Accept the bound ID values used to scope the roster query. */
        public function bind(string $name, mixed $value, mixed $type): self { return $this; }

        /** Accept the ordering used for person dropdown options. */
        public function order(string $order): self { return $this; }

        /** Capture safe IDs passed to the Joomla whereNotIn query builder. */
        public function whereNotIn(string $column, array $ids, mixed $type): self
        {
            ++$this->exclusionCalls;
            $this->excluded = $ids;
            return $this;
        }
    }

    final class LineupDatabaseStub implements \Joomla\Database\DatabaseInterface
    {
        public ?LineupQueryStub $query = null;
        public int $queryCount = 0;

        /** Create an inspectable query object without connecting to Joomla. */
        public function createQuery(): LineupQueryStub
        {
            ++$this->queryCount;
            return new LineupQueryStub();
        }

        /** Return the requested column name for query-scope inspection. */
        public function quoteName(string $name, string $alias = ''): string
        {
            return $name;
        }

        /** Store the complete query for the regression assertions. */
        public function setQuery(LineupQueryStub $query): void
        {
            $this->query = $query;
        }

        /** Return an empty roster without needing database fixtures. */
        public function loadObjectList(): array
        {
            return [];
        }
    }

    $db = new LineupDatabaseStub();
    $service = new \Diddipoeler\Component\SportsManagement\Site\Service\EditmatchLineupViewDataService($db);

    if ($service->getTeamPersons(0, [2], 1, 3, 4) !== [] || $db->queryCount !== 0) {
        throw new \RuntimeException('Invalid project-team IDs must not trigger roster queries.');
    }

    $service->getTeamPersons(
        4,
        [12, '0012', ' 3 ', true, false, [], null, '12garbage', '8.5', '-5', '0', '0000', PHP_INT_MAX . '0'],
        1,
        3,
        7
    );

    if ($db->query?->exclusionCalls !== 1 || $db->query->excluded !== [12, 3]
        || !in_array('pt.id = :projectTeamId', $db->query->conditions, true)
        || !in_array('ppp.project_id = :projectId', $db->query->conditions, true)) {
        throw new \RuntimeException('Lineup exclusion filters must contain only valid IDs and project scope.');
    }

    $service->getTeamPersons(4, [true, null, '1abc', '0', '1.5', PHP_INT_MAX . '0'], 1, 3, 7);

    if ($db->query?->exclusionCalls !== 0 || $db->query->excluded !== []) {
        throw new \RuntimeException('Invalid exclusion inputs must not become selected player IDs.');
    }

    echo "Joomla 5/6 frontend lineup excluded ID validation OK\n";
}
