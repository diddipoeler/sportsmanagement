<?php
/**
 * Joomla 5/6 prediction ranking selector validation regressions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementPredictionReadModel {}
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Model/PredictionrankingModel.php';

    $type = \Diddipoeler\Component\SportsManagement\Site\Model\PredictionrankingModel::class;
    $instance = (new \ReflectionClass($type))->newInstanceWithoutConstructor();

    $ids = new \ReflectionMethod($instance, 'extractId');
    foreach (['', '12junk', '12:bad:slug', '-1', '1e2', '0',
        '999999999999999999999999999999', '12;drop', '123,9'] as $invalid) {
        if ($ids->invoke($instance, $invalid) !== 0) {
            throw new \RuntimeException('Prediction ranking accepted an invalid URL selector: ' . $invalid);
        }
    }

    if ($ids->invoke($instance, '0012:project') !== 12 || $ids->invoke($instance, '35') !== 35) {
        throw new \RuntimeException('Valid numeric and ID:alias ranking selectors failed.');
    }

    $tip = new \ReflectionMethod($instance, 'extractTipTeamId');
    if ($tip->invoke($instance, '12junk,88;12,42', 12) !== 42
        || $tip->invoke($instance, '12,42garbage', 12) !== 0
        || $tip->invoke($instance, '12,9999999999999999999999999', 12) !== 0
        || $tip->invoke($instance, '12,42;13,99', 13) !== 99
        || $tip->invoke($instance, '12', 12) !== 0) {
        throw new \RuntimeException('Ranking team-tip selector converted malformed IDs.');
    }

    echo "Prediction ranking ID/serialized team filters OK\n";
}
