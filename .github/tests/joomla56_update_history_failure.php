<?php
/**
 * Regression for private update-history database diagnostics.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType { public const STRING = 'string'; }
}
namespace Joomla\CMS\MVC\Model {
    class BaseDatabaseModel {}
}
namespace Joomla\CMS\Application {
    final class AdministratorApplication {
        public array $messages = [];
        /** Identify the administrator client. @param string $client Requested client. @return bool Whether backend. */
        public function isClient(string $client): bool { return $client === 'administrator'; }
        /** Capture notifications for privacy assertions. @param string $message Public text. @param string $type Severity. @return void */
        public function enqueueMessage(string $message, string $type): void { $this->messages[] = [$message, $type]; }
    }
}
namespace Joomla\CMS {
    final class Factory {
        public static object $app;
        /** Resolve the fake Joomla application. @return object Container fixture. */
        public static function getContainer(): object {
            return new class {
                /** Return the backend application. @param string $key Service name. @return object Application. */
                public function get(string $key): object { return Factory::$app; }
            };
        }
    }
}
namespace Joomla\CMS\Language {
    final class Text {
        /** Return translated-key fixture. @param string $key Translation key. @return string Key. */
        public static function _(string $key): string { return $key; }
    }
}
namespace Joomla\CMS\Log {
    final class Log {
        public const ERROR = 1;
        public static array $messages = [];
        /** Record server diagnostics. @param string $message Log text. @param int $level Severity. @param string $category Category. @return void */
        public static function add(string $message, int $level, string $category): void { self::$messages[] = $message; }
    }
}
namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/admin/src/Model/UpdatesModel.php';
    \Joomla\CMS\Factory::$app = new \Joomla\CMS\Application\AdministratorApplication();
    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\UpdatesModel();
    $db = new class implements \Joomla\Database\DatabaseInterface {
        /** Simulate a database query setup failure. @return never No query is produced. */
        public function createQuery(): never { throw new \RuntimeException('SQLSTATE password=private'); }
    };
    $lookup = new \ReflectionMethod($model, 'getVersionRow');
    if ($lookup->invoke($model, $db, 'update.php') !== null
        || \Joomla\CMS\Factory::$app->messages !== [['JERROR_AN_ERROR_HAS_OCCURRED', 'error']]
        || count(\Joomla\CMS\Log\Log::$messages) !== 1
        || !str_contains(\Joomla\CMS\Log\Log::$messages[0], 'RuntimeException')
        || str_contains(\Joomla\CMS\Log\Log::$messages[0], 'password')) {
        throw new \RuntimeException('Update history lookup leaked private diagnostic information.');
    }
    try {
        $lookup->invoke($model, $db, 'update.php', true);
        throw new \LogicException('Strict lookup did not abort a subsequent history write.');
    } catch (\RuntimeException $e) {
        if ($e->getMessage() !== 'SQLSTATE password=private') {
            throw $e;
        }
    }
    echo "Update history diagnostic guards OK\n";
}
