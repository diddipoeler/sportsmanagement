<?php
/**
 * Joomla 5/6 TeamPlayers bounded roster and module limit regression.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType {
        public const INTEGER = 'integer';
    }
}
namespace Joomla\Registry {
    final class Registry {
        public function __construct(private array $values = []) {}
        public function get(string $key, mixed $default = null): mixed {
            return $this->values[$key] ?? $default;
        }
    }
}
namespace Joomla\CMS\Component {
    final class ComponentHelper {
        public static function getParams(string $component): \Joomla\Registry\Registry {
            if ($component !== 'com_sportsmanagement') {
                throw new \RuntimeException('Unexpected component parameters.');
            }

            return new \Joomla\Registry\Registry();
        }
    }
}
namespace Joomla\CMS\Uri {
    final class Uri {
        public static function root(): string { return 'https://example.test/'; }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver {
        public static function resolve(\Joomla\Database\DatabaseInterface $fallback, int $selector): \Joomla\Database\DatabaseInterface {
            return $fallback;
        }
    }
}
namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_teamplayers/src/Helper/TeamPlayersHelper.php';

    final class BoundedQuery {
        public string $source = '';
        public array $ids = [];
        public array $where = [];
        public array $bindings = [];
        public function select(array|string $columns): self { return $this; }
        public function from(string $table): self { $this->source = $table; return $this; }
        public function join(string $type, string $sql): self { return $this; }
        public function where(string $predicate): self { $this->where[] = $predicate; return $this; }
        public function whereIn(string $name, array $ids, string $type): self {
            if (count($ids) > 500) {
                throw new \RuntimeException('TeamPlayers produced an excessive IN clause.');
            }
            $this->ids = $ids;
            return $this;
        }
        public function order(array|string $items): self { return $this; }
        public function group(string $item): self { return $this; }
        public function bind(string $key, mixed $value, string $type): self {
            $this->bindings[$key] = $value;
            return $this;
        }
    }
    final class BoundedDatabase implements \Joomla\Database\DatabaseInterface {
        public ?BoundedQuery $current = null;
        public int $currentLimit = 0;
        public array $queries = [];
        public function createQuery(): BoundedQuery { return new BoundedQuery(); }
        public function quoteName(string $name, ?string $alias = null): string { return $name; }
        public function setQuery(BoundedQuery $query, int $offset = 0, int $limit = 0): void {
            $this->current = $query;
            $this->currentLimit = $limit;
            $this->queries[] = [
                'source' => $query->source,
                'limit' => $limit,
                'ids' => $query->ids,
                'bindings' => $query->bindings,
            ];
        }
        public function loadObject(): ?object {
            return (object) [
                'id' => 11,
                'alias' => 'project',
                'name' => 'Project',
                'season_id' => 5,
                'game_regular_time' => 90,
                'team_id' => 17,
                'team_alias' => 'team',
                'projectteam_id' => 101,
            ];
        }
        public function loadObjectList(): array {
            if ($this->current === null) {
                throw new \RuntimeException('No active TeamPlayers query.');
            }

            if (str_contains($this->current->source, 'season_team_person_id')) {
                // Mimic the SQL limit. A 600-member legacy squad must return
                // only 500 rows, even if the module limit is set to infinity.
                $rows = [];

                for ($id = 1; $id <= 600; ++$id) {
                    $rows[] = (object) [
                        'pid' => $id,
                        'playerid' => $id,
                        'firstname' => 'Player',
                        'lastname' => sprintf('Member %04d', $id),
                        'nickname' => '',
                        'country' => '',
                        'country_name' => '',
                        'country_picture' => '',
                        'picture' => '',
                        'ppic' => '',
                        'person_alias' => 'member-' . $id,
                        'position_id' => 1,
                        'position' => 'Player',
                    ];
                }

                return $this->currentLimit === 0
                    ? $rows
                    : array_slice($rows, 0, $this->currentLimit);
            }

            if (str_contains($this->current->source, 'match_player')) {
                if ($this->currentLimit !== 500 || count($this->current->ids) !== 500
                    || ($this->current->bindings[':projectId'] ?? null) !== 11) {
                    throw new \RuntimeException('Minutes aggregation was not project-scoped and bounded.');
                }

                return [];
            }

            throw new \RuntimeException('Unexpected TeamPlayers query source.');
        }
    }

    $helper = new \Diddipoeler\Module\SportsManagementTeamPlayers\Site\Helper\TeamPlayersHelper();
    $testLimit = new \ReflectionMethod($helper, 'displayLimit');

    foreach ([
        [null, 24], [true, 24], [['100000'], 24],
        [20.0, 24], ['invalid', 24], ['-12', 24],
        ['', 24], ['5garbage', 24], [24, 24],
        ['0005', 5], ['0', 1], [0, 1], ['1', 1],
        [100, 100], ['100', 100], [101, 100],
        ['999999999999999999999999', 100],
        ['00000000000000000000000002', 2],
    ] as [$raw, $expected]) {
        if ($testLimit->invoke($helper, $raw) !== $expected) {
            throw new \RuntimeException('TeamPlayers module display limit was not normalized.');
        }
    }

    $db = new BoundedDatabase();
    $params = new \Joomla\Registry\Registry([
        'p' => '11:project',
        'team' => '17:team',
        'limit' => '999999999999999999999',
        'show_mins_played' => 1,
        'show_player_link' => 0,
        'show_player_flag' => 0,
    ]);
    $result = $helper->getData($params, $db);

    if (($result['project']->id ?? null) !== 11
        || count($result['players']) !== 100
        || count($result['roster'][1] ?? []) !== 500
        || count($db->queries) !== 5) {
        throw new \RuntimeException('TeamPlayers data was not bounded at 100 visible and 500 SQL roster rows.');
    }

    if (array_column($db->queries, 'limit') !== [1, 500, 500, 500, 500]) {
        throw new \RuntimeException('TeamPlayers SQL queries are not bounded.');
    }

    foreach (array_slice($db->queries, 2) as $stat) {
        if (count($stat['ids']) !== 500 || ($stat['bindings'][':projectId'] ?? null) !== 11) {
            throw new \RuntimeException('TeamPlayers minutes query has an unbounded player selection.');
        }
    }

    $db->queries = [];
    $small = $helper->getData(new \Joomla\Registry\Registry([
        'p' => 11,
        'team' => 17,
        'limit' => '0007',
        'show_mins_played' => 0,
        'show_player_link' => 0,
        'show_player_flag' => 0,
    ]), $db);
    if (count($small['players']) !== 7
        || count($small['roster'][1] ?? []) !== 500
        || array_column($db->queries, 'limit') !== [1, 500]) {
        throw new \RuntimeException('Existing TeamPlayers layout or minute toggle was broken.');
    }

    echo "Joomla 5/6 TeamPlayers bounded roster and module display limits OK\n";
}
