<?php
/**
 * Test the Joomla 5/6 database legacy logger redacts SQL exception details.
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

namespace Joomla\CMS\Log {
    class Log
    {
        public const ERROR = 400;
        public static array $messages = [];

        /**
         * Record the sanitized database maintenance diagnostic.
         *
         * @param string $message Logged diagnostic.
         * @param int $priority Severity.
         * @param string $category Logger category.
         * @return void
         */
        public static function add(string $message, int $priority, string $category): void
        {
            self::$messages[] = [$message, $priority, $category];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Model/DatabasetoolModel.php';

    $class = \Diddipoeler\Component\SportsManagement\Administrator\Model\DatabasetoolModel::class;

    set_error_handler(
        /**
         * Fail immediately on PHP 8 legacy string-cast warnings.
         *
         * @param int $severity PHP diagnostic level.
         * @param string $message PHP warning text.
         * @return never Always raises a test failure.
         */
        static function (int $severity, string $message): never {
            throw new \ErrorException($message, 0, $severity);
        }
    );

    try {
        $class::writeErrorLog(
            $class,
            'checkQuotes',
            '/srv/private/administrator/DatabasetoolModel.php',
            'SQLSTATE password=PRIVATE_DATABASE_SECRET query=DROP TABLE',
            321
        );

        if (count(\Joomla\CMS\Log\Log::$messages) !== 1) {
            throw new \RuntimeException('Database exception was not logged.');
        }

        $logged = \Joomla\CMS\Log\Log::$messages[0][0];

        if (!str_contains($logged, 'checkQuotes')
            || !str_contains($logged, 'DatabasetoolModel.php:321')
            || str_contains($logged, 'PRIVATE_DATABASE_SECRET')
            || str_contains($logged, 'DROP TABLE')
            || str_contains($logged, '/srv/private')) {
            throw new \RuntimeException('Database logger leaked an SQL statement or lost safe context.');
        }

        $class::writeErrorLog(['nested'], new \stdClass(), ['path'], ['secret'], ['line']);
        $class::writeErrorLog($class, 'test', 'file.php', '', 0);

        if (count(\Joomla\CMS\Log\Log::$messages) !== 1) {
            throw new \RuntimeException('Malformed or empty diagnostics should not be logged.');
        }

        $class::writeErrorLog(['nested'], new \stdClass(), new \stdClass(), 'test failure', ['line']);

        if (count(\Joomla\CMS\Log\Log::$messages) !== 2
            || !str_contains(\Joomla\CMS\Log\Log::$messages[1][0], 'unknown::unknown')) {
            throw new \RuntimeException('Malformed diagnostic metadata was not safely handled.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 legacy database SQL logger privacy: OK\n";
}
