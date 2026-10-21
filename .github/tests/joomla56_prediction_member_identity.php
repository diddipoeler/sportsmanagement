<?php
/**
 * Verify Joomla 5/6 prediction profile ACL handling with absent identities.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementPredictionReadModel
    {
        protected int $predictionGameId = 7;
        public static ?object $identity = null;
        public static bool $adminAllowed = false;

        protected function siteApplication(): object
        {
            return new class {
                public function getIdentity(): ?object
                {
                    return SportsManagementPredictionReadModel::$identity;
                }
            };
        }

        public function isAllowedAdmin(): bool
        {
            return self::$adminAllowed;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Model/PredictionuserModel.php';

    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\PredictionuserModel();
    $base = \Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementPredictionReadModel::class;
    $member = (object) ['user_id' => 42];

    $base::$identity = null;
    $base::$adminAllowed = true;
    if ($model->canEditMember($member)) {
        throw new \RuntimeException('Anonymous identity gained access to prediction profile.');
    }

    $base::$identity = (object) ['id' => 42];
    $base::$adminAllowed = false;
    if (!$model->canEditMember($member)) {
        throw new \RuntimeException('Profile owner cannot edit their own prediction profile.');
    }

    $base::$identity = (object) ['id' => 43];
    if ($model->canEditMember($member)) {
        throw new \RuntimeException('Unrelated member gained edit access.');
    }

    $base::$adminAllowed = true;
    if (!$model->canEditMember($member)) {
        throw new \RuntimeException('Authorised prediction administrator lost edit access.');
    }

    $parseId = new \ReflectionMethod($model, 'memberId');
    foreach ([
        [12, 12], ['12', 12], ['0012:my-team', 12],
        [null, 0], [true, 0], [12.5, 0], [['12'], 0],
        ['12junk', 0], ['12:bad:extra', 0],
        ['-12', 0], ['0', 0], ['12,13', 0],
        ['999999999999999999999999999', 0],
    ] as [$raw, $expected]) {
        if ($parseId->invoke($model, $raw) !== $expected) {
            throw new \RuntimeException('Malformed prediction member ID accepted: ' . var_export($raw, true));
        }
    }

    echo "Prediction member identity ACL regressions passed.\n";
}
