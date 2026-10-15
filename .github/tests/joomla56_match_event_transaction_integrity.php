<?php
/**
 * Joomla 5/6 regression for atomic frontend event/statistic writes.
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
        public const STRING = 'string';
    }
}

namespace Joomla\CMS\Log {
    final class Log {
        public const ERROR = 400;
        public static array $messages = [];
        public static function add(string $message, int $level, string $category): void {
            self::$messages[] = $message;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Service/MatchMutationService.php';

    final class EventWriteQuery {
        public string $table = '';
        public array $binds = [];

        public function select(mixed $columns): self { return $this; }
        public function from(string $table): self { $this->table = $table; return $this; }
        public function join(string $type, string $condition): self { return $this; }
        public function where(string $condition): self { return $this; }
        public function bind(string $key, mixed $value, string $type): self {
            $this->binds[$key] = $value;
            return $this;
        }
    }

    final class EventWriteDatabase implements \Joomla\Database\DatabaseInterface {
        public array $rows = [];
        public int $started = 0;
        public int $committed = 0;
        public int $rolledBack = 0;
        public int $nextId = 23;
        public bool $duplicate = false;
        public bool $missingInsertId = false;
        public bool $failEventInsert = false;
        public bool $failStatisticLookup = false;
        public bool $failStatisticInsert = false;
        public bool $failCommit = false;
        public array $snapshots = [];
        private ?EventWriteQuery $query = null;
        private int $lastInsertId = 0;

        public function createQuery(): EventWriteQuery { return new EventWriteQuery(); }
        public function quoteName(string $name, ?string $alias = null): string {
            return $alias === null ? $name : $name . ' AS ' . $alias;
        }
        public function setQuery(EventWriteQuery $query, int $offset = 0, int $limit = 0): void {
            $this->query = $query;
        }
        public function loadResult(): int {
            return $this->query?->table === '#__sportsmanagement_match_event' && $this->duplicate ? 7 : 0;
        }
        public function loadObjectList(): array {
            if ($this->failStatisticLookup) {
                throw new \RuntimeException('Statistic selection failed.');
            }

            return [(object) [
                'id' => 9, 'class' => 'basic', 'params' => '{"event_id":3,"event_value":1}',
            ]];
        }
        public function insertObject(string $table, object $row): void {
            if (($table === '#__sportsmanagement_match_event' && $this->failEventInsert)
                || ($table === '#__sportsmanagement_match_statistic' && $this->failStatisticInsert)) {
                throw new \RuntimeException('Simulated insert failure.');
            }
            $this->rows[] = [$table, clone $row];
            $this->lastInsertId = $this->missingInsertId ? 0 : $this->nextId++;
        }
        public function insertid(): int { return $this->lastInsertId; }
        public function transactionStart(): void {
            ++$this->started;
            $this->snapshots[] = $this->rows;
        }
        public function transactionCommit(): void {
            if ($this->failCommit) {
                throw new \RuntimeException('Commit failed.');
            }
            ++$this->committed;
            array_pop($this->snapshots);
        }
        public function transactionRollback(): void {
            ++$this->rolledBack;
            $this->rows = array_pop($this->snapshots) ?? [];
        }
    }

    $data = [
        'match_id' => 11, 'projectteam_id' => 17, 'teamplayer_id' => 29,
        'event_type_id' => 3, 'event_sum' => '1', 'event_time' => '38',
        'notice' => 'test', 'notes' => '',
    ];

    $serviceFor = static fn (EventWriteDatabase $db) =>
        new \Diddipoeler\Component\SportsManagement\Site\Service\MatchMutationService(
            $db, $db, 42, '2026-10-15 12:00:00'
        );

    // Both related rows must commit on the same database connection.
    $successDb = new EventWriteDatabase();
    $result = $serviceFor($successDb)->saveEvent($data);
    if ($result !== 23 || $successDb->started !== 1 || $successDb->committed !== 1
        || $successDb->rolledBack !== 0 || count($successDb->rows) !== 2
        || $successDb->rows[0][0] !== '#__sportsmanagement_match_event'
        || $successDb->rows[1][0] !== '#__sportsmanagement_match_statistic'
        || $successDb->rows[1][1]->statistic_id !== 9) {
        throw new \RuntimeException('Related match event/statistic rows were not committed together.');
    }

    // A failure after the event insert must roll back that event too.
    foreach (['failEventInsert', 'failStatisticLookup', 'failStatisticInsert', 'failCommit', 'missingInsertId'] as $failure) {
        $db = new EventWriteDatabase();
        $db->{$failure} = true;
        if ($serviceFor($db)->saveEvent($data) !== false
            || $db->started !== 1 || $db->committed !== 0
            || $db->rolledBack !== 1 || $db->rows !== []) {
            throw new \RuntimeException('Failed event/statistic write was not rolled back: ' . $failure);
        }
    }

    // Duplicate checks and malformed forms must not start a transaction.
    $duplicateDb = new EventWriteDatabase();
    $duplicateDb->duplicate = true;
    if ($serviceFor($duplicateDb)->saveEvent($data) !== false
        || $duplicateDb->started !== 0 || $duplicateDb->rows !== []) {
        throw new \RuntimeException('Duplicate match event still reached the writer.');
    }

    $invalidDb = new EventWriteDatabase();
    if ($serviceFor($invalidDb)->saveEvent(array_replace($data, ['event_sum' => ''])) !== false
        || $serviceFor($invalidDb)->saveEvent(array_replace($data, ['event_time' => '', 'useeventtime' => true])) !== false
        || $invalidDb->started !== 0 || $invalidDb->rows !== []) {
        throw new \RuntimeException('Invalid match event reached the database.');
    }

    echo "Joomla 5/6 match event/statistic transaction integrity OK\n";
}
