<?php
/**
 * Validate Joomla 5/6 bootstrap SQL builders before touching a database.
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

    $type = \Diddipoeler\Component\SportsManagement\Administrator\Model\DatabasetoolModel::class;
    $model = new $type();

    /**
     * Verify invalid input fails before database service resolution.
     *
     * No Joomla DI container exists in this regression fixture. The expected
     * InvalidArgumentException must occur before any SQL or schema changes.
     *
     * @param object $model The native database maintenance model.
     * @param string $method SQL builder entry point.
     * @param array $arguments Input values for the SQL builder.
     * @return void
     */
    function assertBootstrapRejected(object $model, string $method, array $arguments): void
    {
        try {
            $model->$method(...$arguments);
        } catch (\InvalidArgumentException) {
            return;
        }

        throw new \RuntimeException('Malformed input accepted by ' . $method);
    }

    set_error_handler(
        /**
         * Fail on warnings from PHP 8 implicit type conversions.
         *
         * @param int $severity PHP diagnostic level.
         * @param string $message Diagnostic message.
         * @return never Always throws.
         */
        static function (int $severity, string $message): never {
            throw new \ErrorException($message, 0, $severity);
        }
    );

    try {
        foreach ([
            ['build_SelectQuery', [['eventtype'], 'Goal', 1]],
            ['build_SelectQuery', ['eventtype', ['Goal'], 1]],
            ['build_SelectQuery', ['eventtype', 'Goal', ['nested']]],
            ['build_SelectQuery', ['other_table', 'Goal', 1]],
            ['build_InsertQuery_Event', ['eventtype', ['Goal'], '', 1, 1]],
            ['build_InsertQuery_Event', ['eventtype', 'Goal', new \stdClass(), 1, 1]],
            ['build_InsertQuery_Event', ['eventtype', 'Goal', '', '1junk', 1]],
            ['build_InsertQuery_Event', ['eventtype', 'Goal', '', 1, '999999999999999999999999']],
            ['build_InsertQuery_Position', ['position', 'Defender', ['is_defender'], 0, '', 1, 1]],
            ['build_InsertQuery_Position', ['position', 'Defender', 'is_defender;DROP', 0, '', 1, 1]],
            ['build_InsertQuery_Position', ['position', 'Defender', 'is_defender', ['nested'], '', 1, 1]],
            ['build_InsertQuery_Position', ['position', 'Defender', 'is_defender', 0, '', new \stdClass(), 1]],
            ['build_InsertQuery_Position', ['position', 'Defender', 'is_defender', 0, '', 1, [1]]],
            ['build_InsertQuery_PositionEventType', [[1], 2]],
            ['build_InsertQuery_PositionEventType', [1, new \stdClass()]],
            ['build_InsertQuery_PositionEventType', ['0', 2]],
            ['build_InsertQuery_PositionEventType', [1, '2suffix']],
        ] as [$method, $arguments]) {
            assertBootstrapRejected($model, $method, $arguments);
        }

        $intParser = new \ReflectionMethod($type, 'bootstrapId');
        $textParser = new \ReflectionMethod($type, 'bootstrapText');

        if ($intParser->invoke(null, '00032') !== 32
            || $intParser->invoke(null, '0000', true) !== 0
            || $intParser->invoke(null, 3) !== 3
            || $textParser->invoke(null, 'Goal') !== 'Goal'
            || $textParser->invoke(null, '') !== '') {
            throw new \RuntimeException('Valid bootstrap text and numeric IDs changed.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 bootstrap SQL preflight guards: OK\n";
}
