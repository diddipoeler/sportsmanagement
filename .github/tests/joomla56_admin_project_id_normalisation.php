<?php
/**
 * Regression for strict administrator project-season ID validation.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementListModel
    {
        /**
         * Fail when invalid project selectors reach a database lookup.
         *
         * @return never This path is forbidden for invalid identifiers.
         */
        protected function getDatabase(): never
        {
            throw new \RuntimeException('Invalid project selection reached SQL.');
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Model/ProjectsModel.php';

    $type = \Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectsModel::class;
    $model = (new \ReflectionClass($type))->newInstanceWithoutConstructor();
    $ids = (new \ReflectionMethod($model, 'normaliseIds'))->invoke($model, [
        '007', '7', 12, ' 23 ', '8junk', '-2', '1e2', true,
        3.5, [], '999999999999999999999999999999999999', '0', 0,
    ]);

    if ($ids !== [7, 12, 23]) {
        throw new \RuntimeException('Projects model accepted malformed season IDs.');
    }

    foreach ([
        [['7', '8bad'], '4garbage'],
        [['23invalid'], '4'],
        [['0'], '4'],
        [['12'], '999999999999999999999999999'],
    ] as [$seasons, $leagueId]) {
        if ($model->existcurrentseason($seasons, $leagueId) !== null) {
            throw new \RuntimeException('Invalid season/league selection resolved a project.');
        }
    }

    echo "Administrator project season/league ID validation OK\n";
}
