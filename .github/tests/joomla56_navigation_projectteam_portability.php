<?php
/**
 * Joomla 5/6 portable navigation and administrator project-team ID regression.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application { interface CMSApplicationInterface {} }
namespace Joomla\Database {
    interface DatabaseInterface {}
    class ParameterType { public const INTEGER = 'integer'; }
}
namespace Joomla\Registry {
    class Registry {
        public function __construct(private array $items = []) {}
        public function get(string $name, mixed $default = null): mixed {
            return $this->items[$name] ?? $default;
        }
    }
}
namespace Joomla\CMS\Language {
    class Text { public static function _(string $value): string { return $value; } }
}
namespace Joomla\CMS\HTML {
    class HTMLHelper {
        public static function _(string $method, mixed ...$args): string {
            return json_encode($args[0], JSON_THROW_ON_ERROR);
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel {}
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/modules/mod_sportsmanagement_navigation_menu/src/Helper/NavigationMenuHelper.php';
    require_once $root . '/admin/src/Model/ProjectteamModel.php';

    $class = \Diddipoeler\Module\SportsManagementNavigationMenu\Site\Helper\NavigationMenuHelper::class;
    $slug = new \ReflectionMethod($class, 'slug');
    foreach ([[7, 'name', '7:name'], [7, null, '7'],
        [7, '', '7:'], [null, null, ''], [null, 'name', 'name']] as [$id, $alias, $expected]) {
        if ($slug->invoke(null, $id, $alias) !== $expected) {
            throw new \RuntimeException('Portable navigation slug mismatch.');
        }
    }

    $database = new class implements \Joomla\Database\DatabaseInterface {
        public array $queries = [];
        public function createQuery(): object {
            return new class {
                public array $columns = [];
                public function select(array $columns): self {
                    array_push($this->columns, ...$columns); return $this;
                }
                public function from(string $table): self { return $this; }
                public function join(string $type, string $condition): self { return $this; }
                public function where(string $condition): self { return $this; }
                public function bind(string $name, mixed $value, mixed $type): self { return $this; }
                public function order(string $column): self { return $this; }
            };
        }
        public function quoteName(string $column, ?string $alias = null): string {
            return $column . ($alias === null ? '' : ' AS ' . $alias);
        }
        public function setQuery(object $query, int $offset = 0, int $limit = 0): void {
            $this->queries[] = $query;
        }
        public function loadObject(): object {
            return (object) [
                'id' => 7, 'project_alias' => 'test',
                'league_id' => 3, 'joined_league_id' => 3, 'league_alias' => '',
                'season_id' => 9, 'joined_season_id' => null, 'season_alias' => null,
            ];
        }
        public function loadObjectList(): array { return []; }
    };
    $input = new class {
        public function getCmd(string $name): string { return 'com_sportsmanagement'; }
        public function getInt(string $name, int $default = 0): int {
            return $name === 'p' ? 7 : $default;
        }
    };
    $app = new class($input) implements \Joomla\CMS\Application\CMSApplicationInterface {
        public function __construct(private object $input) {}
        public function getInput(): object { return $this->input; }
    };

    $navigation = new $class(new \Joomla\Registry\Registry(), $app, $database);
    $project = $navigation->getProject();
    if (!$project || $project->project_slug !== '7:test'
        || $project->league_slug !== '3:' || $project->season_slug !== '') {
        throw new \RuntimeException('Navigation project slugs changed.');
    }

    $navigation->getSeasonSelect();
    $navigation->getLeagueSelect();
    $navigation->getDivisionSelect();
    $navigation->getProjectSelect();

    $fields = implode("\n", array_merge(...array_map(
        static fn (object $query): array => $query->columns, $database->queries
    )));
    if (count($database->queries) !== 5 || str_contains($fields, 'CONCAT_WS(')) {
        throw new \RuntimeException('MySQL-dependent navigation read query.');
    }
    foreach (['p.alias AS project_alias', 'l.alias AS league_alias',
        's.alias AS season_alias'] as $needle) {
        if (!str_contains($fields, $needle)) {
            throw new \RuntimeException('Missing navigation alias: ' . $needle);
        }
    }

    $adminClass = \Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectteamModel::class;
    $admin = (new \ReflectionClass($adminClass))->newInstanceWithoutConstructor();
    $normalise = new \ReflectionMethod($admin, 'normaliseIds');
    $ids = ['7', '007', 7, ' 8 ', '9garbage', true, -1, 0,
        ['9'], '999999999999999999999999999', '1e3', '012'];
    if ($normalise->invoke($admin, $ids) !== [7, 8, 12]
        || $normalise->invoke($admin, '7') !== []) {
        throw new \RuntimeException('Admin projectteam IDs not validated.');
    }
    echo "Joomla 5/6 portable navigation and project-team IDs OK\n";
}
