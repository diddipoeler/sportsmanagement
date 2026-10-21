<?php
/**
 * Joomla 5/6 JSON calendar feed bounds for scalar, array and CSV IDs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementModel {}
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Model/JsonfeedModel.php';

    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\JsonfeedModel();
    $normalize = new \ReflectionMethod($model, 'normalizeCalendarIds');

    $integers = range(1, 200);
    $result = $normalize->invoke($model, $integers);

    if ($result !== range(1, 50)) {
        throw new \RuntimeException('Integer array exceeded the 50-calendar limit.');
    }

    $csv = implode(',', range(1, 200));
    if ($normalize->invoke($model, $csv) !== range(1, 50)) {
        throw new \RuntimeException('CSV calendar selection exceeded the limit.');
    }

    foreach ([
        [['001', '2', '2', '3'], [2, 3]],
        [[7, 7, 8], [7, 8]],
        [[-2, true, [], '7garbage', 4], [4]],
        [null, []],
        ['', []],
        [['999999999999999999999999999', 12], [12]],
    ] as [$input, $expected]) {
        if ($normalize->invoke($model, $input) !== $expected) {
            throw new \RuntimeException('JSON calendar ID normalization regressed.');
        }
    }

    echo "JSON calendar selection count and ID validation OK\n";
}
