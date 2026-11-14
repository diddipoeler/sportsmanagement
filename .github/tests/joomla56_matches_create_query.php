<?php
/**
 * Joomla 5/6 regression for native matches query construction without getQuery.
 *
 * Both match-navigation and referee lookups must use createQuery(), which is
 * compatible with the Joomla database framework's newer query factory API.
 *
 * Usage: php .github/tests/joomla56_matches_create_query.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Store the module settings used by the test fixture.
         *
         * @param array<string,mixed> $items Module options.
         * @return void
         */
        public function __construct(private array $items = [])
        {
        }

        /**
         * Fetch a configured module value without Joomla services.
         *
         * @param string $key Module option identifier.
         * @param mixed $default Default for missing settings.
         * @return mixed Configured value or default.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->items[$key] ?? $default;
        }
    }
}

namespace {
    \define('_JEXEC', 1);
    // The production MatchesHelper combines the query, format and link traits.
    // The formatter's referee input guards depend on NativeQueryTrait.
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_matches/connectors/native/QueryTrait.php';
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_matches/connectors/native/FormatTrait.php';
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_matches/connectors/native/LinkTrait.php';

    final class MatchesQueryFixture
    {
        /**
         * Support the fluent SELECT operations used by referee and neighbour queries.
         *
         * @param mixed ...$arguments Query arguments are intentionally ignored.
         * @return self The same query fixture for method chaining.
         */
        public function select(mixed ...$arguments): self { return $this; }

        /**
         * Accept the fixture query's source table without execution.
         *
         * @param mixed ...$arguments Source table arguments.
         * @return self The query fixture.
         */
        public function from(mixed ...$arguments): self { return $this; }

        /**
         * Accept an optional join to a match or person table.
         *
         * @param mixed ...$arguments Join type and SQL expression.
         * @return self The query fixture.
         */
        public function join(mixed ...$arguments): self { return $this; }

        /**
         * Accept a WHERE clause without SQL evaluation.
         *
         * @param mixed ...$arguments Filter expression.
         * @return self The query fixture.
         */
        public function where(mixed ...$arguments): self { return $this; }

        /**
         * Accept chronological or name ordering for fixture rows.
         *
         * @param mixed ...$arguments Sort columns.
         * @return self The query fixture.
         */
        public function order(mixed ...$arguments): self { return $this; }
    }

    final class MatchesDatabaseFixture implements \Joomla\Database\DatabaseInterface
    {
        public int $created = 0;
        public int $executed = 0;

        /**
         * Create a fresh fluent database query without any getQuery method.
         *
         * @return MatchesQueryFixture New query fixture for Joomla lookups.
         */
        public function createQuery(): MatchesQueryFixture
        {
            ++$this->created;
            return new MatchesQueryFixture();
        }

        /**
         * Return the field name while preserving optional SQL aliases.
         *
         * @param string $name Requested column or table name.
         * @param string|null $alias Optional column alias.
         * @return string SQL identifier for the fixture.
         */
        public function quoteName(string $name, ?string $alias = null): string
        {
            return $alias === null ? $name : $name . ' AS ' . $alias;
        }

        /**
         * Record a query execution without connecting to a real database.
         *
         * @param object $query Query created by the fixture.
         * @param int $offset First result row for bounded queries.
         * @param int $limit Maximum requested rows.
         * @return void
         */
        public function setQuery(object $query, int $offset = 0, int $limit = 0): void
        {
            ++$this->executed;
        }

        /**
         * Simulate a referee query returning no assigned officials.
         *
         * @return array Empty referee data for this regression.
         */
        public function loadObjectList(): array
        {
            return [];
        }

        /**
         * Simulate a neighbouring match query with no earlier or later fixture.
         *
         * @return null No neighbouring fixture in the test database.
         */
        public function loadObject(): null
        {
            return null;
        }
    }

    final class MatchesQueryCompatibilityHarness
    {
        use \Diddipoeler\Module\SportsManagementMatches\Site\Helper\NativeQueryTrait;
        use \Diddipoeler\Module\SportsManagementMatches\Site\Helper\NativeFormatTrait;
        use \Diddipoeler\Module\SportsManagementMatches\Site\Helper\NativeLinkTrait;
    }

    $db = new MatchesDatabaseFixture();
    $helper = new MatchesQueryCompatibilityHarness();
    $params = new \Joomla\Registry\Registry(['nextlast_from_same_project' => 1]);
    $referees = new \ReflectionMethod($helper, 'refereesByMatch');
    $neighbors = new \ReflectionMethod($helper, 'neighborLinks');

    if ($referees->invoke($helper, $db, [8], 0) !== []) {
        throw new \RuntimeException('Empty referee lookup returned unexpected rows.');
    }

    $match = (object) ['project_id' => 11, 'match_timestamp' => 1700000000];
    $links = $neighbors->invoke($helper, $db, $match, $params, [11]);

    if ($links !== ['previous' => null, 'next' => null]
        || $db->created !== 3
        || $db->executed !== 3
        || method_exists($db, 'getQuery')) {
        throw new \RuntimeException('Matches native query factory compatibility broke.');
    }

    echo "Joomla 5/6 matches createQuery regressions passed.\n";
}
