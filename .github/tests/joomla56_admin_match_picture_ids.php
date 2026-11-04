<?php
/**
 * Ensure the administrator match media folder cannot use malformed IDs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementListModel {}
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Model/MatchesModel.php';

    $type = \Diddipoeler\Component\SportsManagement\Administrator\Model\MatchesModel::class;
    $model = (new \ReflectionClass($type))->newInstanceWithoutConstructor();
    $parser = new \ReflectionMethod($model, 'strictMatchId');

    foreach (['12junk', '12:bad:slug', '-1', '1e2', '0',
        '999999999999999999999999999', true, [], 2.5] as $invalid) {
        if ($parser->invoke($model, $invalid) !== 0
            || $model->checkMatchPicturePath($invalid) !== false) {
            throw new \RuntimeException('Invalid match ID was allowed to reach picture path creation.');
        }
    }

    if ($parser->invoke($model, '0012:home-away') !== 12
        || $parser->invoke($model, 13) !== 13) {
        throw new \RuntimeException('Valid match identifiers should keep their numeric identity.');
    }

    echo "Administrator match picture ID safety OK\n";
}
