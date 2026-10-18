<?php
/**
 * Joomla 5/6 current-season ID normalization compatibility tests.
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
$normalise = new ReflectionMethod($helper, 'normaliseIds');

foreach ([
    [['0007', '7', '0002', 3], [7, 2, 3]],
    ['0001,0002; 03', [1, 2, 3]],
    [[0, '000', -4, '0008', 'x', '2garbage', '2.5', true], [8]],
    [['999999999999999999999999', '0009'], [9]],
    [[[], ['1'], (object) ['id' => 1]], []],
    ['', []],
    [null, []],
    [[1, 1, '0001'], [1]],
] as [$input, $expected]) {
    $actual = $normalise->invoke($helper, $input);

    if ($actual !== $expected) {
        throw new RuntimeException(
            'Current-season IDs were normalized incorrectly for ' . var_export($input, true)
        );
    }
}

echo "Current-season zero-padded ID regression passed.\n";
