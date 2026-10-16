<?php
/**
 * Joomla 5/6 regression checks for frontend match mutation input integrity.
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
        public static function add(string $message, int $level, string $category): void {}
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Service/MatchMutationService.php';

    final class InputIntegrityQuery {
        public function select(mixed $selection): self { return $this; }
        public function from(string $table): self { return $this; }
        public function join(string $kind, string $join): self { return $this; }
        public function where(string $where): self { return $this; }
        public function bind(string $key, mixed $value, string $type): self { return $this; }
    }

    final class InputIntegrityDb implements \Joomla\Database\DatabaseInterface {
        public int $queries = 0;
        public int $insertCount = 0;
        public int $transactions = 0;
        public int $commits = 0;
        public int $rollbacks = 0;
        public array $records = [];
        public function createQuery(): InputIntegrityQuery {
            ++$this->queries;
            return new InputIntegrityQuery();
        }
        public function quoteName(string $name, ?string $alias = null): string {
            return $alias === null ? $name : $name . ' ' . $alias;
        }
        public function setQuery(InputIntegrityQuery $query, int $offset = 0, int $limit = 0): void {}
        public function loadResult(): int { return 0; }
        public function loadObjectList(): array { return []; }
        public function insertObject(string $table, object $row): void {
            ++$this->insertCount;
            $this->records[] = [$table, clone $row];
        }
        public function insertid(): int { return $this->insertCount + 100; }
        public function transactionStart(): void { ++$this->transactions; }
        public function transactionCommit(): void { ++$this->commits; }
        public function transactionRollback(): void { ++$this->rollbacks; }
    }

    $db = new InputIntegrityDb();
    $service = new \Diddipoeler\Component\SportsManagement\Site\Service\MatchMutationService(
        $db, $db, 7, '2026-10-16 08:00:00'
    );
    $validate = new \ReflectionMethod($service, 'positiveId');
    foreach ([
        [11, false, 11], ['11', false, 11], [0, false, null],
        [0, true, 0], ['0', true, 0], ['11', true, 11],
        ['011', false, null], ['11junk', false, null], ['1e2', false, null],
        [[11], false, null], [true, false, null], [1.5, false, null],
        ['999999999999999999999999999', false, null], [-1, true, null],
        ['', true, null],
    ] as [$value, $allowZero, $expected]) {
        if ($validate->invoke(null, $value, $allowZero) !== $expected) {
            throw new \RuntimeException('Malformed mutation ID was accepted: ' . json_encode($value));
        }
    }

    $event = [
        'match_id' => 11, 'projectteam_id' => 17, 'teamplayer_id' => 19,
        'event_type_id' => 3, 'event_sum' => '1', 'event_time' => '38',
        'notice' => '', 'notes' => '', 'doubleevents' => 1,
    ];
    foreach ([
        ['match_id' => 0], ['match_id' => [11]], ['match_id' => '11junk'],
        ['projectteam_id' => -1], ['projectteam_id' => true],
        ['event_type_id' => '3oops'], ['teamplayer_id' => ['19']],
        ['event_sum' => ['1']], ['event_time' => ['38']],
        ['notice' => ['bad']], ['notes' => (object) ['bad' => true]],
    ] as $bad) {
        if ($service->saveEvent(array_replace($event, $bad)) !== false) {
            throw new \RuntimeException('Malformed frontend event was accepted.');
        }
    }

    $substitution = [
        'in' => 21, 'out' => 22, 'matchid' => 11,
        'project_position_id' => 3, 'in_out_time' => '56',
    ];
    foreach ([
        ['in' => [21]], ['in' => '21junk'], ['out' => '022'],
        ['matchid' => 0], ['matchid' => true],
        ['project_position_id' => ['3']], ['in_out_time' => ['56']],
        ['in_out_time' => ''],
    ] as $bad) {
        if ($service->saveSubstitution(array_replace($substitution, $bad)) !== false) {
            throw new \RuntimeException('Malformed frontend substitution was accepted.');
        }
    }

    $comment = ['match_id' => 11, 'event_time' => '56', 'notes' => 'Note', 'type' => 'plain'];
    foreach ([
        ['match_id' => 0], ['match_id' => ['11']], ['match_id' => '11x'],
        ['event_time' => ['56']], ['notes' => ['Note']], ['type' => ['plain']],
    ] as $bad) {
        if ($service->saveComment(array_replace($comment, $bad)) !== false) {
            throw new \RuntimeException('Malformed frontend commentary was accepted.');
        }
    }

    if ($db->queries !== 0 || $db->insertCount !== 0 || $db->transactions !== 0) {
        throw new \RuntimeException('Malformed frontend inputs reached the database.');
    }

    // A legitimate event without a player (team-level event) remains supported.
    $event['teamplayer_id'] = 0;
    if ($service->saveEvent($event) !== 101
        || $db->records[0][0] !== '#__sportsmanagement_match_event'
        || $db->records[0][1]->teamplayer_id !== 0
        || $db->transactions !== 1 || $db->commits !== 1) {
        throw new \RuntimeException('Valid team-level events must still be persisted.');
    }

    if ($service->saveSubstitution($substitution) !== true
        || $db->records[1][0] !== '#__sportsmanagement_match_player'
        || $db->records[1][1]->match_id !== 11) {
        throw new \RuntimeException('Valid substitutions were unexpectedly rejected.');
    }

    if ($service->saveComment($comment) !== 103
        || $db->records[2][0] !== '#__sportsmanagement_match_commentary'
        || $db->records[2][1]->match_id !== 11) {
        throw new \RuntimeException('Valid commentary was unexpectedly rejected.');
    }

    echo "Joomla 5/6 frontend event, substitution and comment inputs OK\n";
}
