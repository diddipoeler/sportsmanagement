<?php
/**
 * Regression for optional club-plan data and strict identifier validation.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementProjectModel
    {
        /**
         * Simulate an unavailable connection for optional presentation data.
         * @return object Never returned while the database is unavailable.
         * @throws \RuntimeException Simulated connection failure.
         */
        protected function getDatabase(): object
        {
            ++$GLOBALS['databaseCalls'];
            throw new \RuntimeException('Database unavailable');
        }
    }
}
namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/site/src/Model/ClubplanViewDataModel.php';
    $GLOBALS['databaseCalls'] = 0;
    $model = (new \ReflectionClass(\Diddipoeler\Component\SportsManagement\Site\Model\ClubplanViewDataModel::class))->newInstanceWithoutConstructor();
    $normalise = new \ReflectionMethod($model, 'normaliseIds');
    if ($normalise->invoke($model, [7, '0007', '8', true, [], '3junk', '999999999999999999999999']) !== [7, 8]) {
        throw new \RuntimeException('Club-plan identifiers were not normalized.');
    }
    foreach (['getFavoriteSettings', 'getMatchReferees'] as $method) {
        if ($model->$method([true, ['2'], '7junk']) !== []) {
            throw new \RuntimeException('Invalid club-plan selection returned data.');
        }
    }
    if ($GLOBALS['databaseCalls'] !== 0) {
        throw new \RuntimeException('Invalid identifiers reached the database.');
    }
    if ($model->getFavoriteSettings([1]) !== [] || $model->getMatchReferees([1]) !== []
        || $GLOBALS['databaseCalls'] !== 2) {
        throw new \RuntimeException('Optional database failures interrupted the club plan.');
    }
    echo "Club-plan optional data guards OK\n";
}
