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

echo "Random Quotes input checks passed.\n";
