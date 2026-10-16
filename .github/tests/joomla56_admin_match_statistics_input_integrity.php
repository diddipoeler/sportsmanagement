<?php
/**
 * Joomla 5/6 regression for validated administrator match statistics writes.
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

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Service/MatchWriteService.php';

    use Diddipoeler\Component\SportsManagement\Administrator\Service\MatchWriteService;

    final class StatisticsQuery {
        public string $table = '';
        public array $where = [];

        public function delete(string $table): self {
            $this->table = $table;
            return $this;
        }
        public function where(string $clause): self {
            $this->where[] = $clause;
            return $this;
        }
    }

    final class StatisticsDatabase implements \Joomla\Database\DatabaseInterface {
        public int $queryCount = 0;
        public int $started = 0;
        public int $committed = 0;
        public int $rolledBack = 0;
        public array $actions = [];
        public array $rows = [];
        private ?StatisticsQuery $query = null;
        private array $originalRows = [];

        public function createQuery(): StatisticsQuery {
            ++$this->queryCount;
            return new StatisticsQuery();
        }
        public function quoteName(string $name): string { return $name; }
        public function setQuery(StatisticsQuery $query): self {
            $this->query = $query;
            return $this;
        }
        public function execute(): void {
            $this->actions[] = ['delete', $this->query?->table, $this->query?->where];
        }
        public function insertObject(string $table, object $row): void {
            $this->rows[] = [$table, clone $row];
            $this->actions[] = ['insert', $table];
        }
        public function transactionStart(): void {
            ++$this->started;
            $this->originalRows = $this->rows;
        }
        public function transactionCommit(): void { ++$this->committed; }
        public function transactionRollback(): void {
            ++$this->rolledBack;
            $this->rows = $this->originalRows;
        }
    }

    $base = [
        'match_id' => '91',
        'cid' => ['11'],
        'teamplayer_id' => ['11', '0'],
        'projectteam_id' => ['21', '0'],
        'stat11_2' => '7',
        'stat11_3' => '',
        'staffcid' => ['31'],
        'team_staff_id' => ['31'],
        'sprojectteam_id' => ['41'],
        'staffstat31_4' => '2',
    ];

    foreach ([
        ['match_id' => '91junk'],
        ['match_id' => [91]],
        ['match_id' => false],
        ['match_id' => '9999999999999999999999999'],
        ['teamplayer_id' => ['11garbage']],
        ['teamplayer_id' => [[11]]],
        ['teamplayer_id' => ['011']],
        ['teamplayer_id' => '11'],
        ['teamplayer_id' => ['999999999999999999999999999']],
        ['projectteam_id' => [['21']]],
        ['projectteam_id' => ['21junk']],
        ['projectteam_id' => []],
        ['stat11_2' => ['7']],
        ['stat11_2' => new \stdClass()],
        ['stat11_999999999999999999999' => '7'],
        ['team_staff_id' => ['31oops']],
        ['team_staff_id' => [true]],
        ['sprojectteam_id' => [['41']]],
        ['sprojectteam_id' => ['41oops']],
        ['staffstat31_4' => ['2']],
        ['staffstat31_999999999999999999999999999' => '2'],
    ] as $tamper) {
        $db = new StatisticsDatabase();
        $service = new MatchWriteService($db);
        if ($service->saveStatistics(array_replace($base, $tamper)) !== false
            || $db->queryCount !== 0
            || $db->started !== 0
            || $db->rows !== []
            || $db->actions !== []) {
            throw new \RuntimeException('Malformed statistics form modified the database: '
                . json_encode(array_keys($tamper)));
        }
    }

    // Legitimate form values remain compatible with Joomla's string array POST.
    $db = new StatisticsDatabase();
    $service = new MatchWriteService($db);
    if (!$service->saveStatistics($base)
        || $db->started !== 1 || $db->committed !== 1 || $db->rolledBack !== 0
        || count($db->rows) !== 2
        || $db->rows[0][0] !== '#__sportsmanagement_match_statistic'
        || $db->rows[1][0] !== '#__sportsmanagement_match_staff_statistic'
        || $db->rows[0][1]->match_id !== 91
        || $db->rows[0][1]->projectteam_id !== 21
        || $db->rows[0][1]->teamplayer_id !== 11
        || $db->rows[0][1]->statistic_id !== 2
        || $db->rows[1][1]->team_staff_id !== 31
        || $db->rows[1][1]->statistic_id !== 4
        || count(array_filter($db->actions, static fn (array $a): bool => $a[0] === 'delete')) !== 2) {
        throw new \RuntimeException('Valid player and staff statistic updates changed.');
    }

    // Placeholder player IDs (0) must remain harmless and write nothing.
    $db = new StatisticsDatabase();
    if (!(new MatchWriteService($db))->saveStatistics([
        'match_id' => 91, 'cid' => true,
        'teamplayer_id' => [0], 'projectteam_id' => [0],
    ]) || $db->queryCount !== 0 || $db->started !== 1
        || $db->rows !== []) {
        throw new \RuntimeException('Placeholder statistic IDs must remain a no-op.');
    }

    echo "Joomla 5/6 administrator match statistic input integrity OK\n";
}
