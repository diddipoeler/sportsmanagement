<?php
/**
 * Joomla 5/6 administrator match timeline must reject missing insert IDs atomically.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}

namespace Joomla\CMS {
    class Factory {
        public static function getDate(): object {
            return new class {
                public function toSql(): string { return '2026-10-15 17:00:00'; }
            };
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    class SportsManagementAdministratorApplicationResolver {
        public static function resolve(): \Joomla\CMS\Application\CMSApplicationInterface {
            return new class implements \Joomla\CMS\Application\CMSApplicationInterface {
                public function getIdentity(): object { return (object) ['id' => 19]; }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Service/MatchTimelineWriteService.php';

    use Diddipoeler\Component\SportsManagement\Administrator\Service\MatchTimelineWriteService;

    class AdminTimelineQuery {
        public string $table = '';
        public function select(mixed $columns): self { return $this; }
        public function from(string $table): self { $this->table = $table; return $this; }
        public function where(string $condition): self { return $this; }
        public function join(string $type, string $condition): self { return $this; }
    }

    class AdminTimelineDatabase implements \Joomla\Database\DatabaseInterface {
        public array $rows = [];
        public int $started = 0;
        public int $committed = 0;
        public int $rolledBack = 0;
        public bool $zeroInsertId = false;
        public bool $failInsert = false;
        public bool $failCommit = false;
        public bool $duplicate = false;
        private int $lastId = 0;
        private array $snapshot = [];
        private ?AdminTimelineQuery $query = null;

        public function createQuery(): AdminTimelineQuery { return new AdminTimelineQuery(); }
        public function quoteName(string $name, ?string $alias = null): string {
            return $alias !== null ? $name . ' ' . $alias : $name;
        }
        public function quote(string $value): string { return "'" . str_replace("'", "''", $value) . "'"; }
        public function setQuery(AdminTimelineQuery $query, int $offset = 0, int $limit = 0): self {
            $this->query = $query;
            return $this;
        }
        public function loadResult(): int {
            return $this->duplicate && str_contains($this->query?->table ?? '', 'match_event') ? 5 : 0;
        }
        public function loadObjectList(): array { return []; }
        public function insertObject(string $table, object $row): void {
            if ($this->failInsert) {
                throw new \RuntimeException('Simulated write failure.');
            }
            $this->rows[] = [$table, clone $row];
            $this->lastId = $this->zeroInsertId ? 0 : 91;
        }
        public function insertid(): int { return $this->lastId; }
        public function transactionStart(): void {
            ++$this->started;
            $this->snapshot = $this->rows;
        }
        public function transactionCommit(): void {
            if ($this->failCommit) {
                throw new \RuntimeException('Simulated commit failure.');
            }
            ++$this->committed;
            $this->snapshot = [];
        }
        public function transactionRollback(): void {
            ++$this->rolledBack;
            $this->rows = $this->snapshot;
            $this->snapshot = [];
        }
    }

    $eventData = [
        'match_id' => 11,
        'projectteam_id' => 12,
        'teamplayer_id' => 13,
        'event_type_id' => 14,
        'event_time' => '21',
        'event_sum' => '1',
    ];
    $commentData = [
        'match_id' => 11,
        'event_time' => '21',
        'notes' => 'Valid note',
        'type' => 'normal',
    ];

    foreach (['saveEvent' => $eventData, 'saveComment' => $commentData] as $method => $data) {
        $db = new AdminTimelineDatabase();
        $result = (new MatchTimelineWriteService($db))->{$method}($data);
        $table = $method === 'saveEvent'
            ? '#__sportsmanagement_match_event' : '#__sportsmanagement_match_commentary';

        if ($result !== 91 || $db->started !== 1 || $db->committed !== 1
            || $db->rolledBack !== 0 || count($db->rows) !== 1
            || $db->rows[0][0] !== $table
            || $db->rows[0][1]->modified_by !== 19) {
            throw new \RuntimeException('Valid administrator ' . $method . ' was not committed.');
        }

        foreach (['zeroInsertId', 'failInsert', 'failCommit'] as $failure) {
            $db = new AdminTimelineDatabase();
            $db->{$failure} = true;
            try {
                (new MatchTimelineWriteService($db))->{$method}($data);
                throw new \LogicException('Missing error from ' . $method . '/' . $failure);
            } catch (\RuntimeException $expected) {
                if ($db->started !== 1 || $db->committed !== 0
                    || $db->rolledBack !== 1 || $db->rows !== []) {
                    throw new \LogicException('Uncommitted ' . $method . ' row survived ' . $failure);
                }
            }
        }
    }

    $db = new AdminTimelineDatabase();
    $db->duplicate = true;
    if ((new MatchTimelineWriteService($db))->saveEvent($eventData) !== false
        || $db->started !== 0 || $db->rows !== []) {
        throw new \RuntimeException('Duplicate administrator event reached a transaction.');
    }

    echo "Joomla 5/6 administrator timeline missing insert-ID rollbacks OK\n";
}
