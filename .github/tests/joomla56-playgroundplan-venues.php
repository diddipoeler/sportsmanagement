<?php
/**
 * Regression checks for Playground Plan venue selection and malformed fields.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);
require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_playgroundplan/src/Helper/PlaygroundPlanHelper.php';

$class = \Diddipoeler\Module\SportsManagementPlaygroundPlan\Site\Helper\PlaygroundPlanHelper::class;
$helper = (new ReflectionClass($class))->newInstanceWithoutConstructor();
$select = new ReflectionMethod($class, 'playground');

$cases = [
    [(object) ['playground_id' => '12', 'playground_alias' => 'main', 'playground_name' => 'Main', 'playground_picture' => 'main.png'], [12, 'main', 'Main', 'main.png']],
    [(object) ['playground_id' => ['12'], 'team_playground_id' => 3, 'team_playground_name' => 'Second'], [3, '', 'Second', '']],
    [(object) ['playground_id' => PHP_INT_MAX . '0', 'club_playground_id' => '5', 'club_playground_alias' => ['bad']], [5, '', '', '']],
    [(object) ['playground_id' => 'nope', 'team_playground_id' => 0], [0, '', '', '']],
];

foreach ($cases as [$row, $expected]) {
    if ($select->invoke($helper, $row) !== $expected) {
        fwrite(STDERR, 'Playground Plan venue selection regression.' . PHP_EOL);
        exit(1);
    }
}

echo "Playground Plan venue checks passed.\n";
