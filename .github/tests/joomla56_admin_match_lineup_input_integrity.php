<?php
/**
 * Joomla 5/6 regression: malformed match lineup IDs must not clear assignments.
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
                public function toSql(): string { return '2026-10-16 10:00:00'; }
            };
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    class SportsManagementAdministratorApplicationResolver {
        public static function resolve(): \Joomla\CMS\Application\CMSApplicationInterface {
            return new class implements \Joomla\CMS\Application\CMSApplicationInterface {
                public function getIdentity(): object { return (object) ['id' => 5]; }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Service/MatchWriteService.php';

    use Diddipoeler\Component\SportsManagement\Administrator\Service\MatchWriteService;

    class LineupQuery {
        public string $action = 'select';
        public string $table = '';
        public array $where = [];
        public function select(mixed $fields): self { return $this; }
        public function from(string $table): self { $this->table = $table; return $this; }
        public function join(string $type, string $join): self { return $this; }
        public function where(string $condition): self {
            $this->where[] = $condition;
            return $this;
        }
        public function delete(string $table): self {
            $this->table = $table;
            $this->action = 'delete';
            return $this;
        }
    }

    class LineupDatabase implements \Joomla\Database\DatabaseInterface {
        public int $queries = 0;
        public int $started = 0;
        public int $committed = 0;
        public array $writes = [];
        public array $deleted = [];
        private ?LineupQuery $query = null;

        public function createQuery(): LineupQuery {
            ++$this->queries;
            return new LineupQuery();
        }
        public function quoteName(string $name, ?string $alias = null): string {
            return $alias === null ? $name : $name . ' ' . $alias;
        }
        public function setQuery(LineupQuery $query, int $offset = 0, int $limit = 0): self {
            $this->query = $query;
            return $this;
        }
        public function loadColumn(): array { return [501]; }
        public function loadResult(): int { return 0; }
        public function execute(): void {
            $this->deleted[] = [$this->query?->table, $this->query?->where];
        }
        public function insertObject(string $table, object $row): void {
            $this->writes[] = [$table, clone $row];
        }
        public function updateObject(string $table, object $row, string $key): void {
            $this->writes[] = [$table, clone $row];
        }
        public function transactionStart(): void { ++$this->started; }
        public function transactionCommit(): void { ++$this->committed; }
        public function transactionRollback(): void {}
    }

    $roster = [
        'id' => '90', 'team' => '20',
        'positions' => [4 => (object) ['posid' => 7]],
        'position4' => [0 => '11', 1 => '0'],
        'trikot_number' => [11 => '9'], 'captain' => [11 => 1],
    ];
    $staff = [
        'id' => '90', 'team' => '20',
        'staffpositions' => [3 => (object) ['value' => 8]],
        'staffposition3' => [0 => '21'],
    ];
    $referees = [
        'id' => '90',
        'positions' => [2 => (object) ['pposid' => 9]],
        'position2' => [0 => '31'],
    ];

    // No malformed selection is allowed to query or delete the existing lineup.
    $cases = [
        ['updateRoster', $roster, [
            ['id' => '90junk'],
            ['id' => [90]],
            ['team' => ['20']],
            ['team' => '20junk'],
            ['position4' => [0 => '11garbage']],
            ['position4' => [0 => [11]]],
            ['position4' => '11'],
            ['position4' => ['wrong' => '11']],
            ['position4' => [0 => '9999999999999999999999999']],
            ['positions' => [4 => (object) ['posid' => ['7']]]],
            ['position999' => [0 => '11']],
        ]],
        ['updateStaff', $staff, [
            ['id' => 0],
            ['team' => '20junk'],
            ['staffposition3' => [0 => '21junk']],
            ['staffposition3' => [0 => true]],
            ['staffposition3' => ['wrong' => 21]],
            ['staffpositions' => [3 => ['value' => ['8']]]],
            ['staffposition99' => [0 => 21]],
        ]],
        ['updateReferees', $referees, [
            ['id' => [90]],
            ['id' => '90junk'],
            ['position2' => [0 => '31bad']],
            ['position2' => [0 => [31]]],
            ['position2' => '31'],
            ['position2' => ['wrong' => '31']],
            ['positions' => [2 => (object) ['pposid' => ['9']]]],
            ['position999' => [0 => 31]],
        ]],
    ];

    foreach ($cases as [$method, $original, $invalidValues]) {
        foreach ($invalidValues as $tamper) {
            $db = new LineupDatabase();
            $result = (new MatchWriteService($db))->{$method}(array_replace($original, $tamper));
            $failed = $method === 'updateReferees'
                ? $result === ['success' => false, 'added' => []]
                : $result === false;
            if (!$failed || $db->queries !== 0 || $db->started !== 0
                || $db->writes !== [] || $db->deleted !== []) {
                throw new \RuntimeException('Invalid ' . $method . ' cleared records: '
                    . json_encode(array_keys($tamper)));
            }
        }
    }

    foreach ([
        ['updateRoster', $roster, '#__sportsmanagement_match_player', 'teamplayer_id', 11, 7],
        ['updateStaff', $staff, '#__sportsmanagement_match_staff', 'team_staff_id', 21, 8],
        ['updateReferees', $referees, '#__sportsmanagement_match_referee', 'project_referee_id', 31, 9],
    ] as [$method, $post, $table, $personField, $personId, $positionId]) {
        $db = new LineupDatabase();
        $response = (new MatchWriteService($db))->{$method}($post);
        if (($method === 'updateReferees' && $response !== [
                'success' => true,
                'added' => [['project_referee_id' => 31, 'project_position_id' => 9]],
            ]) || ($method !== 'updateReferees' && $response !== true)
            || $db->started !== 1 || $db->committed !== 1
            || count($db->writes) !== 1 || count($db->deleted) !== 1
            || $db->writes[0][0] !== $table
            || $db->writes[0][1]->{$personField} !== $personId
            || $db->writes[0][1]->project_position_id !== $positionId) {
            throw new \RuntimeException('Legitimate ' . $method . ' write regressed.');
        }
    }

    // Submitting no players remains a valid intentional clear operation.
    $db = new LineupDatabase();
    $empty = array_replace($roster, ['position4' => []]);
    if (!(new MatchWriteService($db))->updateRoster($empty)
        || count($db->deleted) !== 1 || $db->writes !== []) {
        throw new \RuntimeException('Clearing all starter positions must remain possible.');
    }

    echo "Joomla 5/6 administrator lineup/referee ID validation OK\n";
}
