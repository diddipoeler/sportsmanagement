<?php
/**
 * Regression checks for Team Stats Ranking numeric module configuration.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);
require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_teamstats_ranking/src/Helper/TeamStatsRankingHelper.php';

$class = \Diddipoeler\Module\SportsManagementTeamStatsRanking\Site\Helper\TeamStatsRankingHelper::class;
$setting = new ReflectionMethod($class, 'numericSetting');

foreach ([['0003', 0, PHP_INT_MAX, 3], [0, 0, PHP_INT_MAX, 0],
    [true, 0, PHP_INT_MAX, 0], [['1'], 0, PHP_INT_MAX, 0],
    [PHP_INT_MAX . '0', 0, PHP_INT_MAX, 0],
    ['0009', 5, 100, 9], [0, 5, 100, 1], [500, 5, 100, 100],
    ['invalid', 5, 100, 5], [false, 5, 100, 5]] as [$raw, $default, $maximum, $expected]) {
    if ($setting->invoke(null, $raw, $default, $maximum) !== $expected) {
        fwrite(STDERR, 'Team Stats Ranking numeric setting regression.' . PHP_EOL);
        exit(1);
    }
}

$precision = new ReflectionMethod($class, 'rankingPrecision');
foreach ([
    [0, 0], ['0000', 0], ['0008', 8], [12, 8],
    [true, 2], [false, 2], [['3'], 2], ['3garbage', 2],
    [PHP_INT_MAX . '0', 2],
] as [$raw, $expected]) {
    if ($precision->invoke(null, $raw) !== $expected) {
        fwrite(STDERR, 'Team Stats Ranking decimal precision regression.' . PHP_EOL);
        exit(1);
    }
}

$weights = new ReflectionMethod($class, 'weightFactors');
foreach ([
    ['1, 0.5, -2', [1.0, 0.5, -2.0]],
    ['0,0', [0.0, 0.0]],
    ['1e2, 2.5', [100.0, 2.5]],
    [['1', '2'], []],
    [true, []],
    ['1,broken', []],
    ['1,,2', []],
    ['1e309', []],
] as [$raw, $expected]) {
    if ($weights->invoke(null, $raw) !== $expected) {
        fwrite(STDERR, 'Team Stats Ranking weighting factor regression.' . PHP_EOL);
        exit(1);
    }
}

$helper = (new ReflectionClass($class))->newInstanceWithoutConstructor();
$ids = new ReflectionMethod($class, 'ids');

foreach ([
    [true, []],
    [new stdClass(), []],
    ['1, 002;3', [1, 2, 3]],
    [['5', ['6'], 'not-a-number', false], [5, 6]],
] as [$raw, $expected]) {
    if ($ids->invoke($helper, $raw) !== $expected) {
        fwrite(STDERR, 'Team Stats Ranking identifier selection regression.' . PHP_EOL);
        exit(1);
    }
}

$valueMap = new ReflectionMethod($class, 'valueMap');
$records = [
    (object) ['team_id' => '002', 'total' => '3.5'],
    null,
    ['team_id' => 4, 'total' => 20],
    (object) ['team_id' => 0, 'total' => 2],
    (object) ['team_id' => 3, 'total' => 0],
    (object) ['team_id' => 4, 'total' => ['bad']],
    (object) ['team_id' => 5, 'total' => INF],
    (object) ['team_id' => 6, 'total' => '1e309'],
];
if ($valueMap->invoke($helper, $records) !== [2 => 3.5, 3 => 0.0]) {
    fwrite(STDERR, 'Team Stats Ranking aggregate row validation regression.' . PHP_EOL);
    exit(1);
}

echo "Team Stats Ranking numeric settings passed.\n";
