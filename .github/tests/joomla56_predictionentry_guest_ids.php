<?php
/**
 * Joomla 5/6 prediction entry guest and identifier regression.
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
        protected int $predictionGameId = 1;

        /**
         * Return an application whose user identity is unavailable.
         *
         * @return object Minimal site application with a guest identity.
         */
        protected function siteApplication(): object
        {
            return new class {
                /**
                 * Emulate a guest or uninitialised Joomla identity.
                 *
                 * @return null Guest identity.
                 */
                public function getIdentity(): null
                {
                    return null;
                }
            };
        }

        /**
         * Refuse administrator overrides for the anonymous test user.
         *
         * @return bool Always false for this guest.
         */
        protected function isAllowedAdmin(): bool
        {
            return false;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Model/PredictionentryModel.php';

    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\PredictionentryModel();

    if (($model->getEntryMember()->user_id ?? null) !== 0
        || $model->getSelectedMemberNumericId() !== 0
        || $model->isCurrentUserMember()
        || $model->isNotApprovedCurrentMember()
        || $model->canActAsEntryMember((object) [
            'pmID' => 15, 'user_id' => 9, 'approved' => 1,
        ])) {
        throw new \RuntimeException('Guest identity received prediction member access.');
    }

    $ids = new \ReflectionMethod($model, 'normaliseIdList');
    $parsed = $ids->invoke($model, ['7:round', '007', '12', '13junk', '1e2',
        '999999999999999999999999', -2, true, ['5'], ' 14 ']);

    if ($parsed !== [7, 12, 14]) {
        throw new \RuntimeException('Prediction entry IDs accepted malformed data.');
    }

    $memberId = new \ReflectionMethod($model, 'extractMemberId');
    foreach (['12garbage', '12:bad:slug', '-1', '2e4', ['12'], true,
        '999999999999999999999999'] as $invalid) {
        if ($memberId->invoke($model, $invalid) !== 0) {
            throw new \RuntimeException('Invalid prediction member ID accepted.');
        }
    }

    if ($memberId->invoke($model, '0012:member-alias') !== 12) {
        throw new \RuntimeException('Valid legacy Joomla member slug was rejected.');
    }

    echo "Prediction entry guest permission and ID boundaries OK\n";
}
