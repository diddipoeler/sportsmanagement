<?php
/**
 * Regression tests for Joomla 5/6 administrator diagnostic scalar inputs.
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
        public const NOTICE = 200;
        public const ERROR = 400;
    }
}

namespace {
    define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/admin/src/Model/DatabasetoolModel.php';

    $type = \Diddipoeler\Component\SportsManagement\Administrator\Model\DatabasetoolModel::class;
    $model = new $type();

    set_error_handler(
        /**
         * Turn PHP diagnostics into regression failures.
         *
         * @param int $severity PHP diagnostic type.
         * @param string $message Diagnostic text.
         * @return never Always throws to avoid false positives.
         */
        static function (int $severity, string $message): never {
            throw new \ErrorException($message, 0, $severity);
        }
    );

    try {
        foreach ([
            [[1], ['nested']],
            [new \stdClass(), new \stdClass()],
            [true, false],
            [str_repeat('9', 80), str_repeat('x', 5000)],
        ] as [$code, $message]) {
            $normalized = $type::getExeptionMessage($code, $message);

            if ($normalized['code'] !== 0 || !is_string($normalized['message'])
                || strlen($normalized['message']) > 4096) {
                throw new \RuntimeException('Malformed database error details were not normalized.');
            }
        }

        $duplicate = $type::getExeptionMessage('1060', 'SQLSTATE[42S21]: Duplicate column name 1060');

        if ($duplicate['code'] !== 1060 || $duplicate['error'] !== 0
            || $duplicate['log'] !== \Joomla\CMS\Log\Log::NOTICE) {
            throw new \RuntimeException('Duplicate-column diagnostics lost their legacy contract.');
        }

        $memory = $model->getMemory(['invalid'], '2048');

        if (count($memory) !== 3 || $memory[0]->memory !== 0
            || $memory[1]->memory !== 2048 || $memory[2]->memory !== 2048) {
            throw new \RuntimeException('Legacy memory counters were not normalized.');
        }

        foreach ([
            [[], [], 0.0],
            [new \stdClass(), new \stdClass(), 0.0],
            ['not-a-timestamp', 'bogus', 0.0],
            ['1.25', '2.75', 1.5],
            ['0.25 100', '0.75 101', 1.5],
            ['INF', 'NAN', 0.0],
        ] as [$start, $end, $expected]) {
            $elapsed = $model->getQueryTime($start, $end);

            if ($elapsed !== $expected) {
                throw new \RuntimeException('Incorrect safe query runtime calculation.');
            }
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 database diagnostic scalar guards: OK\n";
}
