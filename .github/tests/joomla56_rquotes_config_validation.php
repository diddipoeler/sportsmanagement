<?php
/**
 * Joomla 5/6 random quote module selector and amount configuration tests.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

define('_JEXEC', 1);
require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_rquotes/src/Helper/RquotesHelper.php';

$helper = new \Diddipoeler\Module\SportsManagementRquotes\Site\Helper\RquotesHelper();
$selector = new ReflectionMethod($helper, 'normaliseDatabaseSelector');
$limit = new ReflectionMethod($helper, 'normalisePositiveLimit');

foreach ([
    [0, 0], [1, 1], ['002', 2], ['000', 0], ['7', 7],
    [-1, 0], ['-1', 0], [true, 0], [false, 0], [[], 0],
    [['1'], 0], ['2junk', 0], ['3.5', 0], [' 2', 0],
    ['9999999999999999999999999', 0],
] as [$input, $expected]) {
    $actual = $selector->invoke($helper, $input);

    if ($actual !== $expected) {
        throw new \RuntimeException('Random quote database selector invalid for ' . var_export($input, true));
    }
}

foreach ([
    [1, 1], [0, 1], ['0000', 1], ['0007', 7], [2, 2],
    [5000, 2500], ['5000', 2500],
    [-1, 2], ['-1', 2], [true, 2], [false, 2], [[], 2],
    [['1'], 2], ['2junk', 2], ['1e3', 2],
    ['999999999999999999999999', 2],
] as [$input, $expected]) {
    $actual = $limit->invoke($helper, $input, 2, 2500);

    if ($actual !== $expected) {
        throw new \RuntimeException('Random quote display count invalid for ' . var_export($input, true));
    }
}

echo "Joomla 5/6 random quote module parameters passed.\n";
