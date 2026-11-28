<?php
/**
 * Verify that frontend editor tables preserve the model's database identity.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}
namespace Joomla\CMS\MVC\Model {
    class BaseDatabaseModel {
        private \Joomla\Database\DatabaseInterface $database;
        /** Inject the database selected by the test or MVC factory. */
        public function setDatabase(\Joomla\Database\DatabaseInterface $db): void {
            $this->database = $db;
        }
        /** Return the exact injected database driver. */
        protected function getDatabase(): \Joomla\Database\DatabaseInterface {
            return $this->database;
        }
        /** Record fallback arguments for unrecognised table types. */
        public function getTable($type = '', $prefix = '', $config = []) {
            return [$type, $prefix, $config];
        }
    }
    class AdminModel extends BaseDatabaseModel {}
    class ListModel extends BaseDatabaseModel {}
}
namespace Joomla\CMS\Table {
    class Table {
        /** Capture storage table, primary key and actual database identity. */
        public function __construct(public string $table, public string $key, public object $database) {}
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper {
    class SportsManagementDatabaseResolver {
        public static \Joomla\Database\DatabaseInterface $replacement;
        public static int $calls = 0;
        /** Simulate a different database being selected on redundant resolution. */
        public function resolve($selector, $db): \Joomla\Database\DatabaseInterface {
            self::$calls++;
            return self::$replacement;
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementDatabaseResolver {
        /** Reject unexpected frontend resolution in an already authorised edit. */
        public static function resolve($db, $selector): \Joomla\Database\DatabaseInterface {
            throw new \RuntimeException('Authorised match connection was resolved again');
        }
    }
}
namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
    use Joomla\Database\DatabaseInterface;

    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    $site = 'Diddipoeler\\Component\\SportsManagement\\Site\\Model\\';
    $admin = 'Diddipoeler\\Component\\SportsManagement\\Administrator\\Table\\';

    spl_autoload_register(static function (string $class) use ($root, $site, $admin): void {
        foreach ([$site => '/site/src/Model/', $admin => '/admin/src/Table/'] as $prefix => $dir) {
            if (str_starts_with($class, $prefix)) {
                $file = $root . $dir . substr($class, strlen($prefix)) . '.php';
                if (is_file($file)) {
                    require_once $file;
                }
            }
        }
    });

    $selected = new class implements DatabaseInterface {};
    $primary = new class implements DatabaseInterface {};
    SportsManagementDatabaseResolver::$replacement = new class implements DatabaseInterface {};

    foreach ([
        ['EditmatchModel', 'match', 'MatchTable', 'match'],
        ['EditprojectteamModel', 'projectteam', 'ProjectteamTable', 'project_team'],
        ['EditclubModel', 'club', 'ClubTable', 'club'],
        ['EditteamModel', 'team', 'TeamTable', 'team'],
        ['EditpersonModel', 'player', 'PersonTable', 'person'],
        ['EditpersonModel', 'person', 'PersonTable', 'person'],
        ['ResultsModel', 'match', 'MatchTable', 'match'],
    ] as [$modelName, $type, $tableName, $storage]) {
        $model = (new \ReflectionClass($site . $modelName))->newInstanceWithoutConstructor();

        // Bypass application-dependent model setup; exercise the real getTable().
        $setter = new \ReflectionMethod(\Joomla\CMS\MVC\Model\BaseDatabaseModel::class, 'setDatabase');
        $setter->invoke($model, $modelName === 'EditmatchModel' ? $primary : $selected);
        if ($modelName === 'EditmatchModel') {
            $model->useAuthorizedDatabase($selected);
        }
        foreach ([$type, strtoupper($type)] as $alias) {
            $table = $model->getTable($alias);
            if (get_class($table) !== $admin . $tableName || $table->database !== $selected
                || $table->table !== '#__sportsmanagement_' . $storage || $table->key !== 'id'
                || SportsManagementDatabaseResolver::$calls !== 0) {
                throw new \RuntimeException('Incorrect table connection: ' . $modelName . '/' . $alias);
            }
        }
        if ($model->getTable('other', 'ExternalTable', ['custom' => 1]) !== ['other', 'ExternalTable', ['custom' => 1]]) {
            throw new \RuntimeException('Fallback changed: ' . $modelName);
        }
    }

    echo "Frontend editor table database identity: OK\n";
}
