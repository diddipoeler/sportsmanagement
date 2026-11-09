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
         * @param int $value Project identifier.
         * @param string $type Parameter type.
         * @return static Fluent query object.
         */
        public function bind(string $key, int $value, string $type): static { return $this; }
    }

    /** Fake round database containing three distinct project rounds. */
    final class SmRoundDatabase implements \Joomla\Database\DatabaseInterface
    {
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
        public function quoteName(string $name): string { return $name; }

        /**
         * Accept the completed round lookup SQL.
         *
         * @param SmRoundQuery $query Query instance.
         * @return void
         */
        public function setQuery(SmRoundQuery $query): void {}

        /**
         * Return the three known rounds in ascending roundcode order.
         *
         * @return array<int,object> Round records.
         */
        public function loadObjectList(): array
        {
            return [
                (object) ['id' => 5, 'roundcode' => 1],
                (object) ['id' => 8, 'roundcode' => 2],
                (object) ['id' => 13, 'roundcode' => 3],
            ];
        }
    }

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
