<?php
/**
 * Joomla 5/6 model authorization and match-slider input/SQL bound regression.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS {
    class Factory {
        public static function getDate(): object {
            return new class {
                public function toSql(): string { return '2026-10-16 08:00:00'; }
            };
        }
    }
}
namespace Joomla\CMS\Language {
    class Text {
        public static function _(string $key): string { return $key; }
    }
}
namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}
namespace Joomla\CMS\Component {
    class ComponentHelper {
        public static function getParams(string $component): \Joomla\Registry\Registry {
            return new \Joomla\Registry\Registry();
        }
    }
}
namespace Joomla\CMS\Uri {
    class Uri {
        public static function root(): string { return 'https://example.org/'; }
    }
}
namespace Joomla\Registry {
    class Registry {
        public function __construct(private array $params = []) {}
        public function get(string $name, mixed $default = null): mixed {
            return $this->params[$name] ?? $default;
        }
    }
}
namespace Joomla\Database {
    interface DatabaseInterface {}
    class ParameterType {
        public const INTEGER = 'integer';
        public const STRING = 'string';
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementDatabaseResolver {
        public static function resolve(\Joomla\Database\DatabaseInterface $database, int $selector): \Joomla\Database\DatabaseInterface {
            return $database;
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel {
        protected function administratorApplication(): object { return $GLOBALS['jsmInstallApp']; }
        protected function getDatabase(): object { return $GLOBALS['jsmInstallDb']; }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Model/InstallhelperModel.php';
    require_once $root . '/modules/mod_sportsmanagement_matchesslider/src/Helper/MatchesSliderHelper.php';

    $GLOBALS['jsmInstallApp'] = new class {
        public object $identity;
        public function __construct() {
            $this->identity = new class {
                public bool $allowed = false;
                public int $id = 28;
                public function authorise(string $action, string $asset): bool {
                    return $this->allowed && $action === 'core.admin' && $asset === 'com_sportsmanagement';
                }
            };
        }
        public function getIdentity(): object { return $this->identity; }
    };
    $GLOBALS['jsmInstallDb'] = new class {
        public array $inserted = [];
        public function getNullDate(): string { return '0000-00-00 00:00:00'; }
        public function insertObject(string $table, object $row): bool {
            $this->inserted[] = [$table, clone $row];
            return true;
        }
    };
    $install = new \Diddipoeler\Component\SportsManagement\Administrator\Model\InstallhelperModel();
    if ($install->saveSportstype(['filter_sports_type' => 'soccer']) === []
        || $GLOBALS['jsmInstallDb']->inserted !== []) {
        throw new \RuntimeException('Direct installation model write bypassed component admin ACL.');
    }
    $GLOBALS['jsmInstallApp']->identity->allowed = true;
    foreach ([
        [],
        ['filter_sports_type' => []],
        ['filter_sports_type' => (object) ['name' => 'soccer']],
        ['filter_sports_type' => "soccer\nattack"],
        ['filter_sports_type' => str_repeat('A', 65)],
        ['filter_sports_type' => 'sport/type'],
        ['filter_sports_type' => '0'],
    ] as $post) {
        if ($install->saveSportstype($post) === [] || $GLOBALS['jsmInstallDb']->inserted !== []) {
            throw new \RuntimeException('Invalid installation sports type was persisted.');
        }
    }
    if ($install->saveSportstype(['filter_sports_type' => 'soccer']) !== []
        || count($GLOBALS['jsmInstallDb']->inserted) !== 1
        || $GLOBALS['jsmInstallDb']->inserted[0][1]->name !== 'COM_SPORTSMANAGEMENT_ST_SOCCER'
        || $GLOBALS['jsmInstallDb']->inserted[0][1]->modified_by !== 28) {
        throw new \RuntimeException('Authorized installation sports type save was broken.');
    }

    class SliderQuery {
        public function select(mixed $arg): self { return $this; }
        public function from(string $arg): self { return $this; }
        public function join(string $type, string $on): self { return $this; }
        public function where(string $arg): self { return $this; }
        public function whereIn(string $column, array $ids, string $type): self { return $this; }
        public function order(array|string $orders): self { return $this; }
    }
    class SliderDatabase implements \Joomla\Database\DatabaseInterface {
        public array $queryLimits = [];
        public function createQuery(): SliderQuery { return new SliderQuery(); }
        public function quoteName(string $column, ?string $alias = null): string { return $column; }
        public function setQuery(SliderQuery $query, int $offset = 0, int $limit = 0): self {
            $this->queryLimits[] = $limit;
            return $this;
        }
        public function loadObjectList(): array { return []; }
    }

    $module = new \Diddipoeler\Module\SportsManagementMatchesSlider\Site\Helper\MatchesSliderHelper();
    $parseIds = new \ReflectionMethod($module, 'ids');

    foreach ([
        [['2:foo', '2', '3:bar'], [2, 3]],
        [['4bad', '-9', '0', '+8', '9999999999999999999999'], []],
        [[str_repeat('1', 5000), '9'], [9]],
        [[implode(',', range(1, 150))], range(1, 100)],
    ] as [$input, $expected]) {
        if ($parseIds->invoke($module, $input) !== $expected) {
            throw new \RuntimeException('Match-slider IDs were not validated or bounded.');
        }
    }

    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        public function isClient(string $client): bool { return $client === 'site'; }
        public function getInput(): object {
            return new class {
                public function getInt(string $name, int $default = 0): int { return $default; }
                public function get(string $name, mixed $default = null, string $filter = 'raw'): mixed {
                    return $default;
                }
            };
        }
    };
    $db = new SliderDatabase();
    $result = $module->getData(new \Joomla\Registry\Registry(['p' => [5]]), (object) ['id' => 1], $app, $db);
    if ($result !== [] || $db->queryLimits !== [500]) {
        throw new \RuntimeException('Match-slider main query was not capped at 500 rows.');
    }

    $favoritesDb = new SliderDatabase();
    $favorites = $module->getData(
        new \Joomla\Registry\Registry(['p' => [5], 'use_fav' => 1]),
        (object) ['id' => 1],
        $app,
        $favoritesDb
    );
    if ($favorites !== [] || $favoritesDb->queryLimits !== [100]) {
        throw new \RuntimeException('Favorite-project lookups exceeded the bounded query.');
    }

    echo "Joomla 5/6 install model and match slider bounds OK\n";
}
