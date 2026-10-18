<?php
/**
 * Joomla 5/6 playground plan module settings and legacy ID regression.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

define('_JEXEC', 1);
require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_playgroundplan/src/Helper/PlaygroundPlanHelper.php';

$helper = new \Diddipoeler\Module\SportsManagementPlaygroundPlan\Site\Helper\PlaygroundPlanHelper();
$selector = new ReflectionMethod($helper, 'nonNegativeInteger');
$limit = new ReflectionMethod($helper, 'positiveLimit');

foreach ([
    [0, 0], [1, 1], ['0002', 2], ['0000', 0], [7, 7],
    [-1, 0], ['-1', 0], [true, 0], [false, 0], [[], 0],
    [['2'], 0], [1.5, 0], ['2junk', 0], [' 2', 0],
    ['999999999999999999999999', 0],
] as [$value, $expected]) {
    $actual = $selector->invoke(null, $value);

    if ($actual !== $expected) {
        throw new RuntimeException('Incorrect playground database or season ID normalization for ' . var_export($value, true));
    }
}

foreach ([
    [0, 1], ['0000', 1], [7, 7], ['0007', 7], ['100', 100],
    [101, 100], ['101', 100],
    [-1, 7], ['-1', 7], [true, 7], [[], 7], [['1'], 7],
    ['1.5', 7], ['1e3', 7], ['7junk', 7], [' 2', 7],
    ['999999999999999999999999', 7],
] as [$value, $expected]) {
    $actual = $limit->invoke(null, $value, 7, 100);

    if ($actual !== $expected) {
        throw new RuntimeException('Invalid playground fixture count for ' . var_export($value, true));
    }
}

echo "Playground plan module parameter validation passed.\n";
