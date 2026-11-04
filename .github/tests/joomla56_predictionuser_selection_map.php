<?php
/**
 * Regression test for Joomla 5/6 prediction member selection parsing.
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
    require_once dirname(__DIR__, 2) . '/site/src/Model/PredictionuserModel.php';

    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\PredictionuserModel();
    $selection = $model->selectionMap(
        '12,34; 12,45; 8,3; 17junk,9; 9,2tail; 1e2,99; '
        . '99999999999999999999999999,3; 4; -2,1; 3,-1; 10,0'
    );

    if ($selection !== [12 => 45, 8 => 3]) {
        throw new \RuntimeException('Prediction single-team selection accepted malformed IDs.');
    }

    $multi = $model->selectionMap('12,34;12,45;8,3;7,not-an-id;2,99999999999999999999999999', true);

    if ($multi !== [12 => [34, 45], 8 => [3]]) {
        throw new \RuntimeException('Prediction multi-team selections must keep only valid pairs.');
    }

    echo "Prediction member selection parser OK\n";
}
