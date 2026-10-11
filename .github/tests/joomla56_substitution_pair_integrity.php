<?php
/**
 * Joomla 5/6 regression tests for safe substitution pair deletion.
 *
 * Usage: php .github/tests/joomla56_substitution_pair_integrity.php
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType
    {
        public const INTEGER = 'integer';
        public const STRING = 'string';
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Service/MatchMutationService.php';
    require_once $root . '/admin/src/Service/MatchTimelineWriteService.php';

    final class SubstitutionQuery
    {
        public string $action = 'select';
        public array $where = [];
        public array $binds = [];
        public array $whereIn = [];

        public function select(mixed $fields): self { return $this; }
        public function from(string $table): self { return $this; }
        public function delete(string $table): self
        {
            $this->action = 'delete';
            return $this;
        }
        public function where(string $condition): self
        {
            $this->where[] = $condition;
            return $this;
        }
        public function whereIn(string $column, array $values, string $type): self
        {
            $this->whereIn[$column] = $values;
            return $this;
        }
        public function bind(string $name, mixed $value, string $type): self
        {
            $this->binds[$name] = $value;
            return $this;
        }
    }

    final class SubstitutionDatabase implements \Joomla\Database\DatabaseInterface
    {
        public ?SubstitutionQuery $query = null;
        public array $queries = [];
        public array $deleted = [];
        public int $started = 0;
        public int $committed = 0;
        public int $rolledBack = 0;
        public bool $rejectPair = false;
        public ?object $incoming = null;
        public array $outgoingIds = [];

        public function createQuery(): SubstitutionQuery { return new SubstitutionQuery(); }
        public function quoteName(string $value): string { return $value; }
        public function setQuery(SubstitutionQuery $query, int $offset = 0, int $limit = 0): self
        {
            $this->query = $query;
            $this->queries[] = $query;
            return $this;
        }
        public function loadObject(): ?object
        {
            if ($this->query?->binds[':substitutionId'] !== 100
                || !in_array('came_in = 1', $this->query->where, true)) {
                throw new \RuntimeException('Incoming substitution was not selected by ID and status.');
            }

            return $this->incoming;
        }
        public function loadColumn(): array
        {
            foreach ([
                ':matchId' => 11, ':outPlayerId' => 25,
                ':positionId' => 3, ':substitutionTime' => '53',
            ] as $name => $expected) {
                if ($this->query?->binds[$name] !== $expected) {
                    throw new \RuntimeException('Outgoing substitution lookup was not scoped by ' . $name);
                }
            }
            if (!in_array('came_in = 2', $this->query->where, true)) {
                throw new \RuntimeException('Outgoing substitution status was not checked.');
            }

            return $this->outgoingIds;
        }
        public function execute(): bool
        {
            if ($this->query?->action !== 'delete') {
                throw new \RuntimeException('Expected substitution DELETE query.');
            }

            $this->deleted = $this->query->whereIn['id'] ?? [];
            return true;
        }
        public function transactionStart(): void { ++$this->started; }
        public function transactionCommit(): void { ++$this->committed; }
        public function transactionRollback(): void { ++$this->rolledBack; }
    }

    $incoming = (object) [
        'match_id' => 11,
        'in_for' => 25,
        'in_out_time' => '53',
        'project_position_id' => 3,
    ];

    foreach (['site', 'admin'] as $area) {
        foreach ([
            ['pair', $incoming, [107], true, [100, 107]],
            ['single', $incoming, [], true, [100]],
            ['missing', null, [], false, []],
            ['ambiguous', $incoming, [107, 109], false, []],
        ] as [$name, $source, $matchingIds, $expectedSuccess, $expectedDeleted]) {
            $database = new SubstitutionDatabase();
            $database->incoming = $source;
            $database->outgoingIds = $matchingIds;

            $service = $area === 'site'
                ? new \Diddipoeler\Component\SportsManagement\Site\Service\MatchMutationService(
                    $database, $database, 1, '2026-10-11 12:00:00'
                )
                : new \Diddipoeler\Component\SportsManagement\Administrator\Service\MatchTimelineWriteService(
                    $database
                );

            $success = $service->removeSubstitution(100);
            if ($success !== $expectedSuccess || $database->deleted !== $expectedDeleted) {
                throw new \RuntimeException($area . '/' . $name . ': deleted incorrect substitution rows.');
            }
            if ($database->started !== (int) $expectedSuccess
                || $database->committed !== (int) $expectedSuccess
                || $database->rolledBack !== 0) {
                throw new \RuntimeException($area . '/' . $name . ': transaction handling regressed.');
            }
        }

        $db = new SubstitutionDatabase();
        $service = $area === 'site'
            ? new \Diddipoeler\Component\SportsManagement\Site\Service\MatchMutationService(
                $db, $db, 1, '2026-10-11 12:00:00'
            )
            : new \Diddipoeler\Component\SportsManagement\Administrator\Service\MatchTimelineWriteService($db);
        if ($service->removeSubstitution(0) !== false || $db->queries !== []) {
            throw new \RuntimeException($area . ': invalid substitution ID accessed database.');
        }
    }

    echo "Joomla 5/6 substitution pair deletion OK\n";
}
