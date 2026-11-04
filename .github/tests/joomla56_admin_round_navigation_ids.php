<?php
/**
 * Joomla 5/6 administrator round navigation selector safety.
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
    require_once dirname(__DIR__, 2) . '/admin/src/Model/RoundsModel.php';

    $method = new \ReflectionMethod(
        \Diddipoeler\Component\SportsManagement\Administrator\Model\RoundsModel::class,
        'findRoundIndex'
    );

    $rows = [
        ['id' => '11:alpha', 'round_id' => 11],
        ['id' => '12:beta', 'round_id' => 12],
    ];

    foreach (['11junk', '1e1', '11:bad:slug', '11,12',
        '99999999999999999999999', '0', -1, true, []] as $invalid) {
        if ($method->invoke(null, $rows, $invalid) !== null) {
            throw new \RuntimeException('Malformed round navigation selector was accepted.');
        }
    }

    if ($method->invoke(null, $rows, '11:alpha') !== 0
        || $method->invoke(null, $rows, '0012') !== 1
        || $method->invoke(null, $rows, 12) !== 1
        || $method->invoke(null, $rows, '13') !== null) {
        throw new \RuntimeException('Valid Joomla round selectors failed to resolve.');
    }

    echo "Admin round navigation identifiers OK\n";
}
