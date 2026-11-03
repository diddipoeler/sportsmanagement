<?php
/**
 * Regression checks for Random Quotes legacy numeric parameters.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);
require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_rquotes/src/Helper/RquotesHelper.php';

$class = \Diddipoeler\Module\SportsManagementRquotes\Site\Helper\RquotesHelper::class;
$selector = new ReflectionMethod($class, 'normaliseDatabaseSelector');
$limit = new ReflectionMethod($class, 'normalisePositiveLimit');

foreach ([['0002', 2], [0, 0], [true, 0], [['2'], 0], ['2junk', 0], [PHP_INT_MAX . '0', 0]] as [$value, $expected]) {
    if ($selector->invoke(null, $value) !== $expected) {
        fwrite(STDERR, 'Random Quotes database selector regression.' . PHP_EOL);
        exit(1);
    }
}

foreach ([['0003', 3], [0, 1], [300, 10], [['2'], 2], ['broken', 2], [PHP_INT_MAX . '0', 2]] as [$value, $expected]) {
    if ($limit->invoke(null, $value, 2, 10) !== $expected) {
        fwrite(STDERR, 'Random Quotes limit regression.' . PHP_EOL);
        exit(1);
    }
}

// A rotation position previously saved for a larger quote collection must
// remain distinguishable from the clamped position and be persisted again.
foreach ([['9', 3, 9], [7, 2, 7], ['0002', 3, 2]] as [$saved, $count, $original]) {
    $bounded = $limit->invoke(null, $saved, 1, $count);
    $unbounded = $limit->invoke(null, $saved, 1, PHP_INT_MAX);
    if ($unbounded !== $original || $bounded !== min($count, $original)) {
        fwrite(STDERR, 'Random Quotes saved rotation position regression.' . PHP_EOL);
        exit(1);
    }
}

$matchesSavedPosition = new ReflectionMethod($class, 'savedPositionMatches');
foreach ([[1, 1, true], ['0002', 2, true], ['9', 3, false], [0, 1, false], ['000', 1, false], [['1'], 1, false], ['1junk', 1, false], [PHP_INT_MAX . '0', 1, false]] as [$saved, $current, $expected]) {
    if ($matchesSavedPosition->invoke(null, $saved, $current) !== $expected) {
        fwrite(STDERR, 'Random Quotes persisted rotation validation regression.' . PHP_EOL);
        exit(1);
    }
}

$nextSequentialIndex = new ReflectionMethod($class, 'nextSequentialIndex');
foreach ([
    [0, 3, 1], ['0002', 3, 0], [2, 3, 0],
    [3, 3, null], [-1, 3, null], ['2invalid', 3, null],
    [true, 3, null], [['1'], 3, null], [null, 3, null],
    [PHP_INT_MAX . '0', 3, null], [0, 0, null],
] as [$raw, $count, $expected]) {
    if ($nextSequentialIndex->invoke(null, $raw, $count) !== $expected) {
        fwrite(STDERR, 'Random Quotes sequential cookie index regression.' . PHP_EOL);
        exit(1);
    }
}

$normaliseIds = new ReflectionMethod($class, 'normaliseIds');
$helper = (new ReflectionClass($class))->newInstanceWithoutConstructor();
foreach ([
    [['0002', '03:league', '2', 5], [2, 3, 5]],
    ['0008:league, 9; 010', [8, 9, 10]],
    [['000', '0:invalid', '-2', '4invalid', '7:valid'], [7]],
    [[true, ['3'], '5.2', PHP_INT_MAX . '0'], []],
    [['1:valid', '1:duplicate', '002:other'], [1, 2]],
] as [$raw, $expected]) {
    if ($normaliseIds->invoke($helper, $raw) !== $expected) {
        fwrite(STDERR, 'Random Quotes zero-padded category selection regression.' . PHP_EOL);
        exit(1);
    }
}

echo "Random Quotes input checks passed.\n";
