<?php
/**
 * Validate malformed Joomla 5/6 administrator database maintenance inputs.
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

    /**
     * Reject a malformed maintenance request without database side effects.
     *
     * PHP warnings are converted into test failures so accidental coercions
     * of arrays and objects cannot silently pass Joomla's PHP 8 CI checks.
     *
     * @param mixed $table Untrusted legacy table selector.
     * @param mixed $command Untrusted legacy maintenance action.
     * @return void
     */
    function assertMaintenanceRejected(mixed $table, mixed $command): void
    {
        global $model;

        if ($model->setSportsManagementTableQuery($table, $command) !== false) {
            throw new \RuntimeException('Malformed database maintenance request was accepted.');
        }
    }

    set_error_handler(
        /**
         * Escalate PHP warnings instead of hiding invalid string casts.
         *
         * @param int $severity PHP diagnostic priority.
         * @param string $message PHP warning or notice.
         * @return never Always throws to fail the regression.
         */
        static function (int $severity, string $message): never {
            throw new \ErrorException($message, 0, $severity);
        }
    );

    try {
        foreach ([null, false, 0, 1.2, [], ['#__sportsmanagement_project'], new \stdClass()] as $table) {
            assertMaintenanceRejected($table, 'TRUNCATE');
        }

        foreach ([null, false, 0, ['TRUNCATE'], new \stdClass(), 'DROP', 'TRUNCATE; DROP TABLE', str_repeat('A', 128)] as $action) {
            assertMaintenanceRejected('#__sportsmanagement_project', $action);
        }

        assertMaintenanceRejected('', 'REPAIR');
        assertMaintenanceRejected(str_repeat('p', 256), 'REPAIR');
        assertMaintenanceRejected('#__sportsmanagement_project', 'TRUNCATE unexpectedly');
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 database maintenance input guards: OK\n";
}
