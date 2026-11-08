<?php
/**
 * Regression tests for Joomla 5/6's pure in-memory ranking calculator.
 *
 * Verifies recorded results, input immutability, allowed-team filtering and
 * stored final-table rank ordering without a Joomla installation.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);

$root = dirname(__DIR__, 2);
require_once $root . '/site/src/Service/RankingRow.php';
require_once $root . '/site/src/Service/RankingCalculator.php';

use Diddipoeler\Component\SportsManagement\Site\Service\RankingCalculator;
use Diddipoeler\Component\SportsManagement\Site\Service\RankingRow;

$calculator = new RankingCalculator();
$home = new RankingRow(10);
$home->name = 'Home';
$home->team = (object) ['name' => 'Home'];
$away = new RankingRow(20);
$away->name = 'Away';
$away->team = (object) ['name' => 'Away'];
$seed = [10 => $home, 20 => $away];
$project = (object) [
    'sport_type_name' => 'COM_SPORTSMANAGEMENT_ST_SOCCER',
    'allow_add_time' => 0,
    'points_after_regular_time' => '3,1,0',
];
$config = ['ranking_order' => 'POINTS, DIFF, FOR', 'ranking_sort_order' => 'DESC, DESC, DESC'];
$match = (object) [
    'projectteam1_id' => 10,
    'projectteam2_id' => 20,
    'decision' => 0,
    'home_score' => 2,
    'away_score' => 1,
];

$result = $calculator->calculate($seed, [$match], $project, $config);

if (array_keys($result) !== [10, 20]
    || $result[10]->cnt_won !== 1
    || $result[20]->cnt_lost !== 1
    || $result[10]->getPoints() !== 3.0
    || $result[20]->getPoints() !== 0.0
    || $result[10]->rank !== 1
    || $result[20]->rank !== 2
    || $seed[10]->cnt_matches !== 0
    || $seed[20]->cnt_matches !== 0
    || $result[10] === $seed[10]
    || $result[10]->team === $seed[10]->team) {
    throw new RuntimeException('Ranking calculator failed normal results or input immutability.');
}

$filtered = [10 => new RankingRow(10), 20 => new RankingRow(20)];
$calculator->applyMatches($filtered, [$match], $project, $project->sport_type_name, [10]);

if ($filtered[10]->cnt_matches !== 0 || $filtered[20]->cnt_matches !== 0) {
    throw new RuntimeException('Head-to-head subset must exclude games outside selected teams.');
}

$home->finaltablerank = 2;
$away->finaltablerank = 1;
$final = $calculator->calculate($seed, [], $project, $config);

if (array_keys($final) !== [20, 10]
    || $final[20]->rank !== 1
    || $final[10]->rank !== 2) {
    throw new RuntimeException('Stored final standings must use ascending final-table rank.');
}

echo "Joomla 5/6 pure ranking calculator OK\n";
