<?php
/**
 * Regression test for safe administrator individual-match bulk-ID normalisation.
 *
 * Invokes the real private validator without bootstrapping Joomla, and ensures
 * malformed input cannot accidentally select a different single-match row.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace {
    define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/admin/src/Service/IndividualMatchAdminService.php';

    $database = new class implements \Joomla\Database\DatabaseInterface {};
    $writer = new \Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchAdminService($database);
    $normalise = new ReflectionMethod($writer, 'normaliseIds');

    $testCases = [
        [[3, '0003', 5, '5'], [3, 5]],
        [['0002', '3', '2'], [2, 3]],
        [['1invalid', true, false, 0, -1, ['3'], '3.14'], []],
        [[PHP_INT_MAX . '0', null, new stdClass(), '', '0000'], []],
        [[' 004 ', '0005', 4, 5, '0006'], [4, 5, 6]],
    ];

    foreach ($testCases as [$input, $expected]) {
        if ($normalise->invoke($writer, $input) !== $expected) {
            throw new \RuntimeException('Individual-match selection ID validation regression.');
        }
    }

    $invalid = [true, [['id' => 1]], '2invalid', '0', PHP_INT_MAX . '0'];

    if ($writer->setPublished($invalid, 1) !== false
        || $writer->deleteSingles($invalid) !== false) {
        throw new \RuntimeException('Malformed batch IDs must not trigger database writes.');
    }

    echo "Individual-match bulk selection ID validation OK\n";
}
