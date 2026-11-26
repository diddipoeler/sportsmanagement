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
    ['2', 0],
    ['0002', 0],
    ['0001', 1],
    ['0000', 0],
    ['0008', 0],
    [str_repeat('0', 64) . '1', 0],
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

$valid = new ReflectionMethod($helper, 'isValidDatabaseSelector');
foreach ([0, 1, '0000', '0001'] as $value) {
    if (!$valid->invoke(null, $value)) {
        throw new RuntimeException('Supported current-season mode was rejected.');
    }
}
foreach ([2, '0002', '0008', true, ['1'], '1junk', str_repeat('0', 65)] as $value) {
    if ($valid->invoke(null, $value)) {
        throw new RuntimeException('Unsupported current-season mode was accepted.');
    }
}
$source = file_get_contents(dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_act_season/src/Helper/ActSeasonHelper.php');
if (!str_contains($source, "quoteName('pro.published') . ' = 1'")) {
    throw new RuntimeException('Unpublished projects are not excluded from the season query.');
}

echo "Current-season database selector and publication validation passed.\n";
