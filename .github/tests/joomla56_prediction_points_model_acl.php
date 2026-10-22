<?php
/**
 * Joomla 5/6 prediction scoring model must guard direct write calls.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class PredictionresultsModel {
        public bool $admin = false;
        public int $readCalls = 0;
        public function isAllowedAdmin(): bool { return $this->admin; }
        public function getResultsProject(): ?object {
            ++$this->readCalls;
            return null;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Model/PredictionpointsModel.php';

    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\PredictionpointsModel();

    try {
        $model->recalculatePoints([]);
        throw new \RuntimeException('Unauthorized scoring write was accepted.');
    } catch (\RuntimeException $e) {
        if ($e->getCode() !== 403) { throw $e; }
    }

    if ($model->readCalls !== 0) {
        throw new \RuntimeException('Unauthorized scoring accessed project data.');
    }

    $model->admin = true;
    if ($model->recalculatePoints([]) !== 0 || $model->readCalls !== 1) {
        throw new \RuntimeException('Authorized empty scoring operation failed.');
    }

    echo "Prediction scoring model authorization OK\n";
}
