<?php
/**
 * Joomla 5/6 module database selector regression: Random Player and rankings.
 *
 * The component resolver recognises modes 0 and 1; other numeric settings
 * such as division identifiers must remain independently configurable.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);

$root = dirname(__DIR__, 2);
$modules = [
    [$root . '/modules/mod_sportsmanagement_club_birthday/src/Helper/ClubBirthdayHelper.php',
     \Diddipoeler\Module\SportsManagementClubBirthday\Site\Helper\ClubBirthdayHelper::class],
    [$root . '/modules/mod_sportsmanagement_randomplayer/src/Helper/RandomPlayerHelper.php',
     \Diddipoeler\Module\SportsManagementRandomPlayer\Site\Helper\RandomPlayerHelper::class],
    [$root . '/modules/mod_sportsmanagement_ranking/src/Helper/RankingHelper.php',
     \Diddipoeler\Module\SportsManagementRanking\Site\Helper\RankingHelper::class],
    [$root . '/modules/mod_sportsmanagement_eventsranking/src/Helper/EventsRankingHelper.php',
     \Diddipoeler\Module\SportsManagementEventsRanking\Site\Helper\EventsRankingHelper::class],
];
$cases = [
    [0, 0], [1, 1], ['0', 0], ['1', 1], ['0000', 0], ['0001', 1],
    [2, null], ['2', null], [7, null], ['0007', null],
    [-1, null], ['-1', null], [false, null], [true, null],
    [[], null], [['1'], null], [(object) ['id' => 1], null],
    ['', null], ['1junk', null], ['1.0', null], [' 1', null],
    ['1 ', null], ['+1', null], [PHP_INT_MAX . '0', null],
    [str_repeat('0', 65) . '1', null],
];

foreach ($modules as [$file, $class]) {
    require_once $file;
    $method = new ReflectionMethod($class, 'databaseSelector');

    foreach ($cases as [$raw, $expected]) {
        $actual = $method->invoke(null, $raw);
        if ($actual !== $expected) {
            throw new RuntimeException(
                $class . ' accepted wrong database mode for ' . var_export($raw, true)
                . '; expected ' . var_export($expected, true)
                . ', actual ' . var_export($actual, true)
            );
        }
    }
}

$numeric = new ReflectionMethod(
    \Diddipoeler\Module\SportsManagementRanking\Site\Helper\RankingHelper::class,
    'nonNegativeOption'
);
if ($numeric->invoke(null, 7) !== 7 || $numeric->invoke(null, '0007') !== 7) {
    throw new RuntimeException('Ranking non-database numeric option regressed.');
}

echo "Joomla 5/6 module database modes 0/1: OK\n";
