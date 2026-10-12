<?php
/**
 * Joomla 5/6 tests for Team Stats Ranking routing and New Project service safety.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}

    final class ParameterType
    {
        public const INTEGER = 'integer';
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        public function __construct(private array $values = []) {}

        public function get(string $key, mixed $fallback = null): mixed
        {
            return $this->values[$key] ?? $fallback;
        }
    }
}

namespace Joomla\CMS {
    final class Factory
    {
        public static mixed $database = null;
        public static int $lookups = 0;

        public static function getContainer(): object
        {
            return new class {
                public function get(string $service): mixed
                {
                    ++Factory::$lookups;
                    return Factory::$database;
                }
            };
        }
    }
}

namespace {
    use Diddipoeler\Module\SportsManagementNewProject\Site\Helper\NewProjectHelper;
    use Diddipoeler\Module\SportsManagementTeamStatsRanking\Site\Helper\TeamStatsRankingHelper;
    use Joomla\CMS\Factory;
    use Joomla\Database\DatabaseInterface;
    use Joomla\Registry\Registry;

    define('_JEXEC', 1);

    $root = dirname(__DIR__, 2);
    require_once $root . '/modules/mod_sportsmanagement_teamstats_ranking/src/Helper/TeamStatsRankingHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_new_project/src/Helper/NewProjectHelper.php';

    function assertExpected(mixed $expected, mixed $actual, string $message): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException(
                $message . '. Expected ' . var_export($expected, true)
                . '; got ' . var_export($actual, true)
            );
        }
    }

    final class QueryStub
    {
        public array $columns = [];
        public array $bindings = [];
        public array $whereIn = [];

        public function select(array|string $fields): self
        {
            array_push($this->columns, ...(array) $fields);
            return $this;
        }

        public function from(string $table): self { return $this; }
        public function join(string $type, string $expression): self { return $this; }
        public function where(string $expression): self { return $this; }

        public function bind(string $key, mixed $value, string $type): self
        {
            $this->bindings[$key] = $value;
            return $this;
        }

        public function whereIn(string $column, array $values, string $type): self
        {
            $this->whereIn[$column] = $values;
            return $this;
        }
    }

    final class FakeDatabase implements DatabaseInterface
    {
        public int $queries = 0;
        public array $rows = [];
        public ?object $single = null;
        public QueryStub $query;

        public function createQuery(): QueryStub
        {
            return new QueryStub();
        }

        public function quoteName(string $name, ?string $alias = null): string
        {
            return $alias === null ? $name : $name . ' AS ' . $alias;
        }

        public function setQuery(QueryStub $query, int $offset = 0, int $limit = 0): void
        {
            $this->query = $query;
            ++$this->queries;
        }

        public function loadObject(): ?object
        {
            return $this->single === null ? null : clone $this->single;
        }

        public function loadObjectList(string $key = ''): array
        {
            $rows = array_map(static fn (object $row): object => clone $row, $this->rows);

            if ($key === 'id') {
                $result = [];
                foreach ($rows as $row) {
                    $result[(int) $row->id] = $row;
                }
                return $result;
            }

            return $rows;
        }
    }

    $helper = new TeamStatsRankingHelper();
    $extractId = new \ReflectionMethod(TeamStatsRankingHelper::class, 'extractId');

    foreach ([
        [12, 12],
        ['12', 12],
        ['0012:club-alias', 12],
        ['12:spieler_elf', 12],
        [' 12:fußball ', 12],
        [['12:club'], 12],
        [null, 0],
        [true, 0],
        [12.75, 0],
        [['12', '13'], 0],
        ['12suffix', 0],
        ['12:bad:slug', 0],
        ['12:bad alias', 0],
        ['12,13', 0],
        ['-12', 0],
        ['12.0', 0],
        ['999999999999999999999999999999999', 0],
    ] as [$candidate, $expected]) {
        assertExpected($expected, $extractId->invoke($helper, $candidate),
            'Team Stats Ranking incorrectly parsed an ID selector');
    }

    $db = new FakeDatabase();
    $empty = $helper->getData(new Registry(['p' => '12suffix', 'sid' => 5]), $db);
    assertExpected(null, $empty['project'], 'Malformed ID must return no project');
    assertExpected(0, $db->queries, 'Malformed IDs must not trigger SQL queries');

    $db->single = (object) [
        'id' => 12, 'alias' => 'championship', 'season_id' => 7,
        'season_alias' => 'season-2026',
    ];

    $loadProject = new \ReflectionMethod(TeamStatsRankingHelper::class, 'loadProject');
    $project = $loadProject->invoke($helper, $db, 12);
    assertExpected('12:championship', $project->slug, 'Project slug must be built in PHP');
    assertExpected('7:season-2026', $project->season_slug, 'Season slug must be built in PHP');
    assertExpected(12, $db->query->bindings[':projectId'] ?? null,
        'Project ID should be a bound integer');
    assertExpected(false, str_contains(implode(' ', $db->query->columns), 'CONCAT_WS'),
        'Project query must not use MySQL-only functions');

    $db->rows = [
        (object) ['id' => 17, 'alias' => 'my-team', 'club_id' => 19, 'club_alias' => 'my-club'],
        (object) ['id' => 18, 'alias' => 'other', 'club_id' => 0, 'club_alias' => null],
    ];

    $loadTeams = new \ReflectionMethod(TeamStatsRankingHelper::class, 'loadTeams');
    $teams = $loadTeams->invoke($helper, $db, [17, 18]);
    assertExpected('17:my-team', $teams[17]->team_slug,
        'Team slug must be built in PHP');
    assertExpected('19:my-club', $teams[17]->club_slug,
        'Club slug must be built in PHP');
    assertExpected('', $teams[18]->club_slug,
        'Teams without clubs must not be routed to club ID zero');
    assertExpected([17, 18], $db->query->whereIn['t.id'] ?? null,
        'Team identifiers must be bound in a database-independent query');
    assertExpected(false, str_contains(implode(' ', $db->query->columns), 'CONCAT_WS'),
        'Team query must not use MySQL-only functions');

    $newProject = new NewProjectHelper();
    $databaseResolver = new \ReflectionMethod(NewProjectHelper::class, 'joomlaDatabase');

    assertExpected($db, $databaseResolver->invoke($newProject, $db),
        'Injected database services must be retained');

    Factory::$database = new \stdClass();
    try {
        $databaseResolver->invoke($newProject, null);
        throw new \RuntimeException('Wrong Joomla database container type was accepted');
    } catch (\RuntimeException $exception) {
        assertExpected(500, $exception->getCode(),
            'Wrong Joomla database service type must produce a server error');
    }

    Factory::$database = $db;
    assertExpected($db, $databaseResolver->invoke($newProject, null),
        'A correctly typed container database must be accepted');

    $ajaxSource = file_get_contents(
        $root . '/modules/mod_sportsmanagement_new_project/src/Helper/NewProjectHelper.php'
    );
    assertExpected(true, $ajaxSource !== false
        && str_contains($ajaxSource, "Log::add(__METHOD__ . ': ' . \$e->getMessage()")
        && str_contains($ajaxSource, "'message' => 'Content article could not be saved.'")
        && !str_contains($ajaxSource, "'message' => \$e->getMessage()"),
        'AJAX article creation must log internal exceptions without leaking them to users');

    echo "Team Stats Ranking routing and New Project database contracts OK.\n";
}
