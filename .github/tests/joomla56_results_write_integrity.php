<?php
/**
 * Joomla 5/6 compact frontend result-write integrity regression.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Factory {
    interface MVCFactoryInterface {}
}
namespace Joomla\CMS\Language {
    class Text {
        public static function _(string $name): string { return $name; }
        public static function sprintf(string $name, mixed ...$args): string { return $name; }
    }
}
namespace Joomla\CMS\Log {
    class Log {
        public const ERROR = 400;
        public static array $errors = [];
        public static function add(string $message, int $level, string $category): void {
            self::$errors[] = $message;
        }
    }
}
namespace Joomla\Database {
    class ParameterType {
        public const INTEGER = 'integer';
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper {
    class SportsManagementDateHelper {
        public static function toSqlDate(string $value): ?string {
            return $value;
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementModel {
        public static ?object $db = null;
        public static ?object $app = null;
        public function __construct($config = [], ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null) {}
        protected function siteApplication(): object { return self::$app; }
        protected function getDatabase(): object { return self::$db; }
    }
}
namespace {
    define('_JEXEC', 1);

    class QueryStub {
        public mixed $selection = null;
        public array $bindings = [];
        public function select(mixed $selection): self {
            $this->selection = $selection;
            return $this;
        }
        public function from(string $table): self { return $this; }
        public function where(string $expr): self { return $this; }
        public function join(string $type, string $expr): self { return $this; }
        public function bind(string $key, mixed $value, mixed $kind): self {
            $this->bindings[$key] = $value;
            return $this;
        }
    }
    class DbStub {
        private ?QueryStub $current = null;
        public array $saved = [];
        public array $queries = [];
        public bool $writeFailure = false;
        public array $legMatches = [101 => true, 102 => false];
        public function createQuery(): QueryStub { return new QueryStub(); }
        public function quoteName(string $name, string $alias = ''): string {
            return $name;
        }
        public function setQuery(QueryStub $query, int $offset = 0, int $limit = -1): self {
            $this->current = $query;
            $this->queries[] = $query;
            return $this;
        }
        public function loadResult(): mixed {
            if (str_contains((string) $this->current->selection, 'use_legs')) {
                return (int) ($this->legMatches[(int) ($this->current->bindings[':matchId'] ?? 0)] ?? false);
            }
            return '2026-10-10 16:00:00';
        }
        public function updateObject(string $table, object $record, string $key, bool $nulls = false): void {
            if ($this->writeFailure) {
                throw new \RuntimeException('Sensitive DB password abc-private-xyz');
            }
            if ($table !== '#__sportsmanagement_match' || $key !== 'id') {
                throw new \RuntimeException('Unexpected result-table update.');
            }
            $this->saved[] = clone $record;
        }
    }
    class PostInputStub {
        public function getArray(): array { return []; }
        public function get(string $key, mixed $default, string $filter): mixed {
            return $default;
        }
    }
    class ApplicationStub {
        public object $post;
        public array $messages = [];
        public function __construct() { $this->post = new PostInputStub(); }
        public function getInput(): self { return $this; }
        public function enqueueMessage(string $key, string $type = 'message'): void {
            $this->messages[] = ['key' => $key, 'type' => $type];
        }
    }

    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Model/ResultsEditModel.php';

    $base = \Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementModel::class;
    $db = new DbStub();
    $app = new ApplicationStub();
    $base::$db = $db;
    $base::$app = $app;
    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\ResultsEditModel();

    $payload = [
        // These untrusted fields MUST NOT select competition scoring rules
        // or move an authorized match to another league/team/project.
        'use_legs' => 0,
        'round_id101' => 999,
        'division_id101' => 999,
        'projectteam1_id101' => 999,
        'projectteam2_id101' => 999,
        'content_id101' => 999,
        'match_number101' => 'R1',
        'team1_result101' => 90,
        'team2_result101' => 90,
        'team1_result_split101' => ['4', '0'],
        'team2_result_split101' => ['2', '3'],
        'match_result_type101' => 2,
        'crowd101' => 150,
    ];
    if (!$model->saveShort($payload, [101, 101])) {
        throw new \RuntimeException('Cannot save valid leg competition result.');
    }
    if (count($db->saved) !== 1) {
        throw new \RuntimeException('Duplicate selected match ID caused duplicate database writes.');
    }
    $row = $db->saved[0];
    if (($row->team1_result ?? null) !== 1 || ($row->team2_result ?? null) !== 1) {
        throw new \RuntimeException('Leg scoring trusted client-supplied use_legs instead of the project.');
    }
    if (($row->team1_result_split ?? '') !== '4;0'
        || ($row->team2_result_split ?? '') !== '2;3') {
        throw new \RuntimeException('Submitted split scores were not persisted.');
    }
    foreach (['round_id', 'division_id', 'projectteam1_id', 'projectteam2_id', 'content_id'] as $field) {
        if (property_exists($row, $field)) {
            throw new \RuntimeException('Results endpoint allowed reassignment of ' . $field);
        }
    }
    if (($row->match_number ?? null) !== 'R1' || ($row->crowd ?? null) !== 150) {
        throw new \RuntimeException('Legitimate result form data was discarded.');
    }

    $db->saved = [];
    // A tampered flag must also be unable to force leg-based score calculation.
    if (!$model->saveShort([
        'use_legs' => 1,
        'team1_result102' => '2',
        'team2_result102' => '1',
    ], [102])) {
        throw new \RuntimeException('Normal result write failed.');
    }
    $row = $db->saved[0];
    if (($row->team1_result ?? null) !== '2' || ($row->team2_result ?? null) !== '1') {
        throw new \RuntimeException('Normal result accepted forged leg competition mode.');
    }

    $db->saved = [];
    // Updating a match number without split fields must not reset leg scores.
    if (!$model->saveShort([
        'match_number101' => 'R2',
        'use_legs' => 0,
        'team1_result101' => 999,
    ], [101])) {
        throw new \RuntimeException('Partial leg form save failed.');
    }
    $row = $db->saved[0];
    foreach (['team1_result', 'team2_result', 'team1_result_split', 'team2_result_split'] as $field) {
        if (property_exists($row, $field)) {
            throw new \RuntimeException('Partial form cleared protected leg score field: ' . $field);
        }
    }
    $db->saved = [];
    $db->writeFailure = true;
    if ($model->saveShort(['match_number101' => 'R3'], [101])) {
        throw new \RuntimeException('Database write failure was reported as success.');
    }
    $last = end($app->messages);
    if (($last['key'] ?? '') !== 'JERROR_AN_ERROR_HAS_OCCURRED'
        || ($last['type'] ?? '') !== 'error') {
        throw new \RuntimeException('Database failure exposed internal details or lacked error feedback.');
    }
    foreach ($app->messages as $message) {
        if (str_contains($message['key'], 'abc-private-xyz')) {
            throw new \RuntimeException('Sensitive driver errors leaked into frontend messages.');
        }
    }
    echo "Joomla 5/6 compact results integrity and error handling OK\n";
}
