<?php
/**
 * Regression for Joomla 5/6 prediction profile guests and saved teams.
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
        protected int $predictionGameId = 5;

        /**
         * Return a frontend context with no user identity.
         *
         * @return object Anonymous Joomla frontend app.
         */
        protected function siteApplication(): object
        {
            return new class {
                /**
                 * Represent an uninitialised Joomla identity.
                 *
                 * @return null No logged-in member.
                 */
                public function getIdentity(): null { return null; }
            };
        }

        /**
         * Forbid administrator access for guest regression tests.
         *
         * @return bool False for guest visitors.
         */
        protected function isAllowedAdmin(): bool { return false; }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Model/PredictionusersModel.php';

    $type = \Diddipoeler\Component\SportsManagement\Site\Model\PredictionusersModel::class;
    $model = (new \ReflectionClass($type))->newInstanceWithoutConstructor();

    $private = (object) ['user_id' => 18, 'show_profile' => 0];
    $public = (object) ['user_id' => 18, 'show_profile' => 1];
    if ($model->isPredictionMember()
        || $model->canViewMemberProfile($private)
        || !$model->canViewMemberProfile($public)) {
        throw new \RuntimeException('Guest visibility or membership was incorrectly granted.');
    }

    $ids = new \ReflectionMethod($model, 'extractMemberId');
    foreach (['12junk', '12:bad:slug', '0', '-1', '1e2',
        '99999999999999999999999999', [], true] as $invalid) {
        if ($ids->invoke($model, $invalid) !== 0) {
            throw new \RuntimeException('Malformed prediction member ID accepted.');
        }
    }
    if ($ids->invoke($model, '0012:member') !== 12) {
        throw new \RuntimeException('Valid prediction member slug rejected.');
    }

    $selections = new \ReflectionMethod($model, 'parseMemberTeamSelections');
    $single = $selections->invoke($model, '12junk,55;12,42;9,3junk;12,43', false);
    $multi = $selections->invoke($model, '12,42;12,43;3,999999999999999999999999;8,15', true);

    if ($single !== [12 => 43]
        || $multi !== [12 => [42, 43], 8 => [15]]) {
        throw new \RuntimeException('Invalid serialized member team IDs were accepted.');
    }

    echo "Prediction users guest ACL and team IDs OK\n";
}
