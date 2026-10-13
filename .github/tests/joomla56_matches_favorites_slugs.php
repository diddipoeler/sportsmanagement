<?php
/**
 * Joomla 5/6 Matches native favorite-team filter and portable slug regressions.
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
            \Joomla\Database\DatabaseInterface $fallback,
            int $selector
        ): \Joomla\Database\DatabaseInterface {
            return $fallback;
        }
    }
}

namespace {
    \defined('_JEXEC') || define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_matches/connectors/native/QueryTrait.php';

    final class NativeMatchesQuery
    {
        public array $columns = [];
        public array $conditions = [];
        public array $joins = [];
        public array $bindings = [];
        public string $source = '';
        public string $order = '';

        public function select(string|array $columns): self
        {
            array_push($this->columns, ...(array) $columns);

            return $this;
        }

        public function from(string $source): self
        {
            $this->source = $source;

            return $this;
        }

        public function join(string $type, string $condition): self
        {
            $this->joins[] = [$type, $condition];

            return $this;
        }

        public function where(string $condition): self
        {
            $this->conditions[] = $condition;

            return $this;
        }

        public function whereIn(string $field, array $values, mixed $type): self
        {
            $this->conditions[] = $field . ' IN (' . implode(',', $values) . ')';

            return $this;
        }

        public function whereNotIn(string $field, array $values, mixed $type): self
        {
            $this->conditions[] = $field . ' NOT IN (' . implode(',', $values) . ')';

            return $this;
        }

        public function bindArray(array $values, mixed $type): array
        {
            $placeholders = [];

            foreach ($values as $value) {
                $placeholder = ':v' . count($this->bindings);
                $this->bindings[$placeholder] = [$value, $type];
                $placeholders[] = $placeholder;
            }

            return $placeholders;
        }

        public function bind(string $key, mixed $value, mixed $type): self
        {
            $this->bindings[$key] = [$value, $type];

            return $this;
        }

        public function order(string $order): self
        {
            $this->order = $order;

            return $this;
        }
    }

    final class NativeMatchesDatabase implements \Joomla\Database\DatabaseInterface
    {
        public array $queries = [];
        public ?NativeMatchesQuery $current = null;

        public function __construct(
            private array $favoriteValues,
            private array $matches = []
        ) {
        }

        public function createQuery(): NativeMatchesQuery
        {
            return new NativeMatchesQuery();
        }

        public function quoteName(string $name): string
        {
            return $name;
        }

        public function setQuery(NativeMatchesQuery $query, int $start = 0, int $limit = 0): void
        {
            $this->queries[] = $query;
            $this->current = $query;
        }

        public function loadColumn(): array
        {
            return $this->favoriteValues;
        }

        public function loadObjectList(): array
        {
            return array_map(
                static fn (object $match): object => clone $match,
                $this->matches
            );
        }
    }

    final class MatchesQueryHarness
    {
        use \Diddipoeler\Module\SportsManagementMatches\Site\Helper\NativeQueryTrait;

        public function fetch(
            \Joomla\Database\DatabaseInterface $db,
            \Joomla\Registry\Registry $params
        ): array {
            return $this->loadMatches($db, $params, [11]);
        }
    }

    $harness = new MatchesQueryHarness();
    $favorites = new \Joomla\Registry\Registry([
        'use_fav' => 1,
        'limit' => 10,
    ]);

    foreach ([[], [''], ['invalid'], ['0'], ['9999999999999999999999999']] as $favoriteValues) {
        $db = new NativeMatchesDatabase(
            $favoriteValues,
            [(object) ['match_id' => 44]]
        );
        $result = $harness->fetch($db, $favorites);

        if ($result !== []
            || count($db->queries) !== 1
            || $db->queries[0]->source !== '#__sportsmanagement_project'
        ) {
            throw new \RuntimeException('Empty favorite-team selection included unrelated matches');
        }
    }

    $match = (object) [
        'match_id' => 44,
        'project_id' => 11,
        'project_alias' => 'football-league',
        'round_id' => 3,
        'round_alias' => 'third-round',
        'team1_alias' => 'home',
        'team2_alias' => 'away',
    ];
    $db = new NativeMatchesDatabase(['8,17'], [$match]);
    $result = $harness->fetch($db, $favorites);
    $query = $db->queries[1] ?? null;

    if (count($result) !== 1
        || $result[0]->project_slug !== '11:football-league'
        || $result[0]->round_slug !== '3:third-round'
        || $result[0]->match_slug !== '44:home_away'
        || $query === null
        || $query->source !== '#__sportsmanagement_match AS m'
        || str_contains(implode(' ', $query->columns), 'CONCAT_WS')
    ) {
        throw new \RuntimeException('Favorite match URLs or portable Joomla slugs are incorrect');
    }

    $favoriteIds = [];
    foreach ($query->bindings as [$value, $type]) {
        if ($type === \Joomla\Database\ParameterType::INTEGER && in_array($value, [8, 17], true)) {
            $favoriteIds[] = $value;
        }
    }
    sort($favoriteIds);

    if ($favoriteIds !== [8, 8, 17, 17]
        || !str_contains(implode(' ', $query->conditions), 'st1.team_id IN (')
        || !str_contains(implode(' ', $query->conditions), 'st2.team_id IN (')
    ) {
        throw new \RuntimeException('Favorite-team filter does not bind both home and away IDs');
    }

    $withoutFavorites = new \Joomla\Registry\Registry(['use_fav' => 0]);
    $db = new NativeMatchesDatabase([], [
        (object) [
            'match_id' => 51,
            'project_id' => 11,
            'project_alias' => '',
            'round_id' => 7,
            'round_alias' => '',
            'team1_alias' => null,
            'team2_alias' => 'guests',
        ],
    ]);
    $result = $harness->fetch($db, $withoutFavorites);

    if (count($db->queries) !== 1
        || count($result) !== 1
        || $result[0]->project_slug !== '11:'
        || $result[0]->round_slug !== '7:'
        || $result[0]->match_slug !== '51:guests'
    ) {
        throw new \RuntimeException('Missing aliases or disabled favorite filter changed match results');
    }

    echo "Matches favorite-team filter and portable Joomla slugs OK\n";
}
