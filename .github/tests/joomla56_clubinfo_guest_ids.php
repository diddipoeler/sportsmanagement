<?php
/**
 * Regression tests for Joomla 5/6 club selection and guest edit permissions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementProjectModel
    {
        /**
         * Return a test application without a logged-in Joomla identity.
         *
         * @return object Guest site application.
         */
        protected function siteApplication(): object
        {
            return new class {
                /**
                 * Simulate a guest without a usable user identity.
                 *
                 * @return null No active user.
                 */
                public function getIdentity(): null { return null; }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Model/ClubinfoModel.php';

    $type = \Diddipoeler\Component\SportsManagement\Site\Model\ClubinfoModel::class;
    $model = (new \ReflectionClass($type))->newInstanceWithoutConstructor();

    if ($model->hasEditPermission() || $model->hasEditPermission('core.edit')) {
        throw new \RuntimeException('Guest club editor was not rejected.');
    }

    $method = new \ReflectionMethod($model, 'normaliseIds');
    $ids = $method->invoke(null, ['007:club', '7', 13, '14junk',
        '1e2', '9999999999999999999999999', [], false, ' 15 ']);

    if ($ids !== [7, 13, 15]
        || $method->invoke(null, '12:club|13,14') !== [12, 13, 14]) {
        throw new \RuntimeException('Club ID parsing permitted malformed selectors.');
    }

    echo "Clubinfo guest ACL and selector boundaries OK\n";
}
