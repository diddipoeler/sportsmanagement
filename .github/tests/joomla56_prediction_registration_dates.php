<?php
/**
 * Joomla 5/6 prediction member registration date validation.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class PredictionuserModel {}
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Model/PredictionmemberModel.php';

    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\PredictionmemberModel();
    $parse = new \ReflectionMethod($model, 'normaliseRegisterDate');
    $fallback = '2020-01-01 00:00:00';

    foreach ([
        [['registerDate' => '2024-02-29', 'registerTime' => '7:05'], '2024-02-29 07:05:00'],
        [['registerDate' => '2024-02-29', 'registerTime' => '23:59:59'], '2024-02-29 23:59:59'],
        [['registerDate' => '2024-02-29'], '2024-02-29 00:00:00'],
        [['registerDate' => '2025-02-29'], $fallback],
        [['registerDate' => '2026-02-31'], $fallback],
        [['registerDate' => '2026-13-01'], $fallback],
        [['registerDate' => '2026-10-21', 'registerTime' => '24:00'], $fallback],
        [['registerDate' => '2026-10-21', 'registerTime' => '12:61'], $fallback],
        [['registerDate' => '2026-10-21', 'registerTime' => '12:30:60'], $fallback],
        [['registerDate' => '2026-10-21 extra', 'registerTime' => '12:00'], $fallback],
        [['registerDate' => ['2026-10-21'], 'registerTime' => '12:00'], $fallback],
        [[], $fallback],
    ] as [$post, $expected]) {
        $actual = $parse->invoke($model, $post, $fallback);

        if ($actual !== $expected) {
            throw new \RuntimeException('Invalid prediction registration date: '
                . json_encode($post) . ' -> ' . $actual);
        }
    }

    echo "Prediction member registration date validation OK\n";
}
