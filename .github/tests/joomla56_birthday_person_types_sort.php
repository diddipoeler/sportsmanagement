<?php
/**
 * Verify Joomla 5/6 birthday options and sorting reject structured input.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace {
    define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_birthday/src/Helper/BirthdayHelper.php';

    $class = \Diddipoeler\Module\SportsManagementBirthday\Site\Helper\BirthdayHelper::class;
    $select = new \ReflectionMethod($class, 'personTypes');

    /**
     * Verify a configured person-type selector produces the expected filter.
     *
     * @param mixed $value Untrusted legacy Joomla module option.
     * @param array<int,int> $expected Native person-type IDs.
     * @return void
     */
    function assertBirthdayPersonTypes(mixed $value, array $expected): void
    {
        global $select;

        $actual = $select->invoke(null, $value);

        if ($actual !== $expected) {
            throw new \RuntimeException('Unexpected birthday filter for ' . get_debug_type($value));
        }
    }

    set_error_handler(
        /**
         * Raise any PHP warning caused by an invalid module option.
         *
         * @param int $severity PHP diagnostic level.
         * @param string $message PHP warning or notice.
         * @return never Always throws to fail the regression.
         */
        static function (int $severity, string $message): never {
            throw new \ErrorException($message, 0, $severity);
        }
    );

    try {
        assertBirthdayPersonTypes(1, [1]);
        assertBirthdayPersonTypes(2, [2]);
        assertBirthdayPersonTypes('0001', [1]);
        assertBirthdayPersonTypes('02', [2]);
        assertBirthdayPersonTypes(0, [1, 2]);
        assertBirthdayPersonTypes('0', [1, 2]);
        assertBirthdayPersonTypes(['1'], [1, 2]);
        assertBirthdayPersonTypes(new \stdClass(), [1, 2]);
        assertBirthdayPersonTypes(true, [1, 2]);
        assertBirthdayPersonTypes('1junk', [1, 2]);
        assertBirthdayPersonTypes('-1', [1, 2]);
        assertBirthdayPersonTypes('99999999999999999999999999', [1, 2]);

        $rows = [
            ['id' => 1, 'days_to_birthday' => 4, 'age' => 20],
            ['id' => 2, 'days_to_birthday' => 4, 'age' => 60],
            ['id' => 3, 'days_to_birthday' => 2, 'age' => 30],
        ];

        $descending = $class::sortPersons($rows, '-');
        $ascending = $class::sortPersons($rows, ['-']);

        if (array_column($descending, 'id') !== [3, 2, 1]
            || array_column($ascending, 'id') !== [3, 1, 2]) {
            throw new \RuntimeException('Birthday sorting direction no longer matches safe legacy semantics.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 birthday person type and sorting: OK\n";
}
