<?php
/**
 * Joomla 5/6 Sports Type Statistics club and player scoping regression tests.
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

namespace Joomla\CMS\Uri {
    final class Uri
    {
        public static function root(): string
        {
            return 'https://example.test/';
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

    final class SportRelationQuery
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

    final class SportRelationDb implements \Joomla\Database\DatabaseInterface
    {
        public array $queries = [];
        private ?SportRelationQuery $active = null;

        public function __construct(private int $projectCount = 4)
        {
        }

        public function createQuery(): SportRelationQuery
        {
            return new SportRelationQuery();
        }

        public function quoteName(string $column, ?string $alias = null): string
        {
            return $column;
        }

        public function setQuery(SportRelationQuery $query, int $start = 0, int $limit = 0): void
        {
            $this->queries[] = $query;
            $this->active = $query;
        }

        public function loadObject(): object
        {
            return (object) ['id' => 7, 'name' => 'Football', 'icon' => ''];
        }

        public function loadResult(): int
        {
            return match ($this->active->columns[0] ?? '') {
                'COUNT(*)' => $this->projectCount,
                'COUNT(DISTINCT t.club_id)' => 2,
                'COUNT(DISTINCT tp.person_id)' => 8,
                default => throw new \RuntimeException('Unexpected statistics query'),
            };
        }
    }

    $helper = new \Diddipoeler\Module\SportsManagementSportsTypeStatistics\Site\Helper\SportsTypeStatisticsHelper();

    $off = array_fill_keys([
        'show_leagues', 'show_seasons', 'show_playgrounds', 'show_teams',
        'show_divisions', 'show_rounds', 'show_matches',
        'show_player_events', 'show_player_stats',
    ], 0);
    $params = new \Joomla\Registry\Registry($off + [
        'sportstypes' => 7,
        'show_clubs' => 1,
        'show_players' => 1,
    ]);
    $db = new SportRelationDb();
    $result = $helper->getData($params, $db);

    if ($result['counts'] !== ['projects' => 4, 'clubs' => 2, 'players' => 8]
        || count($db->queries) !== 4
    ) {
        throw new \RuntimeException('Unexpected sport-scoped club/player totals');
    }

    $queries = [];
    foreach ($db->queries as $query) {
        if (str_starts_with($query->columns[0] ?? '', 'COUNT(DISTINCT ')) {
            $queries[$query->columns[0]] = $query;
        }
    }

    $expected = [
        'COUNT(DISTINCT t.club_id)' => [
            '#__sportsmanagement_project_team',
            [
                ['INNER', '#__sportsmanagement_project ON p.id = pt.project_id'],
                ['INNER', '#__sportsmanagement_season_team_id ON st.id = pt.team_id'],
                ['INNER', '#__sportsmanagement_team ON t.id = st.team_id'],
            ],
            ['p.sports_type_id = :sportTypeId', 't.club_id > 0'],
        ],
        'COUNT(DISTINCT tp.person_id)' => [
            '#__sportsmanagement_project_team',
            [
                ['INNER', '#__sportsmanagement_project ON p.id = pt.project_id'],
                ['INNER', '#__sportsmanagement_season_team_id ON st.id = pt.team_id'],
                ['INNER', '#__sportsmanagement_season_team_person_id ON tp.team_id = st.team_id AND tp.season_id = st.season_id'],
            ],
            ['p.sports_type_id = :sportTypeId', 'tp.persontype = 1', 'tp.person_id > 0'],
        ],
    ];

    if (count($queries) !== count($expected)) {
        throw new \RuntimeException('Expected two distinct sport relation queries');
    }

    foreach ($expected as $column => [$table, $joins, $conditions]) {
        $query = $queries[$column] ?? null;

        if ($query === null
            || $query->table !== $table
            || $query->joins !== $joins
            || $query->conditions !== $conditions
            || $query->bindings[':sportTypeId'] !== [7, \Joomla\Database\ParameterType::INTEGER]
        ) {
            throw new \RuntimeException('Invalid sport/team/season relation in ' . $column);
        }
    }

    // Disabled metrics must not generate the otherwise expensive relation queries.
    $disabledDb = new SportRelationDb();
    $disabledParams = new \Joomla\Registry\Registry($off + [
        'sportstypes' => 7,
        'show_clubs' => 0,
        'show_players' => 0,
    ]);
    $disabled = $helper->getData($disabledParams, $disabledDb);

    if ($disabled['counts'] !== ['projects' => 4] || count($disabledDb->queries) !== 2) {
        throw new \RuntimeException('Disabled sport counters still query unrelated tables');
    }

    $emptyDb = new SportRelationDb(0);
    $empty = $helper->getData($params, $emptyDb);
    if ($empty['counts'] !== ['projects' => 0] || count($emptyDb->queries) !== 2) {
        throw new \RuntimeException('Empty sport must not query club or player relations');
    }

    // Direct guard: unconfigured sports should issue no count queries.
    foreach (['countSportClubs', 'countSportPlayers'] as $name) {
        $guardDb = new SportRelationDb();
        $method = new \ReflectionMethod($helper, $name);
        if ($method->invoke($helper, $guardDb, 0) !== 0 || $guardDb->queries !== []) {
            throw new \RuntimeException('Invalid sport ID accepted by ' . $name);
        }
    }

    echo "Sports Type Statistics club/player relation regressions OK\n";
}
