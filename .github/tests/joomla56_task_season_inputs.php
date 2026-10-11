<?php
/**
 * Joomla 5/6 regression checks for controller task validation and season IDs.
 *
 * Usage: php .github/tests/joomla56_task_season_inputs.php
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);
$root = dirname(__DIR__, 2);
require_once $root . '/modules/mod_sportsmanagement_act_season/src/Helper/ActSeasonHelper.php';

$helper = new \Diddipoeler\Module\SportsManagementActSeason\Site\Helper\ActSeasonHelper();
$method = new ReflectionMethod($helper, 'normaliseIds');

$cases = [
    ['1, 2;3', [1, 2, 3]],
    [[1, '02', 2, 0, -1], [1, 2]],
    [['junk', '4abc', [], ['5'], new stdClass(), '6'], [6]],
    [['999999999999999999999999', '7', '7'], [7]],
    [null, []],
];

foreach ($cases as [$input, $expected]) {
    $actual = $method->invoke($helper, $input);
    if ($actual !== $expected) {
        fwrite(STDERR, 'Season IDs: expected ' . json_encode($expected)
            . ', got ' . json_encode($actual) . PHP_EOL);
        exit(1);
    }
}

foreach (['site/sportsmanagement.php', 'admin/sportsmanagement.php'] as $path) {
    $source = file_get_contents($root . '/' . $path);
    if ($source === false || !str_contains($source, "str_contains(\$task, '.')")) {
        fwrite(STDERR, $path . ': malformed dotted task guard missing' . PHP_EOL);
        exit(1);
    }
}

echo "Joomla 5/6 task and season input regression checks passed.\n";
