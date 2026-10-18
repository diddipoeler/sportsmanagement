<?php
/**
 * Joomla 5/6 project map season ID backward-compatibility regression.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

define('_JEXEC', 1);
require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_projectmap/src/Helper/ProjectMapHelper.php';

$map = new \Diddipoeler\Module\SportsManagementProjectMap\Site\Helper\ProjectMapHelper();
$ids = new ReflectionMethod($map, 'normaliseSeasonIds');

foreach ([
    [['0007', '7', '0002', 3], [7, 2, 3]],
    ['0001,0002; 03', [1, 2, 3]],
    [[0, '000', -4, '0008', 'x', '2garbage', '2.5', true], [8]],
    [['999999999999999999999999', '0009'], [9]],
    [[[], ['1'], (object) ['id' => 1]], []],
    ['', []],
    [null, []],
    [[1, 1, '0001'], [1]],
    [[implode(',', range(1, 80))], range(1, 50)],
    [[str_repeat('1', 4200), 8], [8]],
] as [$input, $expected]) {
    $result = $ids->invoke($map, $input);

    if ($result !== $expected) {
        throw new RuntimeException(
            'Project map season IDs were normalized incorrectly for ' . var_export($input, true)
        );
    }
}

echo "Project map zero-padded seasons and input bounds passed.\n";
