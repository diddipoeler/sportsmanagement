<?php
/**
 * Joomla 5/6 strict match/position IDs and bounded quote output regression.
 *
 * This standalone fixture uses fake Joomla database services and converts all
 * PHP warnings into exceptions on PHP 8.3 and PHP 8.4.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Keep translated match outcome labels deterministic.
         * @param string $key Requested Joomla language key.
         * @return string The test translation marker.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\Database {
    interface DatabaseInterface {}

    final class ParameterType
    {
        public const INTEGER = 1;
    }
}

namespace {
    define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/site/src/Helper/MatchResultHelper.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Service/XmlPositionEventTypeImportService.php';
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_rquotes/src/Helper/QuoteText.php';

    /** Standalone Joomla query fixture for an existing position/event relation. */
    final class PositionRelationQueryFixture
    {
        public array $bindings = [];

        /**
         * Accept the selected relation ID column.
         * @param string $identifier SQL column.
         * @return self Fluent query fixture.
         */
        public function select(string $identifier): self { return $this; }

        /**
         * Accept the existing relation table.
         * @param string $table Joomla table identifier.
         * @return self Fluent query fixture.
         */
        public function from(string $table): self { return $this; }

        /**
         * Accept a bound equality filter.
         * @param string $condition SQL condition.
         * @return self Fluent query fixture.
         */
        public function where(string $condition): self { return $this; }

        /**
         * Record one bound position or event identifier.
         * @param string $parameter Parameter placeholder.
         * @param int $value Validated relation ID.
         * @param int $type Joomla integer binding type.
         * @return self Fluent query fixture.
         */
        public function bind(string $parameter, int $value, int $type): self
        {
            $this->bindings[$parameter] = $value;
            return $this;
        }
    }

    /** In-memory position/event relation database for XML import tests. */
    final class PositionRelationDatabaseFixture implements \Joomla\Database\DatabaseInterface
    {
        public ?PositionRelationQueryFixture $query = null;
        public int $queries = 0;
        public array $inserted = [];
        public bool $rejectInsert = false;

        /**
         * Create a recordable Joomla query.
         * @return PositionRelationQueryFixture Query spy.
         */
        public function createQuery(): PositionRelationQueryFixture
        {
            ++$this->queries;
            return new PositionRelationQueryFixture();
        }

        /**
         * Return the identifier unchanged so fixtures are easy to inspect.
         * @param string $identifier SQL identifier.
         * @return string Same SQL identifier.
         */
        public function quoteName(string $identifier): string
        {
            return $identifier;
        }

        /**
         * Store the latest selection request.
         * @param PositionRelationQueryFixture $query Query containing bound IDs.
         * @param int $offset Starting record index.
         * @param int $limit Maximum record count.
         * @return void
         */
        public function setQuery(PositionRelationQueryFixture $query, int $offset = 0, int $limit = 0): void
        {
            $this->query = $query;
        }

        /**
         * Report whether the current relation was previously inserted.
         * @return int|null Existing relation identifier or null.
         */
        public function loadResult(): ?int
        {
            $position = $this->query?->bindings[':positionId'] ?? null;
            $event = $this->query?->bindings[':eventId'] ?? null;

            foreach ($this->inserted as $index => $row) {
                if ($row->position_id === $position && $row->eventtype_id === $event) {
                    return $index + 1;
                }
            }

            return null;
        }

        /**
         * Simulate insertion or explicit failure without a Joomla database.
         * @param string $table Requested SportsManagement relationship table.
         * @param object $record Validated relation record.
         * @return bool Whether the fixture accepted the insertion.
         */
        public function insertObject(string $table, object $record): bool
        {
            if ($table !== '#__sportsmanagement_position_eventtype') {
                throw new \RuntimeException('Importer tried to write an unrelated table.');
            }

            if ($this->rejectInsert) {
                return false;
            }

            $this->inserted[] = $record;
            return true;
        }
    }

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $result = \Diddipoeler\Component\SportsManagement\Site\Helper\MatchResultHelper::class;
        $game = (object) [
            'projectteam1_id' => '00010',
            'projectteam2_id' => '20',
            'team1_result' => '3',
            'team2_result' => '1',
        ];

        if ($result::outcome($game, 10) !== 1
            || $result::outcome($game, 20) !== -1
            || $result::outcome($game, 30) !== false
            || !str_contains($result::renderOutcomeIcon($game, 10), 'fa-thumbs-up')) {
            throw new \RuntimeException('Valid home and away match results changed.');
        }

        foreach ([
            ['10junk', 10],
            [['10'], 10],
            [true, 1],
            [10.0, 10],
            ['-10', 10],
            [' 10', 10],
            [PHP_INT_MAX . '0', PHP_INT_MAX],
            [str_repeat('9', 150), 10],
            ['0000', 10],
        ] as [$invalid, $requested]) {
            $game->projectteam1_id = $invalid;

            if ($result::outcome($game, $requested) !== false
                || $result::renderOutcomeIcon($game, $requested) !== '') {
                throw new \RuntimeException('Malformed home team ID was partially cast to a match owner.');
            }
        }

        $game->projectteam1_id = 10;
        foreach ([['20junk'], [['20']], [false], [20.0], [PHP_INT_MAX . '0']] as [$invalid]) {
            $game->projectteam2_id = $invalid;

            if ($result::outcome($game, 20) !== false) {
                throw new \RuntimeException('Malformed away team ID was partially cast.');
            }
        }

        $database = new PositionRelationDatabaseFixture();
        $importer = new \Diddipoeler\Component\SportsManagement\Administrator\Service\XmlPositionEventTypeImportService($database);
        $validPost = ['dbPositionID_0' => '0007', 'dbEventID_0' => '0009'];
        $validData = [
            'position' => [(object) ['id' => '0005']],
            'event' => [(object) ['id' => '0003']],
            'positioneventtype' => [
                (object) ['position_id' => '0005', 'eventtype_id' => '0003'],
                (object) ['position_id' => 5, 'eventtype_id' => 3],
            ],
        ];

        $importer->import($validPost, $validData);

        if ($database->queries !== 2 || count($database->inserted) !== 1
            || $database->inserted[0]->position_id !== 7
            || $database->inserted[0]->eventtype_id !== 9) {
            throw new \RuntimeException('Valid imported position/event links were not preserved or deduplicated.');
        }

        // Invalid nested or partial values in either source or form fields
        // must never query or insert relationships for unrelated records.
        $invalidSamples = [
            [
                ['dbPositionID_0' => ['7'], 'dbEventID_0' => 9],
                $validData,
            ],
            [
                ['dbPositionID_0' => '7junk', 'dbEventID_0' => 9],
                $validData,
            ],
            [
                ['dbPositionID_0' => '9999999999999999999999999', 'dbEventID_0' => 9],
                $validData,
            ],
            [
                $validPost,
                [
                    'position' => [(object) ['id' => ['5']]],
                    'event' => [(object) ['id' => 3]],
                    'positioneventtype' => [(object) ['position_id' => 5, 'eventtype_id' => 3]],
                ],
            ],
            [
                $validPost,
                [
                    'position' => [(object) ['id' => 5]],
                    'event' => [(object) ['id' => true]],
                    'positioneventtype' => [(object) ['position_id' => 5, 'eventtype_id' => 3]],
                ],
            ],
            [
                $validPost,
                [
                    'position' => [(object) ['id' => 5]],
                    'event' => [(object) ['id' => 3]],
                    'positioneventtype' => [(object) ['position_id' => ['5'], 'eventtype_id' => 3]],
                ],
            ],
            [
                $validPost,
                [
                    'position' => [(object) ['id' => 5]],
                    'event' => [(object) ['id' => 3]],
                    'positioneventtype' => [(object) ['position_id' => 5, 'eventtype_id' => '3junk']],
                ],
            ],
        ];

        foreach ($invalidSamples as [$post, $data]) {
            $oldQueries = $database->queries;
            $oldWrites = count($database->inserted);
            $importer->import($post, $data);

            if ($database->queries !== $oldQueries || count($database->inserted) !== $oldWrites) {
                throw new \RuntimeException('Malformed XML relation or form ID reached database.');
            }
        }

        $failingDatabase = new PositionRelationDatabaseFixture();
        $failingDatabase->rejectInsert = true;
        $failingImport = new \Diddipoeler\Component\SportsManagement\Administrator\Service\XmlPositionEventTypeImportService($failingDatabase);

        try {
            $failingImport->import($validPost, $validData);
            throw new \RuntimeException('XML relation insertion failure was silently ignored.');
        } catch (\RuntimeException $error) {
            if ($error->getCode() !== 500
                || !str_contains($error->getMessage(), 'Unable to store imported')) {
                throw $error;
            }
        }

        $quote = \Diddipoeler\Module\SportsManagementRquotes\Site\Helper\QuoteText::class;

        foreach ([INF, -INF, NAN, str_repeat('A', 65537)] as $invalid) {
            if ($quote::render($invalid) !== '') {
                throw new \RuntimeException('Unsafe oversized or non-finite quote was displayed.');
            }
        }

        if ($quote::render('Line<br>second') !== "Line<br>\nsecond"
            || $quote::render('A & B') !== 'A &amp; B'
            || $quote::render('<b>Safe</b>') !== 'Safe'
            || $quote::render(7) !== '7'
            || $quote::render(str_repeat('X', 65536)) !== str_repeat('X', 65536)) {
            throw new \RuntimeException('Valid quotation formatting was changed.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 strict relation IDs and bounded quotation regressions passed.\n";
}
