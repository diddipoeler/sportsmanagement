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

// Legacy imports can contain non-scalar and ambiguous project-team IDs.
// The pure calculator must not cast these into valid team row keys.
$badFixtures = [
    (object) ['projectteam1_id' => ['nested'], 'projectteam2_id' => 20, 'home_score' => 8, 'away_score' => 0],
    (object) ['projectteam1_id' => new \stdClass(), 'projectteam2_id' => 20, 'home_score' => 8, 'away_score' => 0],
    (object) ['projectteam1_id' => '10notvalid', 'projectteam2_id' => 20, 'home_score' => 8, 'away_score' => 0],
    (object) ['projectteam1_id' => false, 'projectteam2_id' => 20, 'home_score' => 8, 'away_score' => 0],
    (object) ['projectteam1_id' => 10, 'projectteam2_id' => -1, 'home_score' => 8, 'away_score' => 0],
    (object) ['projectteam1_id' => 10, 'home_score' => 8, 'away_score' => 0],
    'unexpected scalar match',
];

set_error_handler(static function (int $severity, string $message): never {
    throw new \ErrorException($message, 0, $severity);
});

try {
    $ignored = [10 => new RankingRow(10), 20 => new RankingRow(20)];
    $calculator->applyMatches($ignored, $badFixtures, $project, $project->sport_type_name);

    if ($ignored[10]->cnt_matches !== 0 || $ignored[20]->cnt_matches !== 0) {
        throw new RuntimeException('Corrupt team IDs contributed to the normal ranking.');
    }

    $smallBore = [10 => new RankingRow(10), 20 => new RankingRow(20)];
    $oneSided = (object) [
        'projectteam1_id' => 10,
        'projectteam2_id' => ['invalid'],
        'home_score' => 400,
        'away_score' => 0,
        'roundcode' => 1,
    ];
    $calculator->applyMatches(
        $smallBore,
        [$oneSided],
        $project,
        'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION'
    );

    if ($smallBore[10]->shooterrings !== 400.0 || $smallBore[20]->shooterrings !== 0.0) {
        throw new RuntimeException('One-sided small-bore match no longer scores its valid team.');
    }

    $bothInvalid = [10 => new RankingRow(10), 20 => new RankingRow(20)];
    $calculator->applyMatches(
        $bothInvalid,
        [(object) ['projectteam1_id' => [], 'projectteam2_id' => new \stdClass()]],
        $project,
        'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION'
    );

    if ($bothInvalid[10]->shooterrings !== 0.0 || $bothInvalid[20]->shooterrings !== 0.0) {
        throw new RuntimeException('Malformed small-bore IDs affected ranking rows.');
    }
} finally {
    restore_error_handler();
}

// Bad legacy point rules must not overwrite 3/1/0 defaults or emit
// warnings when imported config unexpectedly contains nested structures.
$pointRules = new \ReflectionMethod(RankingCalculator::class, 'pointsForResultType');

set_error_handler(static function (int $severity, string $message): never {
    throw new \ErrorException($message, 0, $severity);
});

try {
    $invalidPoints = (object) [
        'points_after_regular_time' => ['nested'],
        'points_after_add_time' => 'invalid,2,',
        'points_after_penalty' => '5,invalid,1',
    ];

    if ($pointRules->invoke($calculator, $invalidPoints, 0) !== [3.0, 1.0, 0.0]
        || $pointRules->invoke($calculator, $invalidPoints, 1) !== [3.0, 2.0, 0.0]
        || $pointRules->invoke($calculator, $invalidPoints, 2) !== [5.0, 1.0, 1.0]) {
        throw new RuntimeException('Invalid imported ranking point settings were not defaulted.');
    }

    $validPoints = (object) ['points_after_regular_time' => '2.5, 1, -1'];

    if ($pointRules->invoke($calculator, $validPoints, 0) !== [2.5, 1.0, -1.0]) {
        throw new RuntimeException('Valid custom ranking points changed after validation.');
    }
} finally {
    restore_error_handler();
}

