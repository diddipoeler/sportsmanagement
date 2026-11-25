<?php
/**
 * Regression for Club Icons optional database failure isolation.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}
namespace Joomla\Database {
    interface DatabaseInterface {}
}
namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver {
        /**
         * Supply the native resolver contract so argument evaluation actually
         * reaches the simulated failing Joomla database container.
         * @param \Joomla\Database\DatabaseInterface $database Default driver.
         * @param int $selector Database mode.
         * @return \Joomla\Database\DatabaseInterface Selected driver.
         */
        public static function resolve(\Joomla\Database\DatabaseInterface $database, int $selector): \Joomla\Database\DatabaseInterface {
            return $database;
        }
    }
}
namespace Joomla\Registry {
    final class Registry {
        /** Retain module parameters. @param array $values Module settings. */
        public function __construct(private array $values) {}
        /** Read a module parameter. @param string $key Parameter name. @param mixed $default Fallback. @return mixed Value. */
        public function get(string $key, mixed $default = null): mixed { return $this->values[$key] ?? $default; }
    }
}
namespace Joomla\CMS {
    final class Factory {
        /** Simulate missing Joomla database service. @return never No container available. */
        public static function getContainer(): never { throw new \RuntimeException('SQLSTATE password=private'); }
    }
}
namespace Joomla\CMS\Log {
    final class Log {
        public const ERROR = 1;
        public static array $messages = [];
        /** Capture sanitized failure diagnostics. @param string $message Log text. @param int $level Severity. @param string $category Category. @return void */
        public static function add(string $message, int $level, string $category): void { self::$messages[] = $message; }
    }
}
namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_clubicons/src/Helper/ClubiconsHelper.php';
    $helper = new \Diddipoeler\Module\SportsManagementClubicons\Site\Helper\ClubiconsHelper();
    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        /** Identify frontend application. @param string $client Client name. @return bool Whether frontend. */
        public function isClient(string $client): bool { return $client === 'site'; }
        /** Return unused input fixture. @return object Request fixture. */
        public function getInput(): object { return new \stdClass(); }
    };
    $params = new \Joomla\Registry\Registry(['project_ids' => 5, 'cfg_which_database' => 0]);
    $result = $helper->getData($params, (object) ['id' => 7], $app);
    if ($result !== ['project' => null, 'ranking' => [], 'teams' => []]
        || count(\Joomla\CMS\Log\Log::$messages) !== 1
        || !str_contains(\Joomla\CMS\Log\Log::$messages[0], 'RuntimeException')
        || str_contains(\Joomla\CMS\Log\Log::$messages[0], 'password')) {
        throw new \RuntimeException('Club Icons database failure was not isolated.');
    }
    $mode = new \ReflectionMethod($helper, 'databaseSelector');
    foreach ([0, 1, '0001', '0000'] as $valid) {
        if ($mode->invoke(null, $valid) !== (int) $valid) {
            throw new \RuntimeException('Supported Club Icons database mode was rejected.');
        }
    }
    foreach ([2, '0007', true, ['1'], '1junk', str_repeat('0', 65)] as $invalid) {
        if ($mode->invoke(null, $invalid) !== null
            || $helper->getData(new \Joomla\Registry\Registry([
                'project_ids' => 5, 'cfg_which_database' => $invalid,
            ]), (object) ['id' => 7], $app) !== ['project' => null, 'ranking' => [], 'teams' => []]
            || count(\Joomla\CMS\Log\Log::$messages) !== 1) {
            throw new \RuntimeException('Unsupported Club Icons mode reached database resolution.');
        }
    }
    echo "Club Icons optional database failure and selector guards OK\n";
}
