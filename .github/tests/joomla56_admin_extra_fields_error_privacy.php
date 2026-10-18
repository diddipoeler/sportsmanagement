<?php
/**
 * Joomla 5/6 administrator extra-field database error and privacy regression.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace Joomla\CMS\Language {
    final class Text
    {
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $entries = [];

        public static function add(string $message, int $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Helper {
    final class SportsManagementAdministratorApplicationResolver
    {
        public static object $app;

        public static function resolve(): \Joomla\CMS\Application\CMSApplicationInterface
        {
            return self::$app;
        }
    }

    final class SportsManagementDatabaseResolver
    {
        public static bool $throwOnResolve = true;

        public function resolve($params, ?\Joomla\Database\DatabaseInterface $db): \Joomla\Database\DatabaseInterface
        {
            if (self::$throwOnResolve) {
                throw new \RuntimeException('private SQLSTATE username=root password=secret!');
            }

            return $db;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Helper/ExtraFieldsReadHelper.php';

    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        public array $messages = [];

        public function enqueueMessage(string $text, string $type): void
        {
            $this->messages[] = [$text, $type];
        }
    };

    \Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementAdministratorApplicationResolver::$app = $app;
    $helper = new \Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraFieldsReadHelper();

    // Early validation must not trigger database resolution or UI messages.
    if ($helper->getFields(0, 'club') !== [] || $app->messages !== []) {
        throw new \RuntimeException('Extra fields were loaded for an invalid record.');
    }

    // Database service initialization can also throw: it must be caught.
    if ($helper->getFields(7, 'club') !== []) {
        throw new \RuntimeException('Failed database resolution returned extra fields.');
    }

    if ($app->messages !== [['JERROR_AN_ERROR_HAS_OCCURRED', 'error']]
        || count(\Joomla\CMS\Log\Log::$entries) !== 1
        || !str_contains(\Joomla\CMS\Log\Log::$entries[0][0], 'password=secret')
        || str_contains($app->messages[0][0], 'secret')) {
        throw new \RuntimeException('Administrator error reporting leaked database credentials.');
    }

    \Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver::$throwOnResolve = false;
    $db = new class implements \Joomla\Database\DatabaseInterface {
        public function createQuery(): object
        {
            throw new \RuntimeException('private query table details: secret table name');
        }
    };

    if ($helper->getFields(7, 'club', 'backend', $db) !== []
        || count(\Joomla\CMS\Log\Log::$entries) !== 2
        || !str_contains(\Joomla\CMS\Log\Log::$entries[1][0], 'secret table name')
        || $app->messages[1] !== ['JERROR_AN_ERROR_HAS_OCCURRED', 'error']) {
        throw new \RuntimeException('Administrator query setup failure was not handled safely.');
    }

    echo "Joomla 5/6 administrator extra-field error privacy passed.\n";
}
