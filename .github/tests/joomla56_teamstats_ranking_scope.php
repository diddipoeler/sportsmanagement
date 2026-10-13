<?php
/**
 * Joomla 5/6 Team Stats Ranking season and project scope regressions.
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
        public function __construct(private array $data = [])
        {
        }

        public function get(string $key, mixed $default = null): mixed
        {
            return $this->data[$key] ?? $default;
        }
    }
}

namespace {
    \defined('_JEXEC') || define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_teamstats_ranking/src/Helper/TeamStatsRankingHelper.php';

    final class RankingQuery
    {
        public array $columns = [];
        public string $from = '';
        public array $joins = [];
        public array $wheres = [];
        public array $groups = [];
        public array $bindings = [];

        public function bindArray(array $values, mixed $type): array
        {
            $placeholders = [];

            foreach ($values as $index => $value) {
                $placeholder = ':stat' . $index;
                $placeholders[] = $placeholder;
                $this->bindings[$placeholder] = [$value, $type];
            }

            return $placeholders;
        }

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

        public function join(string $kind, string $condition): self
        {
            $this->joins[] = [$kind, $condition];

            return $this;
        }

        public function where(string $condition): self
        {
            $this->wheres[] = $condition;

            return $this;
        }

        public function group(string|array $columns): self
        {
            $this->groups = (array) $columns;

            return $this;
        }

        public function bind(string $name, mixed $value, mixed $type): self
        {
            $this->bindings[$name] = [$value, $type];

            return $this;
        }
    }

    final class RankingDatabase implements \Joomla\Database\DatabaseInterface
    {
        public array $queries = [];
        private ?RankingQuery $current = null;

        public function createQuery(): RankingQuery
        {
            return new RankingQuery();
        }

        public function quoteName(string $name, ?string $alias = null): string
        {
            return $name;
        }

        public function setQuery(RankingQuery $query): void
        {
            $this->queries[] = $query;
            $this->current = $query;
        }

        public function loadObjectList(): array
        {
            $weighted = in_array('ms.statistic_id', $this->current?->columns ?? [], true);

            return [(object) [
                'team_id' => 17,
                'statistic_id' => 23,
                'total' => 4,
            ]];
        }
    }

    $helper = new \Diddipoeler\Module\SportsManagementTeamStatsRanking\Site\Helper\TeamStatsRankingHelper();

    $cases = [
        ['aggregateStatistics', [17, [23]], [17 => 4.0], 'ms'],
        ['aggregateEvents', [17, [8]], [17 => 4.0], 'me'],
        ['weightedStatistics', [17, new \Joomla\Registry\Registry([
            'stat_ids' => [23],
            'factors' => '1.5',
        ])], [17 => 6.0], 'ms'],
    ];

    foreach ($cases as [$methodName, $args, $expected, $alias]) {
        $db = new RankingDatabase();
        $method = new \ReflectionMethod($helper, $methodName);
        $actual = $method->invoke($helper, $db, ...$args);

        if ($actual !== $expected || count($db->queries) !== 1) {
            throw new \RuntimeException('Incorrect team ranking result: ' . $methodName);
        }

        $query = $db->queries[0];
        $joins = array_map(static fn(array $join): string => $join[1], $query->joins);
        $required = [
            '#__sportsmanagement_season_team_id ON st.team_id = tp.team_id AND st.season_id = tp.season_id',
            '#__sportsmanagement_project_team ON pt.team_id = st.id',
            $alias === 'ms'
                ? '#__sportsmanagement_match_statistic ON ms.teamplayer_id = tp.id AND ms.projectteam_id = pt.id'
                : '#__sportsmanagement_match_event ON me.teamplayer_id = tp.id AND me.projectteam_id = pt.id',
            '#__sportsmanagement_round ON r.id = m.round_id AND r.project_id = pt.project_id',
        ];

        foreach ($required as $fragment) {
            $found = false;

            foreach ($joins as $join) {
                if (str_contains($join, $fragment)) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                throw new \RuntimeException('Missing scope condition in ' . $methodName . ': ' . $fragment);
            }
        }

        if ($query->from !== '#__sportsmanagement_season_team_person_id'
            || $query->wheres[0] !== 'pt.project_id = :projectId'
            || $query->bindings[':projectId'] !== [17, \Joomla\Database\ParameterType::INTEGER]
        ) {
            throw new \RuntimeException('Project scope or parameter binding missing in ' . $methodName);
        }
    }

    foreach (['aggregateStatistics', 'aggregateEvents'] as $methodName) {
        $db = new RankingDatabase();
        $method = new \ReflectionMethod($helper, $methodName);

        if ($method->invoke($helper, $db, 17, []) !== [] || $db->queries !== []) {
            throw new \RuntimeException('Empty statistic IDs must not execute ' . $methodName);
        }
    }

    echo "Team Stats Ranking season and project scope regressions OK\n";
}
