<?php
/**
 * Reject invalid Joomla 5/6 JoomLeague mapping inputs before database access.
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

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Model/DatabasetoolModel.php';

    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\DatabasetoolModel();

    set_error_handler(
        /**
         * Convert PHP 8 warnings from malformed mappings into test failures.
         *
         * @param int $severity PHP error severity.
         * @param string $message Original warning.
         * @return never Always raises a failure.
         */
        static function (int $severity, string $message): never {
            throw new \ErrorException($message, 0, $severity);
        }
    );

    try {
        foreach ([
            null,
            '',
            true,
            new \stdClass(),
            [['name' => 'prefix_joomleague_project']],
            [(object) ['name' => ['nested']]],
            [(object) ['name' => new \stdClass()]],
            [(object) ['name' => 'prefix_joomleague_project; DROP TABLE']],
            [(object) ['name' => '']],
            [(object) ['name' => str_repeat('x', 256)]],
            array_fill(0, 1001, (object) ['name' => 'prefix_joomleague_project']),
        ] as $invalid) {
            // No Joomla DI container is provided: accessing the database
            // would fail and therefore exposes missing preflight validation.
            if ($model->checkImportTablesJlJsm($invalid) !== []) {
                throw new \RuntimeException('Malformed JoomLeague mappings were not rejected.');
            }
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 JoomLeague mapping input preflight: OK\n";
}
