<?php
/**
 * Regression tests for Top Tipper input validation in Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);
require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_top_tipper/src/Helper/TopTipperHelper.php';

$class = \Diddipoeler\Module\SportsManagementTopTipper\Site\Helper\TopTipperHelper::class;
$helper = (new ReflectionClass($class))->newInstanceWithoutConstructor();
$extractId = new ReflectionMethod($class, 'extractId');
$boundedLimit = new ReflectionMethod($class, 'boundedLimit');
$databaseSelector = new ReflectionMethod($class, 'databaseSelector');

foreach ([['12', 12], ['0012:team', 12], [['7'], 7], ['12bad', 0], ['0', 0], [PHP_INT_MAX . '0', 0], [new stdClass(), 0]] as [$value, $expected]) {
    if ($extractId->invoke($helper, $value) !== $expected) {
        fwrite(STDERR, 'Top Tipper ID validation regression.' . PHP_EOL);
        exit(1);
    }
}

foreach ([[0, 1], ['0007', 7], [999, 100], ['invalid', 5], [[], 5]] as [$value, $expected]) {
    if ($boundedLimit->invoke(null, $value) !== $expected) {
        fwrite(STDERR, 'Top Tipper limit validation regression.' . PHP_EOL);
        exit(1);
    }
}

foreach ([['0001', 1], [0, 0], ['-1', null], [['1'], null], [PHP_INT_MAX . '0', null]] as [$value, $expected]) {
    if ($databaseSelector->invoke(null, $value) !== $expected) {
        fwrite(STDERR, 'Top Tipper database selector regression.' . PHP_EOL);
        exit(1);
    }
}

echo "Top Tipper input checks passed.\n";
