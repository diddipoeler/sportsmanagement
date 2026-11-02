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

echo "Team Stats Ranking numeric settings passed.\n";