// Corrupt or absent scores must not be counted as zero-zero fixtures.
$scoreCases = [
    (object) ['projectteam1_id' => 10, 'projectteam2_id' => 20, 'home_score' => ['nested'], 'away_score' => 2],
    (object) ['projectteam1_id' => 10, 'projectteam2_id' => 20, 'home_score' => 'not-a-score', 'away_score' => 2],
    (object) ['projectteam1_id' => 10, 'projectteam2_id' => 20, 'home_score' => 2],
    (object) ['projectteam1_id' => 10, 'projectteam2_id' => 20, 'home_score' => '1e9999', 'away_score' => 2],
];

set_error_handler(static function (int $severity, string $message): never {
    throw new \ErrorException($message, 0, $severity);
});

try {
    $unscored = [10 => new RankingRow(10), 20 => new RankingRow(20)];
    $calculator->applyMatches($unscored, $scoreCases, $project, $project->sport_type_name);

    if ($unscored[10]->cnt_matches !== 0 || $unscored[20]->cnt_matches !== 0) {
        throw new RuntimeException('Malformed scores counted as published matches.');
    }

    // Keep numeric results while discarding nested or non-finite bonuses.
    $otherSport = (object) [
        'points_after_regular_time' => '3,1,0',
        'allow_add_time' => 0,
    ];
    $bonusRows = [10 => new RankingRow(10), 20 => new RankingRow(20)];
    $calculator->applyMatches($bonusRows, [
        (object) [
            'projectteam1_id' => 10,
            'projectteam2_id' => 20,
            'home_score' => '2',
            'away_score' => '1',
            'home_bonus' => ['broken'],
            'away_bonus' => '1e9999',
        ],
    ], $otherSport, 'COM_SPORTSMANAGEMENT_ST_VOLLEYBALL');

    if ($bonusRows[10]->cnt_matches !== 1
        || $bonusRows[10]->sum_points !== 3.0
        || $bonusRows[10]->bonus_points !== 0.0
        || $bonusRows[20]->bonus_points !== 0.0) {
        throw new RuntimeException('Malformed bonuses altered ranking points.');
    }

    $single = [10 => new RankingRow(10), 20 => new RankingRow(20)];
    $calculator->applyMatches($single, [
        (object) [
            'projectteam1_id' => 10,
            'projectteam2_id' => 20,
            'home_score' => null,
            'away_score' => 300,
            'roundcode' => ['invalid'],
        ],
    ], $project, 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION');

    if ($single[10]->shooterrings !== 0.0 || $single[20]->shooterrings !== 300.0) {
        throw new RuntimeException('A valid one-sided shooting result was discarded.');
    }
} finally {
    restore_error_handler();
}

// Optional match details and shootout metadata from legacy imports must not
// trigger PHP warnings or corrupt scoring when malformed.
set_error_handler(static function (int $severity, string $message): never {
    throw new \ErrorException($message, 0, $severity);
});

try {
    $extraProject = (object) [
        'allow_add_time' => 1,
        'points_after_regular_time' => '3,1,0',
    ];
    $statRows = [10 => new RankingRow(10), 20 => new RankingRow(20)];
    $calculator->applyMatches($statRows, [
        (object) [
            'projectteam1_id' => 10,
            'projectteam2_id' => 20,
            'home_score' => 2,
            'away_score' => 1,
            'l1' => ['invalid'],
            'l2' => new \stdClass(),
            'mp1' => ['invalid'],
            'mp2' => '1e9999',
            'se1' => new \stdClass(),
            'se2' => ['invalid'],
            'ga1' => 'not-a-number',
            'ga2' => 2,
            'match_result_type' => ['invalid'],
        ],
    ], $extraProject, 'COM_SPORTSMANAGEMENT_ST_VOLLEYBALL');

    if ($statRows[10]->cnt_matches !== 1
        || $statRows[10]->sum_team1_legs !== 0.0
        || $statRows[10]->sum_team1_matchpoint !== 0.0
        || $statRows[10]->sum_team1_sets !== 0.0
        || $statRows[10]->sum_team1_games !== 0.0
        || $statRows[20]->sum_team1_games !== 2.0) {
        throw new RuntimeException('Malformed supplementary scores affected rankings.');
    }

    $tieRows = [10 => new RankingRow(10), 20 => new RankingRow(20)];
    $calculator->applyMatches($tieRows, [
        (object) [
            'projectteam1_id' => 10,
            'projectteam2_id' => 20,
            'home_score' => 1,
            'away_score' => 1,
            'home_score_ot' => ['invalid'],
            'away_score_ot' => new \stdClass(),
            'home_score_so' => ['invalid'],
            'away_score_so' => '1e9999',
            'match_result_type' => 1,
        ],
    ], $extraProject, 'COM_SPORTSMANAGEMENT_ST_VOLLEYBALL');

    if ($tieRows[10]->cnt_matches !== 1
        || $tieRows[10]->cnt_wot !== 0
        || $tieRows[20]->cnt_wot !== 0) {
        throw new RuntimeException('Malformed overtime data affected the ranking.');
    }

    $faustball = [10 => new RankingRow(10), 20 => new RankingRow(20)];
    $calculator->applyMatches($faustball, [
        (object) [
            'projectteam1_id' => 10,
            'projectteam2_id' => 20,
            'home_score' => 2,
            'away_score' => 1,
            'ls1' => '3;invalid;4;1e9999',
            'ls2' => ['invalid'],
        ],
    ], $project, 'COM_SPORTSMANAGEMENT_ST_FAUSTBALL');

    if ($faustball[10]->sum_team1_balls !== 7.0
        || $faustball[20]->sum_team1_balls !== 0.0) {
        throw new RuntimeException('Invalid Faustball match segments were not ignored.');
    }
} finally {
    restore_error_handler();
}

// Administrative decisions must remain integer-coded after validation.
// A corrupted fractional decision must not be treated as a valid decision.
set_error_handler(static function (int $severity, string $message): never {
    throw new \ErrorException($message, 0, $severity);
});

try {
    $decided = [10 => new RankingRow(10), 20 => new RankingRow(20)];
    $calculator->applyMatches($decided, [
        (object) [
            'projectteam1_id' => 10,
            'projectteam2_id' => 20,
            'decision' => '1',
            'team_won' => 1,
            'home_score_decision' => 3,
            'away_score_decision' => 0,
        ],
        (object) [
            'projectteam1_id' => 10,
            'projectteam2_id' => 20,
            'decision' => '1.5',
            'team_won' => 1,
        ],
    ], $project, $project->sport_type_name);

    if ($decided[10]->cnt_matches !== 1
        || $decided[20]->cnt_matches !== 1
        || $decided[10]->cnt_won !== 1
        || $decided[20]->cnt_lost !== 1
        || $decided[10]->sum_points !== 3.0) {
        throw new RuntimeException('Administrative ranking decisions lost integer semantics.');
    }
} finally {
    restore_error_handler();
}

// Legacy template registry rows can contain nested sort settings. Ranking
// must retain its default criteria and the original seed objects unchanged.
set_error_handler(static function (int $severity, string $message): never {
    throw new \ErrorException($message, 0, $severity);
});

try {
    $badSortConfig = [
        'ranking_order' => ['invalid', 'nested'],
        'ranking_sort_order' => new \stdClass(),
    ];

    $defaultSort = $calculator->calculate(
        $seed,
        [$match],
        $project,
        ['ranking_order' => 'POINTS, PLAYEDASC, DIFF, FOR', 'ranking_sort_order' => 'DESC, ASC, DESC, DESC']
    );
    $fallbackSort = $calculator->calculate($seed, [$match], $project, $badSortConfig);

    if (array_keys($fallbackSort) !== array_keys($defaultSort)
        || $fallbackSort[10]->rank !== 1
        || $fallbackSort[20]->rank !== 2
        || $seed[10]->cnt_matches !== 0) {
        throw new RuntimeException('Corrupt ranking sort settings did not use safe defaults.');
    }

    $emptySort = $calculator->calculate(
        $seed,
        [$match],
        $project,
        ['ranking_order' => '', 'ranking_sort_order' => '']
    );

    if (array_keys($emptySort) !== array_keys($defaultSort)) {
        throw new RuntimeException('Empty ranking sort settings did not use defaults.');
    }
} finally {
    restore_error_handler();
}

echo "Joomla 5/6 pure ranking calculator OK\n";
