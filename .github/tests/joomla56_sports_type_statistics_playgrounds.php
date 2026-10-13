<?php
/**
 * Regression checks for playgrounds assigned to Joomla 5/6 sports projects.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface
    {
    }

    final class ParameterType
    {
        public const INTEGER = 'integer';
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        public function __construct(private array $values = [])
        {
        }

        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        public static function resolve(
            \Joomla\Database\DatabaseInterface $database,
            int $selector
        ): \Joomla\Database\DatabaseInterface {
            return $database;
        }
    }
}

namespace {
    \defined('_JEXEC') || define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_sports_type_statistics/src/Helper/SportsTypeStatisticsHelper.php';

    final class PlaygroundQuery
    {
        public array $columns = [];
        public string $table = '';
        public array $joins = [];
        public array $conditions = [];
        public array $bindings = [];

        public function select(string|array $columns): self
        {
            array_push($this->columns, ...(array) $columns);
            return $this;
        }

        public function from(string $table): self
        {
            $this->table = $table;
            return $this;
        }

        public function join(string $kind, string $condition): self
        {
            $this->joins[] = [$kind, $condition];
            return $this;
        }

        public function where(string $condition): self
        {
            $this->conditions[] = $condition;
            return $this;
        }

        public function bind(string $name, mixed $value, mixed $type): self
        {
            $this->bindings[$name] = [$value, $type];
            return $this;
        }
    }

    final class PlaygroundDatabase implements \Joomla\Database\DatabaseInterface
    {
        public array $queries = [];
        private ?PlaygroundQuery $current = null;

        public function __construct(
            private array $standard = [11, 12, 12, 0],
            private array $matches = [12, 13, 13],
            private int $projects = 5
        ) {
        }

        public function createQuery(): PlaygroundQuery
        {
            return new PlaygroundQuery();
        }

        public function quoteName(string $name, ?string $alias = null): string
        {
            return $name;
        }

        public function setQuery(PlaygroundQuery $query, int $start = 0, int $limit = 0): void
        {
            $this->queries[] = $query;
            $this->current = $query;
        }

        public function loadObject(): ?object
        {
            return (object) ['id' => 7, 'name' => 'Football', 'icon' => ''];
        }

        public function loadResult(): int
        {
            if (($this->current->columns[0] ?? '') !== 'COUNT(*)') {
                throw new \RuntimeException('Unexpected COUNT query');
            }

            return $this->projects;
        }

        public function loadColumn(): array
        {
            return match ($this->current->table) {
                '#__sportsmanagement_project_team' => $this->standard,
                '#__sportsmanagement_match' => $this->matches,
                default => throw new \RuntimeException('Unexpected playground source table'),
            };
        }
    }

    $helper = new \Diddipoeler\Module\SportsManagementSportsTypeStatistics\Site\Helper\SportsTypeStatisticsHelper();

    $disabled = array_fill_keys([
        'show_leagues', 'show_seasons', 'show_clubs', 'show_teams',
        'show_players', 'show_divisions', 'show_rounds', 'show_matches',
        'show_player_events', 'show_player_stats',
    ], 0);
    $params = new \Joomla\Registry\Registry($disabled + [
        'sportstypes' => 7,
        'show_playgrounds' => 1,
    ]);
    $db = new PlaygroundDatabase();
    $data = $helper->getData($params, $db);

    if ($data['counts'] !== ['projects' => 5, 'playgrounds' => 3]
        || count($db->queries) !== 4
    ) {
        throw new \RuntimeException('Grounds from project teams and matches were not deduplicated');
    }

    $grounds = array_slice($db->queries, 2);
    $expected = [
        [
            '#__sportsmanagement_project_team',
            [
                ['INNER', '#__sportsmanagement_project ON p.id = pt.project_id'],
                ['INNER', '#__sportsmanagement_playground ON pg.id = pt.standard_playground'],
            ],
        ],
        [
            '#__sportsmanagement_match',
            [
                ['INNER', '#__sportsmanagement_round ON r.id = m.round_id'],
                ['INNER', '#__sportsmanagement_project ON p.id = r.project_id'],
                ['INNER', '#__sportsmanagement_playground ON pg.id = m.playground_id'],
            ],
        ],
    ];

    foreach ($grounds as $index => $query) {
        [$source, $joins] = $expected[$index];

        if ($query->table !== $source
            || $query->columns !== ['DISTINCT pg.id']
            || $query->joins !== $joins
            || $query->conditions !== ['p.sports_type_id = :sportTypeId']
            || $query->bindings[':sportTypeId'] !== [7, \Joomla\Database\ParameterType::INTEGER]
        ) {
            throw new \RuntimeException('Ground query is not restricted to the selected sport');
        }
    }

    $method = new \ReflectionMethod($helper, 'countSportPlaygrounds');
    $emptyDb = new PlaygroundDatabase([], []);
    if ($method->invoke($helper, $emptyDb, 7) !== 0 || count($emptyDb->queries) !== 2) {
        throw new \RuntimeException('Missing sports grounds must result in zero');
    }

    $onlyMatchDb = new PlaygroundDatabase([], [22, 23]);
    if ($method->invoke($helper, $onlyMatchDb, 7) !== 2) {
        throw new \RuntimeException('Away grounds without team defaults were omitted');
    }

    $guardDb = new PlaygroundDatabase();
    if ($method->invoke($helper, $guardDb, 0) !== 0 || $guardDb->queries !== []) {
        throw new \RuntimeException('Unconfigured sport may not query playgrounds');
    }

    $offDb = new PlaygroundDatabase();
    $off = $helper->getData(
        new \Joomla\Registry\Registry($disabled + ['sportstypes' => 7, 'show_playgrounds' => 0]),
        $offDb
    );
    if ($off['counts'] !== ['projects' => 5] || count($offDb->queries) !== 2) {
        throw new \RuntimeException('Disabled playground count generated venue queries');
    }

    $noProjectsDb = new PlaygroundDatabase([11], [12], 0);
    $noProjects = $helper->getData($params, $noProjectsDb);
    if ($noProjects['counts'] !== ['projects' => 0] || count($noProjectsDb->queries) !== 2) {
        throw new \RuntimeException('No-project sports should skip the playground queries');
    }

    echo "Sports Type Statistics project playground regressions OK\n";
}
