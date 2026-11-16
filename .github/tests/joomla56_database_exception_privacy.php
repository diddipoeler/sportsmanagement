<?php
/**
 * Verify Joomla 5/6 maintenance SQL exception messages remain private.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Model {
    class BaseDatabaseModel
    {
    }
}

namespace Joomla\Database {
    interface DatabaseInterface
    {
    }
}

namespace Joomla\CMS\Application {
    class AdministratorApplication
    {
        public array $messages = [];

        /**
         * Report the active Joomla administrator client.
         *
         * @param string $client Requested Joomla application client.
         * @return bool Whether the caller is an administrator.
         */
        public function isClient(string $client): bool
        {
            return $client === 'administrator';
        }

        /**
         * Capture the user-facing result of an SQL failure.
         *
         * @param string $message Public notice.
         * @param string $type Joomla severity.
         * @return void
         */
        public function enqueueMessage(string $message, string $type = 'message'): void
        {
            $this->messages[] = [$message, $type];
        }
    }
}

namespace Joomla\CMS {
    class Factory
    {
        public static ?object $app = null;

        /**
         * Resolve an administrator application from the test container.
         *
         * @return object Minimal dependency-injection container.
         */
        public static function getContainer(): object
        {
            return new class {
                /**
                 * Return the configured Joomla application test double.
                 *
                 * @param string $service Requested service type.
                 * @return object Registered administrator application.
                 */
                public function get(string $service): object
                {
                    return \Joomla\CMS\Factory::$app;
                }
            };
        }
    }
}

namespace Joomla\CMS\Language {
    class Text
    {
        /**
         * Preserve the public error key for stable assertions.
         *
         * @param string $key Requested translation.
         * @return string Untranslated message.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\Log {
    class Log
    {
        public const ERROR = 400;
        public static array $messages = [];

        /**
         * Capture redacted server-side database failure messages.
         *
         * @param string $message Diagnostic text.
         * @param int $level Joomla severity.
         * @param string $category Extension log channel.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$messages[] = [$message, $level, $category];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Model/DatabasetoolModel.php';

    $app = new \Joomla\CMS\Application\AdministratorApplication();
    \Joomla\CMS\Factory::$app = $app;

    $database = new class implements \Joomla\Database\DatabaseInterface {
        /**
         * Simulate a rejected SQL statement containing private details.
         *
         * @return never Always throws.
         */
        public function execute(): never
        {
            throw new \RuntimeException('PRIVATE_SQL_USERNAME_AND_PASSWORD');
        }
    };

    $class = \Diddipoeler\Component\SportsManagement\Administrator\Model\DatabasetoolModel::class;
    $result = $class::runJoomlaQuery('', $database);

    if ($result !== false || $app->messages !== [['JERROR_AN_ERROR_HAS_OCCURRED', 'error']]) {
        throw new \RuntimeException('Database maintenance error was not safely translated.');
    }

    $entries = \Joomla\CMS\Log\Log::$messages;

    if (count($entries) !== 1
        || !str_contains($entries[0][0], 'RuntimeException')
        || str_contains($entries[0][0], 'PRIVATE_SQL_USERNAME_AND_PASSWORD')
        || $entries[0][2] !== 'com_sportsmanagement') {
        throw new \RuntimeException('Database exception details leaked into the Joomla log.');
    }

    echo "Joomla 5/6 database maintenance exception privacy: OK\n";
}
