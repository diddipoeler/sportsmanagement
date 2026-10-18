<?php
/**
 * Regression test for Joomla 5/6 current-season database selector inputs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

define('_JEXEC', 1);

require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_act_season/src/Helper/ActSeasonHelper.php';

$helper = new \Diddipoeler\Module\SportsManagementActSeason\Site\Helper\ActSeasonHelper();
$method = new ReflectionMethod($helper, 'normaliseDatabaseSelector');

$cases = [
    [0, 0],
    [1, 1],
    ['0', 0],
    ['2', 2],
    ['0002', 2],
    ['0000', 0],
    ['0008', 8],
    [-1, 0],
    ['-1', 0],
    ['abc', 0],
    ['', 0],
    ['1.5', 0],
    [' 1', 0],
    [[], 0],
    [[1], 0],
    [true, 0],
    [1.5, 0],
    ['9999999999999999999999999999', 0],
];

foreach ($cases as [$value, $expected]) {
    $actual = $method->invoke($helper, $value);

    if ($actual !== $expected) {
        fwrite(STDERR, 'Unexpected database selector for ' . var_export($value, true) . ': ' . var_export($actual, true) . PHP_EOL);
        exit(1);
    }
}

echo "Current-season database selector validation passed.\n";
