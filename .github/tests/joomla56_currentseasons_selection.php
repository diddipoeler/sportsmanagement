<?php
/**
 * Regression for administrator current-season selection.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementListModel {}
}
namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/admin/src/Model/CurrentseasonsModel.php';
    $method = new \ReflectionMethod(\Diddipoeler\Component\SportsManagement\Administrator\Model\CurrentseasonsModel::class, 'normaliseSeasonIds');
    foreach ([
        [['0007', 7, '2', '3junk', true, ['4']], [7, 2]],
        ['0001,2; 3', [1, 2, 3]],
        [range(1, 250), range(1, 200)],
        [str_repeat('1,', 2100), []],
        [[0, -1, '9999999999999999999999999', false, ''], []],
        [null, []],
    ] as [$raw, $expected]) {
        if ($method->invoke(null, $raw) !== $expected) {
            throw new \RuntimeException('Current-season configuration was not safely normalized.');
        }
    }
    $source = file_get_contents(dirname(__DIR__, 2) . '/admin/src/Model/CurrentseasonsModel.php');
    if (!str_contains($source, "->where('1 = 0')")) {
        throw new \RuntimeException('An empty season selection must not list every project.');
    }
    echo "Current-season configuration guards OK\n";
}
