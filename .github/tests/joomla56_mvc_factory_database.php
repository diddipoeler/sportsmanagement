<?php
/**
 * Regression test for Joomla 5/6 MVC factory database restoration.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
    interface DatabaseAwareInterface {
        public function setDatabase(DatabaseInterface $db): void;
    }
}
namespace Joomla\CMS {
    class Factory {
        public static object $container;
        /** Return the fixture container holding the primary Joomla driver. */
        public static function getContainer(): object { return self::$container; }
    }
}
namespace Joomla\CMS\MVC\Factory {
    class MVCFactory {
        public static object $next;
        public static array $config;
        public static string $prefix;
        /** Simulate Joomla's normal model creation and database injection. */
        public function createModel($name, $prefix = '', array $config = []) {
            self::$config = $config;
            self::$prefix = $prefix;
            $model = self::$next;
            if ($model instanceof \Joomla\Database\DatabaseAwareInterface) {
                $model->setDatabase(\Joomla\CMS\Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class));
            }
            return $model;
        }
    }
}
namespace Fixture {
    use Joomla\Database\DatabaseInterface;
    class Driver implements DatabaseInterface {}
    class Model implements \Joomla\Database\DatabaseAwareInterface {
        public DatabaseInterface $database;
        public int $calls = 0;
        /** Record the database assigned by the MVC factory. */
        public function setDatabase(DatabaseInterface $db): void {
            $this->database = $db;
            $this->calls++;
        }
    }
    class NativeModel extends Model {
        public DatabaseInterface $selected;
        /** Simulate native models resolving their own selected connection. */
        public function setDatabase(DatabaseInterface $db): void {
            parent::setDatabase($this->selected);
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel extends \Fixture\NativeModel {}
    class SportsManagementListModel extends \Fixture\NativeModel {}
}
namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementModel extends \Fixture\NativeModel {}
    class SportsManagementListModel extends \Fixture\NativeModel {}
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper {
    class LegacyModelDatabaseHelper {
        public static \Joomla\Database\DatabaseInterface $selected;
        public static int $calls = 0;
        public static bool $invalid = false;
        /** Simulate administrator selection and rejection of malformed input. */
        public static function resolve($db): \Joomla\Database\DatabaseInterface {
            self::$calls++;
            if (self::$invalid) {
                throw new \InvalidArgumentException('Invalid selector', 400);
            }
            return self::$selected;
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementDatabaseResolver {
        public static \Joomla\Database\DatabaseInterface $selected;
        public static int $calls = 0;
        public static bool $fail = false;
        /** Simulate frontend resolution and recoverable connection failure. */
        public static function resolve($db, $selector): \Joomla\Database\DatabaseInterface {
            self::$calls++;
            if (self::$fail) {
                throw new \RuntimeException('Connection failed');
            }
            return self::$selected;
        }
    }
}
namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Helper\LegacyModelDatabaseHelper as AdminResolver;
    use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementMVCFactory;
    use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver as SiteResolver;
    use Joomla\CMS\MVC\Factory\MVCFactory;
    use Joomla\Database\DatabaseInterface;

    class JSMModelAdmin extends \Fixture\Model { public DatabaseInterface $jsmdb; }
    class JSMModelList extends JSMModelAdmin {}
    class JSMModelLegacy extends JSMModelAdmin {}

    /** Fail the test with a descriptive message when an invariant is violated. */
    function verify(bool $condition, string $message): void {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/admin/src/Service/SportsManagementMVCFactory.php';
    $primary = new \Fixture\Driver();
    $legacy = new \Fixture\Driver();
    AdminResolver::$selected = new \Fixture\Driver();
    SiteResolver::$selected = new \Fixture\Driver();
    \Joomla\CMS\Factory::$container = new class($primary) {
        /** Store the primary database fixture. */
        public function __construct(private DatabaseInterface $db) {}
        /** Return the primary database service. */
        public function get(string $key): DatabaseInterface { return $this->db; }
    };
    $factory = new SportsManagementMVCFactory();

    foreach ([
        'Administrator' => [
            \Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel::class,
            \Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementListModel::class,
        ],
        'Site' => [
            \Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementModel::class,
            \Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementListModel::class,
        ],
    ] as $prefix => $classes) {
        foreach ($classes as $class) {
            $model = new $class();
            $model->selected = $legacy;
            MVCFactory::$next = $model;
            verify($factory->createModel('test', $prefix, ['dbo' => $primary, 'custom' => 1]) === $model, 'Model identity changed');
            verify($model->database === $legacy && $model->calls === 1, 'Native database resolved twice');
            verify(MVCFactory::$config === ['custom' => 1], 'Legacy dbo option not removed');
        }
    }

    foreach ([JSMModelAdmin::class, JSMModelList::class, JSMModelLegacy::class] as $class) {
        $model = new $class();
        $model->jsmdb = $legacy;
        MVCFactory::$next = $model;
        $factory->createModel('test', 'admin');
        verify($model->database === $legacy && $model->calls === 2, 'Legacy database identity lost');
    }
    verify(AdminResolver::$calls === 0 && SiteResolver::$calls === 0, 'Unnecessary resolver invocation');

    MVCFactory::$next = $model = new \Fixture\Model();
    $factory->createModel('test', 'admin');
    verify($model->database === AdminResolver::$selected && MVCFactory::$prefix === 'Administrator', 'Incorrect administrator resolver');
    $factory->createModel('test', 'site');
    verify($model->database === SiteResolver::$selected, 'Incorrect site resolver');
    verify(AdminResolver::$calls === 1 && SiteResolver::$calls === 1, 'Unexpected resolver call count');

    AdminResolver::$invalid = true;
    try {
        $factory->createModel('test', 'admin');
        throw new \RuntimeException('Malformed administrator selector was swallowed');
    } catch (\InvalidArgumentException $expected) {
        verify($expected->getCode() === 400, 'Incorrect invalid selector status');
    }

    SiteResolver::$fail = true;
    $factory->createModel('test', 'site');
    verify($model->database === $primary, 'Primary fallback not preserved');

    MVCFactory::$next = new \stdClass();
    verify($factory->createModel('test', 'site') === MVCFactory::$next, 'Non-database model changed');
    echo "MVC factory database restoration: OK\n";
}
