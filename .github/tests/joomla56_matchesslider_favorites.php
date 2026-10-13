<?php
/**
 * Joomla 5/6 Matches Slider portable favorite-team filter regression tests.
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

namespace {
    \defined('_JEXEC') || define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_matchesslider/src/Helper/MatchesSliderHelper.php';

    final class FavoriteQuery
    {
        public array $columns = [];
        public array $conditions = [];
        public array $bindings = [];
        public string $from = '';

        public function select(array $columns): self
        {
            $this->columns = $columns;

            return $this;
        }

        public function from(string $table): self
        {
            $this->from = $table;

            return $this;
        }

        public function where(string $condition): self
        {
            $this->conditions[] = $condition;

            return $this;
        }

        public function whereIn(string $column, array $ids, mixed $type): self
        {
            $this->conditions[] = $column . ' IN (' . implode(',', $ids) . ')';

            return $this;
        }

        public function bind(string $name, mixed $value, mixed $type): self
        {
            $this->bindings[$name] = [$value, $type];

            return $this;
        }

        public function bindArray(array $values, mixed $type): array
        {
            $names = [];

            foreach ($values as $value) {
                $name = ':value' . count($this->bindings);
                $this->bindings[$name] = [$value, $type];
                $names[] = $name;
            }

            return $names;
        }
    }

    final class FavoriteDatabase implements \Joomla\Database\DatabaseInterface
    {
        public array $queries = [];

        public function __construct(private array $favorites)
        {
        }

        public function createQuery(): FavoriteQuery
        {
            return new FavoriteQuery();
        }

        public function quoteName(string $column): string
        {
            return $column;
        }

        public function setQuery(FavoriteQuery $query): void
        {
            $this->queries[] = $query;
        }

        public function loadObjectList(): array
        {
            return $this->favorites;
        }
    }

    $helper = new \Diddipoeler\Module\SportsManagementMatchesSlider\Site\Helper\MatchesSliderHelper();
    $apply = new \ReflectionMethod($helper, 'applyFavoriteTeamFilter');

    $empty = new FavoriteDatabase([
        (object) ['id' => 11, 'fav_team' => ''],
        (object) ['id' => 12, 'fav_team' => 'unparseable'],
    ]);
    $matchQuery = new FavoriteQuery();
    if ($apply->invoke($helper, $empty, $matchQuery, [11, 12]) !== false
        || $matchQuery->conditions !== []
        || count($empty->queries) !== 1
    ) {
        throw new \RuntimeException('Empty favorite teams did not disable the matches filter');
    }

    $missingProjects = new FavoriteDatabase([]);
    if ($apply->invoke($helper, $missingProjects, new FavoriteQuery(), []) !== false
        || $missingProjects->queries !== []
    ) {
        throw new \RuntimeException('Empty project selection queried favorite teams');
    }

    $projects = new FavoriteDatabase([
        (object) ['id' => 11, 'fav_team' => '4, 6'],
        (object) ['id' => 12, 'fav_team' => '8:club-a;9'],
        (object) ['id' => 13, 'fav_team' => '900'],
        (object) ['id' => 11, 'fav_team' => str_repeat('9', 40)],
    ]);
    $matchQuery = new FavoriteQuery();

    if (!$apply->invoke($helper, $projects, $matchQuery, [11, 12])) {
        throw new \RuntimeException('Valid favorite project teams were rejected');
    }

    if (count($projects->queries) !== 1
        || $projects->queries[0]->from !== '#__sportsmanagement_project'
        || !in_array('p.published = 1', $projects->queries[0]->conditions, true)
        || count($matchQuery->conditions) !== 1
        || str_contains(implode(' ', $matchQuery->conditions), 'FIND_IN_SET')
    ) {
        throw new \RuntimeException('Project favorites query is not portable or published-only');
    }

    $sql = implode(' ', $matchQuery->conditions);
    foreach ([
        'p.id = :favoriteProject0',
        'p.id = :favoriteProject1',
        'st1.team_id IN (',
        'st2.team_id IN (',
        ') OR (',
    ] as $required) {
        if (!str_contains($sql, $required)) {
            throw new \RuntimeException('Project-specific favorite restriction missing: ' . $required);
        }
    }

    if (($matchQuery->bindings[':favoriteProject0'][0] ?? null) !== 11
        || ($matchQuery->bindings[':favoriteProject1'][0] ?? null) !== 12
    ) {
        throw new \RuntimeException('Favorite team values could leak across projects');
    }

    $teamBindings = [];
    foreach ($matchQuery->bindings as $name => [$value, $type]) {
        if (!str_starts_with($name, ':favoriteProject')) {
            if ($type !== \Joomla\Database\ParameterType::INTEGER) {
                throw new \RuntimeException('Favorite team ID is not integer-bound');
            }
            $teamBindings[] = $value;
        }
    }
    sort($teamBindings);

    if ($teamBindings !== [4, 4, 6, 6, 8, 8, 9, 9]) {
        throw new \RuntimeException('Wrong or untrusted favorite team IDs reached query parameters');
    }

    echo "Matches Slider project favorites and portable SQL regressions OK\n";
}
