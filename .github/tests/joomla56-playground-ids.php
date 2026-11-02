<?php
/**
 * Regression tests for strict playground project and database record IDs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);
require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_playground_ticker/src/Helper/PlaygroundTickerHelper.php';

$class = \Diddipoeler\Module\SportsManagementPlaygroundTicker\Site\Helper\PlaygroundTickerHelper::class;
$projectId = new ReflectionMethod($class, 'projectId');
$isUnfiltered = new ReflectionMethod($class, 'isUnfilteredProject');

$max = (string) PHP_INT_MAX;
$overflow = $max . '0';
$cases = [
    ['12', 12],
    ['0012:stadion', 12],
    [12, 12],
    ['0', 0],
    ['0:stadion', 0],
    ['12invalid', 0],
    [['12'], 0],
    [$overflow, 0],
    [$overflow . ':stadion', 0],
];

foreach ($cases as [$input, $expected]) {
    if ($projectId->invoke(null, $input) !== $expected) {
        fwrite(STDERR, 'Playground project ID validation regression.' . PHP_EOL);
        exit(1);
    }
}

foreach ([0, '0', '000', null, ''] as $input) {
    if (!$isUnfiltered->invoke(null, $input)) {
        fwrite(STDERR, 'Explicit all-project selection was rejected.' . PHP_EOL);
        exit(1);
    }
}

foreach (['not-an-id', '2broken', ['0'], $overflow] as $input) {
    if ($isUnfiltered->invoke(null, $input)) {
        fwrite(STDERR, 'Malformed project selection was treated as all projects.' . PHP_EOL);
        exit(1);
    }
}

echo "Playground identifier checks passed.\n";
