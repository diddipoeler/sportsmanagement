<?php
/**
 * Regression for Joomla 5/6 project extra-field HTML and roster identifier selection.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Service/ProjectsViewDataService.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Service/IndividualMatchRosterReadService.php';

    final class ProjectQueryStub
    {
        public array $conditions = [];

        /** Accept the fields selected by the project extra-field query. */
        public function select(mixed $fields): self { return $this; }

        /** Accept the table selected by the project extra-field query. */
        public function from(string $table): self { return $this; }

        /** Accept the join used by the project extra-field query. */
        public function join(string $type, string $condition): self { return $this; }

        /** Remember project filters to verify the parent-project scope. */
        public function where(string $condition): self
        {
            $this->conditions[] = $condition;
            return $this;
        }

        /** Accept extra-field ordering for the project's list column. */
        public function order(string $ordering): self { return $this; }
    }

    final class ProjectDbStub implements \Joomla\Database\DatabaseInterface
    {
        public int $queries = 0;
        public array $names = [];
        public ?ProjectQueryStub $current = null;

        /** Create a mock query to capture scopes without querying Joomla. */
        public function createQuery(): ProjectQueryStub
        {
            return new ProjectQueryStub();
        }

        /** Return a readable identifier to the test query builder. */
        public function quoteName(string $name, string $alias = ''): string
        {
            return $name;
        }

        /** Quote the exact test literal for a database filter expression. */
        public function quote(string $value): string
        {
            return "'" . str_replace("'", "''", $value) . "'";
        }

        /** Capture the service's completed project extra-field query. */
        public function setQuery(ProjectQueryStub $query): void
        {
            ++$this->queries;
            $this->current = $query;
        }

        /** Return the configured fixture names without database access. */
        public function loadColumn(): array
        {
            return $this->names;
        }
    }

    $db = new ProjectDbStub();
    $projectData = new \Diddipoeler\Component\SportsManagement\Administrator\Service\ProjectsViewDataService($db);

    if ($projectData->getProjectExtraFieldNames(0) !== '' || $db->queries !== 0) {
        throw new \RuntimeException('Invalid projects must not trigger database access.');
    }

    $db->names = ['<img src=x onerror="alert(1)">', 'One & Two', "Player's name", 'Already <br> markup'];
    $actual = $projectData->getProjectExtraFieldNames(7);
    $expected = implode('<br>', array_map(
        static fn (string $name): string => htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        $db->names
    ));
    if ($actual !== $expected || str_contains($actual, '<img')
        || substr_count($actual, '<br>') !== 3
        || !in_array('ev.jl_id = 7', $db->current?->conditions ?? [], true)) {
        throw new \RuntimeException('Project custom-field HTML must be escaped and project-scoped.');
    }

    $roster = new \Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchRosterReadService($db);
    $normalise = new \ReflectionMethod($roster, 'normaliseExcludedIds');
    foreach ([
        [[8, '0008', ' 3 ', 10, '10'], [8, 3, 10]],
        [[true, false, null, 0, -3, ['8'], ['id' => 8], '8invalid'], []],
        [['1.0', '1.5', '0', '0000', PHP_INT_MAX . '0'], []],
        [['0007', '7', 7, 8], [7, 8]],
    ] as [$raw, $expectedIds]) {
        if ($normalise->invoke(null, $raw) !== $expectedIds) {
            throw new \RuntimeException('Malformed player IDs must not be accepted for roster exclusions.');
        }
    }

    if ($roster->getAvailableTeamPersons(0, 7, 5, 1, [1]) !== []) {
        throw new \RuntimeException('Invalid project must produce an empty team roster.');
    }

    echo "Joomla 5/6 project HTML and roster ID validation OK\n";
}
