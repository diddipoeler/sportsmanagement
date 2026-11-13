<?php
/**
 * Validate Joomla 5/6 round list quick-save date normalisation.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    abstract class SportsManagementAdminModel {}
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Helper/SportsManagementDateHelper.php';
    require_once $root . '/admin/src/Model/RoundModel.php';

    $method = new \ReflectionMethod(
        \Diddipoeler\Component\SportsManagement\Administrator\Model\RoundModel::class,
        'normaliseShortRoundDate'
    );

    foreach ([
        ['13-11-2026', '2026-11-13'],
        ['2026-11-13', '2026-11-13'],
        ['13.11.2026', '2026-11-13'],
        ['13/11/2026', '2026-11-13'],
        ['', null],
        ['0000-00-00', null],
        ['00-00-0000', null],
        ['31-02-2026', false],
        ['2026-02-31', false],
        ['2026-13-01', false],
        ['not a date', false],
        ['2026-11-13 20:00:00', false],
    ] as [$input, $expected]) {
        $actual = $method->invoke(null, $input);

        if ($actual !== $expected) {
            throw new \RuntimeException(
                'Round short date conversion failed for ' . var_export($input, true)
                . ': got ' . var_export($actual, true)
            );
        }
    }

    $numberMethod = new \ReflectionMethod(
        \Diddipoeler\Component\SportsManagement\Administrator\Model\RoundModel::class,
        'normaliseShortRoundNumber'
    );

    foreach ([
        [0, 2147483647, 0],
        ['7', 2147483647, 7],
        [2147483647, 2147483647, 2147483647],
        ['1', 1, 1],
        ['0', 1, 0],
        ['2', 1, false],
        [-1, 2147483647, false],
        ['-1', 2147483647, false],
        ['1.5', 2147483647, false],
        ['1e2', 2147483647, false],
        ['7junk', 2147483647, false],
        ['99999999999999999999999', 2147483647, false],
        [true, 1, false],
        [false, 1, false],
        [[], 1, false],
    ] as [$value, $maximum, $expected]) {
        $actual = $numberMethod->invoke(null, $value, $maximum);
        if ($actual !== $expected) {
            throw new \RuntimeException('Round quick-save number validation failed.');
        }
    }

    $modelSource = file_get_contents($root . '/admin/src/Model/RoundModel.php');
    $viewSource = file_get_contents($root . '/admin/src/View/Round/HtmlView.php');

    if (!is_string($modelSource) || !is_string($viewSource)
        || !str_contains($modelSource, '$record->round_date_first = $firstDate;')
        || !str_contains($modelSource, 'foreach ($records as $record)')
        || !str_contains($modelSource, 'normaliseShortRoundNumber(')
        || !str_contains($modelSource, '$record->round_date_last = $firstDate === null ? null : ($lastDate ?? $firstDate);')
        || str_contains($modelSource, "\$record->round_date_first = '0000-00-00'")
        || str_contains($viewSource, "\$this->item->round_date_first = '0000-00-00'")) {
        throw new \RuntimeException('Round list/editor still contains a legacy zero-date write.');
    }

    echo "Round quick-save nullable date validation OK\n";
}
