<?php
/**
 * Joomla 5/6 scoped individual-match writer security and integrity regression.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType {
        public const INTEGER = 'integer';
    }
}
namespace {
    \define('_JEXEC', 1);

    class QueryStub {
        public string $table = '';
        public array $columns = [];
        public array $conditions = [];
        public array $ids = [];
        public array $bindings = [];
        public function select(string|array $columns): self {
            $this->columns = (array) $columns;
            return $this;
        }
        public function from(string $table): self {
            $this->table = $table;
            return $this;
        }
        public function join(string $type, string $expression): self {
            $this->conditions[] = $type . ':' . $expression;
            return $this;
        }
        public function where(string $condition): self {
            $this->conditions[] = $condition;
            return $this;
        }
        public function whereIn(string $field, array $ids, mixed $type): self {
            $this->ids[$field] = $ids;
            return $this;
        }
        public function bind(string $key, mixed $value, mixed $type): self {
            $this->bindings[$key] = $value;
            return $this;
        }
        public function order(string $ordering): self { return $this; }
    }
    class DbStub implements \Joomla\Database\DatabaseInterface {
        public array $singleMatches = [
            101 => ['match_id' => 50, 'team1_result' => 0, 'team2_result' => 0],
            102 => ['match_id' => 50, 'team1_result' => 0, 'team2_result' => 0],
            201 => ['match_id' => 60, 'team1_result' => 0, 'team2_result' => 0],
        ];
        public array $matches = [
            50 => ['project' => 8, 'projectteam1_id' => 401, 'projectteam2_id' => 402],
            60 => ['project' => 9, 'projectteam1_id' => 601, 'projectteam2_id' => 602],
        ];
        public int $updateCalls = 0;
        public int $transactions = 0;
        public int $commits = 0;
        public int $rollbacks = 0;
        public array $writes = [];
        public bool $failSelection = false;
        private ?QueryStub $query = null;

        public function createQuery(): QueryStub { return new QueryStub(); }
        public function quoteName(string $field, string $alias = ''): string { return $field; }
        public function setQuery(QueryStub $query, int $offset = 0, int $limit = 0): self {
            $this->query = $query;
            return $this;
        }
        public function loadObject(): ?object {
            $q = $this->query;
            if ($q->table === '#__sportsmanagement_match') {
                $matchId = (int) ($q->bindings[':singleParentMatchId'] ?? 0);
                $projectId = (int) ($q->bindings[':singleParentProjectId'] ?? 0);
                if (!isset($this->matches[$matchId])
                    || $this->matches[$matchId]['project'] !== $projectId
                    || !in_array('r.project_id = :singleParentProjectId', $q->conditions, true)) {
                    return null;
                }
                return (object) $this->matches[$matchId];
            }
            if ($q->table === '#__sportsmanagement_project') {
                return (object) ['sports_type_id' => 3, 'use_tie_break' => false, 'game_parts' => 3];
            }
            return null;
        }
        public function loadResult(): mixed {
            $q = $this->query;
            if ($q->table === '#__sportsmanagement_match_single') {
                if ($this->failSelection) {
                    throw new \RuntimeException('Query failure');
                }
                if (!in_array('ms.match_id = :singleParentId', $q->conditions, true)
                    || ($q->bindings[':singleParentId'] ?? null) === null) {
                    throw new \RuntimeException('Missing bound parent-match scope.');
                }
                if (($q->bindings[':singleParentId'] ?? 0) <= 0
                    || !str_contains(implode(',', $q->columns), 'COUNT(DISTINCT ms.id)')) {
                    throw new \RuntimeException('Individual-match batch count missing.');
                }
                return count(array_filter($q->ids['ms.id'] ?? [], fn (int $id): bool =>
                    ($this->singleMatches[$id]['match_id'] ?? 0) === $q->bindings[':singleParentId']));
            }
            if ($q->table === '#__sportsmanagement_sports_type') {
                return 'COM_SPORTSMANAGEMENT_ST_GOLF_BILLARD';
            }
            throw new \RuntimeException('Unexpected scalar SELECT: ' . $q->table);
        }
        public function loadObjectList(): array {
            $q = $this->query;
            if ($q->table !== '#__sportsmanagement_match_single') {
                return [];
            }
            $matchId = 0;
            foreach ($q->conditions as $condition) {
                if (preg_match('/ms\.match_id = (\d+)/', $condition, $hit)) {
                    $matchId = (int) $hit[1];
                }
            }
            return array_values(array_map(static fn (array $v): object => (object) $v,
                array_filter($this->singleMatches,
                    static fn (array $value): bool => $value['match_id'] === $matchId)));
        }
        public function updateObject(string $table, object $row, string $key, bool $nulls = false): void {
            if ($key !== 'id') {
                throw new \RuntimeException('Invalid update key.');
            }
            $this->writes[] = ['table' => $table, 'row' => clone $row];
            $this->updateCalls++;
            if ($table === '#__sportsmanagement_match_single') {
                $id = (int) $row->id;
                if (!isset($this->singleMatches[$id])) {
                    throw new \RuntimeException('Unexpected single-match ID.');
                }
                $this->singleMatches[$id] = array_merge($this->singleMatches[$id], (array) $row);
            }
        }
        public function transactionStart(): void { $this->transactions++; }
        public function transactionCommit(): void { $this->commits++; }
        public function transactionRollback(): void { $this->rollbacks++; }
    }

    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Service/IndividualMatchWriteService.php';
    $db = new DbStub();
    $writer = new \Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchWriteService($db);
    $payload = [
        'match_id' => 50, 'project_id' => 8,
        'rid' => 777,
        'division_id' => 998,
        'projectteam1_id' => 995,
        'projectteam2_id' => 996,
        'team1_result_split101' => ['2', '3'],
        'team2_result_split101' => ['0', '1'],
    ];

    // A malicious cid[] must not update a child owned by a different match.
    foreach ([[101, 201], [201], [101, 999]] as $ids) {
        if ($writer->saveShort($payload, $ids, 7, '2026-10-11 12:00:00')) {
            throw new \RuntimeException('Foreign or missing individual-match ID was accepted.');
        }
    }
    if ($db->updateCalls !== 0 || $db->transactions !== 0) {
        throw new \RuntimeException('Rejected batch performed database writes.');
    }

    // A forged project reference must not authorize this parent match.
    $differentProject = $payload;
    $differentProject['project_id'] = 9;
    if ($writer->saveShort($differentProject, [101], 7, '2026-10-11 12:00:00')) {
        throw new \RuntimeException('Forged project ID was accepted.');
    }

    $unknownMatch = $payload;
    $unknownMatch['match_id'] = 999;
    if ($writer->saveShort($unknownMatch, [101], 7, '2026-10-11 12:00:00')) {
        throw new \RuntimeException('Unknown parent match ID was accepted.');
    }

    if (!$writer->saveShort($payload, [101, 101, -1, 0], 7, '2026-10-11 12:00:00')) {
        throw new \RuntimeException('Valid selected child did not save.');
    }
    if ($db->updateCalls !== 2 || $db->transactions !== 1 || $db->commits !== 1) {
        throw new \RuntimeException('Expected one child and one parent update within a transaction.');
    }
    $savedSingle = $db->writes[0]['row'];
    if ((int) $savedSingle->id !== 101
        || (int) $savedSingle->team1_result !== 5
        || (int) $savedSingle->team2_result !== 1) {
        throw new \RuntimeException('Valid individual result was not calculated or persisted.');
    }
    foreach (['round_id', 'division_id', 'projectteam1_id', 'projectteam2_id'] as $field) {
        if (property_exists($savedSingle, $field)) {
            throw new \RuntimeException('Individual results reassigned protected field: ' . $field);
        }
    }
    $parent = $db->writes[1]['row'];
    if ((int) $parent->id !== 50
        || (int) $parent->team1_result !== 5
        || (int) $parent->team2_result !== 1) {
        throw new \RuntimeException('Parent match result aggregation did not run.');
    }

    if (!$writer->saveShort($payload, [], 7, '2026-10-11 12:00:00')
        || $db->updateCalls !== 2) {
        throw new \RuntimeException('Empty selection did not preserve legacy no-op behavior.');
    }

    $db->failSelection = true;
    try {
        $writer->saveShort($payload, [101], 7, '2026-10-11 12:00:00');
        throw new \RuntimeException('A child selection failure was silently accepted.');
    } catch (\RuntimeException $error) {
        if ($error->getMessage() !== 'Query failure') {
            throw $error;
        }
    }
    if ($db->updateCalls !== 2) {
        throw new \RuntimeException('A failed lookup performed an unauthorized write.');
    }

    echo "Joomla 5/6 individual-match child scope, project scope and transaction integrity OK\n";
}
