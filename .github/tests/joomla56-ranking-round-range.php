<?php
/**
 * Regression checks for Joomla 5/6 ranking round-range selection.
 *
 * Run: php .github/tests/joomla56-ranking-round-range.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    /** Minimal database contract needed by the ranking data loader. */
    interface DatabaseInterface {}

    /** Bind types accepted by the read-only ranking query. */
    final class ParameterType
    {
        public const INTEGER = 'integer';
        public const STRING = 'string';
    }
}

namespace Joomla\Registry {
    /** Optional registry placeholder for loading the data-loader class. */
    class Registry {}
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);

    /** Fluent round query for the isolated Joomla database test fixture. */
    final class SmRoundQuery
    {
        /**
         * Record selected round columns without accessing SQL.
         *
         * @param mixed $fields Requested fields.
         * @return static Fluent query object.
         */
        public function select(mixed $fields): static { return $this; }

        /**
         * Accept the component round table name.
         *
         * @param string $table Table identifier.
         * @return static Fluent query object.
         */
        public function from(string $table): static { return $this; }

        /**
         * Accept a SQL join from the ranking match loader.
         *
         * @param string $type Join type.
         * @param string $condition Joined table and condition.
         * @return static Fluent query object.
         */
        public function join(string $type, string $condition): static { return $this; }

        /**
         * Accept a project-scoped filter expression.
         *
         * @param string $clause SQL where clause.
         * @return static Fluent query object.
         */
        public function where(string $clause): static { return $this; }

        /**
         * Accept the project ID bind parameter.
         *
         * @param string $key Named SQL placeholder.
         * @param mixed $value SQL bind value.
         * @param string $type Parameter type.
         * @return static Fluent query object.
         */
        public function bind(string $key, mixed $value, string $type): static { return $this; }
    }

    /** Fake round database containing three distinct project rounds. */
    final class SmRoundDatabase implements \Joomla\Database\DatabaseInterface
    {
        public int $queryCount = 0;

        public bool $useMatchFixture = false;

        public bool $useTeamFallbackFixture = false;

        public int $teamFallbackAtQuery = 3;

        public mixed $savedTemplateParams = null;

        /**
         * Create an in-memory fluent query for round lookup.
         *
         * @param bool $new Whether the Joomla query is new.
         * @return SmRoundQuery Test query instance.
         */
        public function getQuery(bool $new = true): SmRoundQuery
        {
            return new SmRoundQuery();
        }

        /**
         * Return an identifier unchanged for the query fixture.
         *
         * @param string $name Column name.
         * @return string The same field name.
         */
        public function quoteName(string $name, ?string $alias = null): string
        {
            return $alias === null ? $name : $name . ' AS ' . $alias;
        }

        /**
         * Accept the completed round lookup SQL.
         *
         * @param SmRoundQuery $query Query instance.
         * @param int $offset Optional query offset.
         * @param int $limit Optional query row limit.
         * @return void
         */
        public function setQuery(SmRoundQuery $query, int $offset = 0, int $limit = 0): void
        {
            ++$this->queryCount;
        }

        /**
         * Return synthetic stored template parameters from the database.
         *
         * @return mixed Database query result for the params column.
         */
        public function loadResult(): mixed { return $this->savedTemplateParams; }

        /**
         * Return the three known rounds in ascending roundcode order.
         *
         * @return array<int,object> Round records.
         */
        public function loadObjectList(): array
        {
            if ($this->useTeamFallbackFixture) {
                // Ordinary and division-final queries return no teams.
                // The third, unscoped query would return a team from a
                // different division, which must be skipped for division views.
                return $this->queryCount >= $this->teamFallbackAtQuery
                    ? [
                        (object) ['projectteamid' => 999, 'id' => 42, 'division_id' => 91, 'name' => 'Other division'],
                        (object) ['projectteamid' => ['malformed']],
                        (object) ['projectteamid' => 'not-a-number'],
                        (object) ['projectteamid' => -1],
                        (object) ['name' => 'Missing project-team ID'],
                        'unexpected scalar',
                    ]
                    : [];
            }

            if ($this->useMatchFixture) {
                return [
                    (object) ['id' => 101, 'roundid' => 5],
                    (object) ['id' => '102', 'roundid' => 8],
                    (object) ['id' => ['invalid'], 'roundid' => 13],
                    (object) ['id' => new \stdClass(), 'roundid' => 13],
                    (object) ['id' => 'nonnumeric', 'roundid' => 13],
                    (object) ['id' => -1, 'roundid' => 13],
                    (object) ['roundid' => 13],
                    'unexpected scalar',
                ];
            }

            return [
                (object) ['id' => 5, 'roundcode' => 1],
                (object) ['id' => 8, 'roundcode' => 2],
                (object) ['id' => 13, 'roundcode' => 3],
                (object) ['id' => ['nested'], 'roundcode' => 4],
                (object) ['id' => 20, 'roundcode' => new \stdClass()],
                (object) ['id' => 'bad-id', 'roundcode' => 4],
                (object) ['roundcode' => 5],
                'unexpected scalar',
            ];
        }
    }

    require_once dirname(__DIR__, 2) . '/site/src/Service/RankingRow.php';
    require_once dirname(__DIR__, 2) . '/site/src/Service/RankingDataLoader.php';

    use Diddipoeler\Component\SportsManagement\Site\Service\RankingDataLoader;

    $loader = new RankingDataLoader(new SmRoundDatabase());
    $filter = new \ReflectionMethod(RankingDataLoader::class, 'filterRoundRange');
    $matches = [
        101 => (object) ['roundid' => 5],
        102 => (object) ['roundid' => 8],
        103 => (object) ['roundid' => 13],
    ];

    /**
     * Assert the round-scoped match keys match exactly the expected selection.
     *
     * @param array<int,int> $expectedMatchIds Expected match ID keys.
     * @param array<int,object> $matches Original keyed fixtures.
     * @param int $from Optional first round ID.
     * @param int $to Optional last round ID.
     * @return void
     */
    function smAssertRankingRoundRange(array $expectedMatchIds, array $matches, int $from, int $to): void
    {
        $loader = $GLOBALS['smRoundRangeLoader'];
        $filter = new \ReflectionMethod(RankingDataLoader::class, 'filterRoundRange');
        $result = $filter->invoke($loader, $matches, 1, $from, $to);

        if (array_keys($result) !== $expectedMatchIds) {
            throw new \RuntimeException('Ranking round range selected incorrect match IDs.');
        }
    }

    $GLOBALS['smRoundRangeLoader'] = $loader;
    smAssertRankingRoundRange([101, 102, 103], $matches, 0, 0);
    smAssertRankingRoundRange([102, 103], $matches, 8, 0);
    smAssertRankingRoundRange([101, 102], $matches, 0, 8);
    smAssertRankingRoundRange([102], $matches, 8, 8);
    smAssertRankingRoundRange([], $matches, 999, 0);
    smAssertRankingRoundRange([], $matches, 0, 999);
    smAssertRankingRoundRange([], $matches, 13, 5);

    // Under PHP 8.2–8.4, unexpected database field types must not emit
    // warnings or notices that could pollute the frontend response.
    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $corruptMatches = $matches + [
        104 => (object) ['roundid' => ['unexpected']],
        105 => (object) ['roundid' => new \stdClass()],
        106 => (object) ['roundid' => 'not-a-round'],
        107 => (object) ['name' => 'missing round'],
        108 => 'unexpected scalar',
    ];
        smAssertRankingRoundRange([101, 102], $corruptMatches, 0, 8);
    } finally {
        restore_error_handler();
    }

    $db = new SmRoundDatabase();
    $emptyLoader = new RankingDataLoader($db);
    $emptySelection = $filter->invoke($emptyLoader, [], 1, 5, 8);

    if ($emptySelection !== [] || $db->queryCount !== 0) {
        throw new \RuntimeException('Empty ranking match lists must not issue round queries.');
    }

    $matchDb = new SmRoundDatabase();
    $matchDb->useMatchFixture = true;
    $matchLoader = new RankingDataLoader($matchDb);
    $loadMatches = new \ReflectionMethod(RankingDataLoader::class, 'loadMatches');

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $loaded = $loadMatches->invoke($matchLoader, 1, 0, 'COM_SPORTSMANAGEMENT_ST_SOCCER');
    } finally {
        restore_error_handler();
    }

    if (array_keys($loaded) !== [101, 102] || $matchDb->queryCount !== 1) {
        throw new \RuntimeException('Ranking loader accepted invalid match identifiers.');
    }

    // An unexpected nested result from a custom DB adapter must not
    // trigger a PHP 8 array-to-string conversion warning.
    $paramsDb = new SmRoundDatabase();
    $paramsLoader = new RankingDataLoader($paramsDb);
    $savedParams = new \ReflectionMethod(RankingDataLoader::class, 'loadSavedTemplateParams');

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        foreach ([null, [], ['bad' => 'nested'], new \stdClass()] as $invalid) {
            $paramsDb->savedTemplateParams = $invalid;

            if ($savedParams->invoke($paramsLoader, 'ranking', 1) !== null) {
                throw new \RuntimeException('Malformed saved ranking settings were accepted.');
            }
        }

        $paramsDb->savedTemplateParams = '{"ranking_order":"POINTS"}';

        if ($savedParams->invoke($paramsLoader, 'ranking', 1) !== '{"ranking_order":"POINTS"}') {
            throw new \RuntimeException('Valid ranking settings were not retained.');
        }
    } finally {
        restore_error_handler();
    }

    $divisionDb = new SmRoundDatabase();
    $divisionDb->useTeamFallbackFixture = true;
    $divisionLoader = new RankingDataLoader($divisionDb);
    $loadTeams = new \ReflectionMethod(RankingDataLoader::class, 'loadTeams');

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $divisionTeams = $loadTeams->invoke($divisionLoader, 10, 12);

        if ($divisionTeams !== [] || $divisionDb->queryCount !== 2) {
            throw new \RuntimeException('Division ranking loaded unrelated project-wide final teams.');
        }

        $projectDb = new SmRoundDatabase();
        $projectDb->useTeamFallbackFixture = true;
        $projectDb->teamFallbackAtQuery = 2;
        $projectLoader = new RankingDataLoader($projectDb);
        $projectTeams = $loadTeams->invoke($projectLoader, 10, 0);
    } finally {
        restore_error_handler();
    }

    if (array_keys($projectTeams) !== [999] || $projectDb->queryCount !== 2) {
        throw new \RuntimeException('Project-wide final standings fallback was lost.');
    }

    $parseColors = new \ReflectionMethod(RankingDataLoader::class, 'parseColors');
    $rules = $parseColors->invoke($loader, '1,3,#00FF00,Promotion;4,5,#FF0000,Relegation');

    if (count($rules) !== 2
        || $rules[0]['color'] !== '#00FF00'
        || $rules[1]['description'] !== 'Relegation') {
        throw new \RuntimeException('Legacy ranking colour rules were not preserved.');
    }

    foreach ([null, false, 42, [], ['unexpected' => 'nested'], new \stdClass()] as $invalid) {
        if ($parseColors->invoke($loader, $invalid) !== []) {
            throw new \RuntimeException('Malformed ranking colour settings were not rejected.');
        }
    }

    echo "Joomla 5/6 ranking round-range validation passed.\n";
}
