<?php
/**
 * Joomla 5/6 team-player state and first league overview database regressions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Registry {
    class Registry {
        public function __construct(private array $data = []) {}
        public function get(string $key, mixed $default = null): mixed {
            return $this->data[$key] ?? $default;
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
namespace Joomla\CMS\Log {
    class Log {
        public const ERROR = 'error';
        public static array $messages = [];
        public static function add(string $message, mixed $level, string $category): void {
            self::$messages[] = $message;
        }
    }
}
namespace Joomla\CMS\Component {
    class ComponentHelper {
        public static function getParams(string $component): \Joomla\Registry\Registry {
            return new \Joomla\Registry\Registry();
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel {
        public static array $post = [];
        public string $error = '';
        public static int $databaseAccess = 0;
        public function setError(string $message): void { $this->error = $message; }
        protected function getDatabase(): mixed {
            ++self::$databaseAccess;
            throw new \RuntimeException('Rejected request unexpectedly reached the database');
        }
        protected function administratorApplication(): object {
            return new class {
                public function getInput(): object {
                    return new class {
                        public object $post;
                        public function __construct() {
                            $this->post = new class {
                                public function getArray(): array {
                                    return \Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel::$post;
                                }
                            };
                        }
                    };
                }
            };
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementDatabaseResolver {
        public static int $calls = 0;
        public static bool $throws = false;
        public static function resolve(
            \Joomla\Database\DatabaseInterface $db, int $selector
        ): \Joomla\Database\DatabaseInterface {
            ++self::$calls;
            if (self::$throws) {
                throw new \RuntimeException('External SQL secret');
            }
            return $db;
        }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Model/TeamplayerModel.php';
    require_once $root . '/modules/mod_sportsmanagement_firstleagueoverview/src/Helper/FirstLeagueOverviewHelper.php';

    $adminClass = \Diddipoeler\Component\SportsManagement\Administrator\Model\TeamplayerModel::class;
    $base = \Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel::class;
    $admin = (new \ReflectionClass($adminClass))->newInstanceWithoutConstructor();

    foreach ([
        [['3'], [], 'oops', 2],
        [['3'], [], 99, 2],
        [['3'], [], 1.5, 2],
        [['3'], [], 1, '2garbage'],
        [['3'], [], 1, '9999999999999999999999999'],
        [['3'], [], 2, true],
    ] as [$ids, $mapping, $state, $project]) {
        if ($admin->set_state($ids, $mapping, $state, $project) !== false) {
            throw new \RuntimeException('Invalid roster state or project was accepted.');
        }
    }
    foreach ([-2, 0, 1, 2] as $state) {
        if ($admin->set_state([], [], $state, 0) !== true) {
            throw new \RuntimeException('Valid Joomla state was rejected.');
        }
    }
    foreach ([
        ['pid' => '5garbage', 'cid' => ['3']],
        ['pid' => '99999999999999999999999999', 'cid' => ['3']],
    ] as $post) {
        $base::$post = $post;
        if ($admin->saveshort() !== false) {
            throw new \RuntimeException('Invalid roster project was accepted.');
        }
    }
    if ($base::$databaseAccess !== 0) {
        throw new \RuntimeException('Malformed admin request accessed database.');
    }

    $moduleClass = \Diddipoeler\Module\SportsManagementFirstLeagueOverview\Site\Helper\FirstLeagueOverviewHelper::class;
    $helper = new $moduleClass();
    $params = new \Joomla\Registry\Registry();
    $resolver = \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::class;

    $db = new class implements \Joomla\Database\DatabaseInterface {
        public int $queries = 0;
        public bool $failQueries = false;
        public function createQuery(): object {
            if ($this->failQueries) {
                throw new \RuntimeException('Table missing: SQL details');
            }
            return new class {
                public function select(array $columns): self { return $this; }
                public function from(string $table): self { return $this; }
                public function join(string $type, string $condition): self { return $this; }
                public function where(string $condition): self { return $this; }
                public function bind(string $name, mixed $value, mixed $type): self { return $this; }
                public function whereIn(string $name, array $values, mixed $type): self { return $this; }
                public function order(string|array $fields): self { return $this; }
            };
        }
        public function quoteName(string $name, ?string $alias = null): string {
            return $name . ($alias === null ? '' : ' AS ' . $alias);
        }
        public function setQuery(object $query): void { ++$this->queries; }
        public function loadObjectList(): array { return []; }
    };
    $empty = ['projects' => [], 'federations' => []];

    if ($helper->getData($params, $db) !== $empty || $db->queries !== 2) {
        throw new \RuntimeException('Empty league module shape or read queries changed.');
    }
    $db->failQueries = true;
    if ($helper->getData($params, $db) !== $empty
        || count(\Joomla\CMS\Log\Log::$messages) !== 1
        || !str_contains(\Joomla\CMS\Log\Log::$messages[0], 'Table missing')) {
        throw new \RuntimeException('League module SQL exception leaked.');
    }
    $db->failQueries = false;
    $resolver::$throws = true;
    if ($helper->getData($params, $db) !== $empty
        || count(\Joomla\CMS\Log\Log::$messages) !== 2) {
        throw new \RuntimeException('External database failure was not contained.');
    }

    echo "Joomla 5/6 roster status and first-league database failures OK\n";
}
