<?php
/**
 * Joomla 5/6 regressions for Project Map portability and Playground Plan filters.
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

        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}

namespace Joomla\CMS {
    final class Factory
    {
        public static int $lookups = 0;
        public static mixed $database = null;

        public static function getContainer(): object
        {
            ++self::$lookups;

            return new class {
                public function get(string $service): mixed
                {
                    return \Joomla\CMS\Factory::$database;
                }
            };
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        public static function resolve(
            \Joomla\Database\DatabaseInterface $fallbackDatabase,
            int $databaseSelector
        ): \Joomla\Database\DatabaseInterface {
            return $fallbackDatabase;
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $entries = [];

        public static function add(string $message, int $priority, string $category): void
        {
            self::$entries[] = [$message, $priority, $category];
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        public static function _(string $value): string
        {
            return $value;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    final class SiteRouteHelper
    {
        public static function view(string $view, array $arguments): string
        {
            return $view . '?' . http_build_query($arguments);
        }
    }
}

namespace {
    use Diddipoeler\Module\SportsManagementPlaygroundPlan\Site\Helper\PlaygroundPlanHelper;
    use Diddipoeler\Module\SportsManagementProjectMap\Site\Helper\ProjectMapHelper;
    use Joomla\CMS\Factory;
    use Joomla\CMS\Application\CMSApplicationInterface;
    use Joomla\Database\DatabaseInterface;
    use Joomla\Registry\Registry;

    define('_JEXEC', 1);

    $root = dirname(__DIR__, 2);
    require_once $root . '/modules/mod_sportsmanagement_projectmap/src/Helper/ProjectMapHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_playgroundplan/src/Helper/PlaygroundPlanHelper.php';

    function assertMatch(mixed $wanted, mixed $found, string $message): void
    {
        if ($wanted !== $found) {
            throw new \RuntimeException(
                $message . ': wanted ' . var_export($wanted, true)
                . ', found ' . var_export($found, true)
            );
        }
    }

    final class QueryStub
    {
        public array $selections = [];
        public array $whereIn = [];
        public array $joins = [];

        public function select(array|string $columns): self
        {
            array_push($this->selections, ...(array) $columns);
            return $this;
        }

        public function from(string $name): self { return $this; }
        public function join(string $kind, string $join): self { $this->joins[] = $join; return $this; }
        public function where(string $expression): self { return $this; }
        public function order(string $ordering): self { return $this; }

        public function whereIn(string $field, array $values, string $type): self
        {
            $this->whereIn[$field] = $values;
            return $this;
        }
    }

    final class MapDatabase implements DatabaseInterface
    {
        public int $queries = 0;
        public QueryStub $lastQuery;
        public array $rows = [];

        public function createQuery(): QueryStub
        {
            return new QueryStub();
        }

        public function quoteName(string $column, ?string $alias = null): string
        {
            return $alias === null ? $column : $column . ' AS ' . $alias;
        }

        public function setQuery(QueryStub $query): void
        {
            $this->lastQuery = $query;
            ++$this->queries;
        }

        public function loadObjectList(): array
        {
            return array_map(static fn (object $row): object => clone $row, $this->rows);
        }
    }

    $map = new ProjectMapHelper();
    $database = new MapDatabase();

    assertMatch([], $map->getData(['invalid'], $database),
        'Invalid map seasons must not retrieve projects');
    assertMatch(0, $database->queries, 'Invalid season filter unexpectedly queried the database');

    $database->rows = [
        (object) [
            'id' => 3, 'project_alias' => 'league-a', 'country' => 'DEU',
            'country_alpha2' => 'DE', 'country_federation' => 1,
            'federation_name' => 'UEFA', 'federation_picture' => 'uefa.png',
            'country_name' => 'Germany', 'country_picture' => 'de.png',
            'liganame' => 'League', 'name' => 'Old league', 'league_picture' => 'league.png',
        ],
        (object) [
            'id' => 8, 'project_alias' => 'league-new', 'country' => 'DEU',
            'country_alpha2' => 'DE', 'country_federation' => 1,
            'federation_name' => 'UEFA', 'federation_picture' => 'uefa.png',
            'country_name' => '<b>Germany</b>', 'country_picture' => 'de.png',
            'liganame' => '<img src=x onerror=alert(1)>',
            'name' => '<script>alert(1)</script>', 'league_picture' => 'league.png',
        ],
        (object) [
            'id' => 5, 'project_alias' => 'league-b', 'country' => 'FRA',
            'country_alpha2' => 'FR', 'country_federation' => 1,
            'federation_name' => 'UEFA', 'federation_picture' => 'uefa.png',
            'country_name' => 'France', 'country_picture' => 'fr.png',
            'liganame' => 'League B', 'name' => 'French league', 'league_picture' => 'league.png',
        ],
    ];

    $result = $map->getMapData([7], $database);
    assertMatch(1, $database->queries, 'Expected one map query');
    assertMatch([7], $database->lastQuery->whereIn['pro.season_id'] ?? null,
        'Map must bind valid integer season IDs');
    assertMatch(false, str_contains(implode(' ', $database->lastQuery->selections), 'CONCAT_WS'),
        'Map must avoid vendor-specific MySQL route expressions');
    assertMatch(true, in_array('pro.alias AS project_alias', $database->lastQuery->selections, true),
        'Map must request the project alias for PHP route creation');
    assertMatch(2, count($result['projects']), 'Map must select one project per country');
    assertMatch('8:league-new', $result['projects'][0]->project_slug,
        'Map must select highest project ID for Germany and preserve its alias');
    assertMatch('5:league-b', $result['projects'][1]->project_slug,
        'Map must generate France project slug in PHP');
    parse_str((string) parse_url($result['options']['state_specific']['DE']['url'], PHP_URL_QUERY), $route);
    assertMatch('8:league-new', $route['p'] ?? null,
        'Map routing must include the highest matching project ID:alias');
    assertMatch(true, str_contains($result['options']['state_specific']['DE']['name'], '&lt;b&gt;Germany&lt;/b&gt;'),
        'Map country text must be HTML-escaped.');
    assertMatch(true, str_contains($result['options']['state_specific']['DE']['description'], '&lt;script&gt;'),
        'Map project description must not contain raw executable markup.');
    assertMatch(false, str_contains($result['options']['state_specific']['DE']['description'], '<script>'),
        'Map popup descriptions must never inject unescaped script tags.');
    assertMatch(true, str_contains($result['options']['state_specific']['DE']['description'], '&lt;img'),
        'Map league name must be HTML-escaped.');

    assertMatch(['DE', 'FR'], $result['options']['regions'][1]['states'] ?? null,
        'Map regions must group countries under their federation');

    $playground = new PlaygroundPlanHelper();
    $parseIds = new \ReflectionMethod(PlaygroundPlanHelper::class, 'ids');

    assertMatch([12, 15, 20], $parseIds->invoke(
        $playground, ['12:matchfield', '015', '20,12', '20suffix', '5:bad:slug', '99999999999999999999999']
    ), 'Playground selectors must accept valid Joomla slugs and reject invalid IDs');
    assertMatch([], $parseIds->invoke($playground, [false, true, [12], '12.1', '0']),
        'Playground selector must reject non-integer and nested values');

    $app = new class implements CMSApplicationInterface {
        public array $messages = [];
        public function isClient(string $client): bool { return $client === 'site'; }
        public function enqueueMessage(string $message, string $type): void
        {
            $this->messages[] = [$message, $type];
        }
    };

    Factory::$lookups = 0;
    foreach ([
        ['projects' => ['12garbage'], 'playground' => []],
        ['projects' => ['99999999999999999999999'], 'playground' => []],
        ['projects' => [], 'playground' => ['23:bad:alias']],
        ['projects' => ['12:bad:extra'], 'playground' => ''],
        ['projects' => true, 'playground' => []],
    ] as $params) {
        assertMatch([], $playground->getData(new Registry($params), $app, (object) []),
            'Invalid explicitly selected projects/playgrounds must fail closed');
    }
    assertMatch(0, Factory::$lookups,
        'Invalid selectors should never fetch the database service');
    assertMatch([], $app->messages, 'Invalid selectors should not disclose internal errors');

    // Empty/default selectors remain intentional, and a valid selection
    // reaches the database service type validation.
    Factory::$database = new \stdClass();
    assertMatch([], $playground->getData(
        new Registry(['projects' => ['12:alias'], 'playground' => '0']), $app, (object) []
    ), 'A missing/invalid Joomla database service must not load fixtures');
    assertMatch(1, Factory::$lookups, 'Valid project selector must attempt the DB service');
    assertMatch('error', $app->messages[0][1] ?? null, 'Database service failure must be signaled');
    assertMatch('SportsManagement playground fixtures could not be loaded.',
        $app->messages[0][0] ?? null, 'Site users should receive a generic error, not internal details.');
    assertMatch(true, str_contains(\Joomla\CMS\Log\Log::$entries[0][0] ?? '', 'database service is unavailable'),
        'Internal database service failures should be logged for administrators.');


    // A match must only join opponents from its own project and teams
    // registered for that project's season.
    Factory::$database = new MapDatabase();
    $messageCount = count($app->messages);
    assertMatch([], $playground->getData(new Registry(), $app, (object) []),
        'Playground fixtures should allow an empty result set');
    assertMatch($messageCount, count($app->messages),
        'An empty fixture result must not be treated as a database error');
    $joinSql = implode("\n", Factory::$database->lastQuery->joins);
    foreach ([
        'pt2.id = m.projectteam2_id AND pt2.project_id = pt1.project_id',
        'st1.id = pt1.team_id AND st1.season_id = p.season_id',
        'st2.id = pt2.team_id AND st2.season_id = p.season_id',
    ] as $constraint) {
        assertMatch(true, str_contains($joinSql, $constraint),
            'Playground Plan must constrain both match teams to one project/season: ' . $constraint);
    }

    echo "Project Map portability and Playground Plan selector regressions OK.\n";
}
