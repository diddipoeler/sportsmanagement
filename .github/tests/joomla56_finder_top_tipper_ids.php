<?php
/**
 * Joomla 5/6 Finder and Top Tipper ID normalization regressions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType { public const INTEGER = 'integer'; }
}

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Service/FinderRelationNotifier.php';
    require_once $root . '/modules/mod_sportsmanagement_top_tipper/src/Helper/TopTipperHelper.php';

    $db = new class implements \Joomla\Database\DatabaseInterface {};
    $finder = new \Diddipoeler\Component\SportsManagement\Administrator\Service\FinderRelationNotifier($db);
    $normalize = new \ReflectionMethod($finder, 'normaliseIds');

    $actual = $normalize->invoke($finder, [
        '0007', 7, '8', 9, '9junk', true, 10.9, '-11', '12:slug',
        '999999999999999999999999999999999999', ['13'], (object) ['id' => 14],
        '0', null,
    ]);
    if ($actual !== [7, 8, 9]) {
        throw new \RuntimeException('Finder IDs must be valid, positive and deduplicated.');
    }

    if ($normalize->invoke($finder, '00024') !== [24]
        || $normalize->invoke($finder, [false, 0, '00', '1e3']) !== []) {
        throw new \RuntimeException('Finder scalar or zero ID normalization regressed.');
    }

    $tipper = new \Diddipoeler\Module\SportsManagementTopTipper\Site\Helper\TopTipperHelper();
    $extract = new \ReflectionMethod($tipper, 'extractId');

    foreach ([
        [42, 42],
        ['0042:joomla-alias', 42],
        [['7:team', '99:other'], 7],
        [' 005 ', 5],
        ['0', 0],
        ['12garbage', 0],
        ['12:invalid:extra', 0],
        ['-12', 0],
        ['2e3', 0],
        ['1.5', 0],
        ['999999999999999999999999999', 0],
        [true, 0],
        [8.9, 0],
        [[true, '12:alias'], 0],
        [[], 0],
    ] as [$value, $expected]) {
        $actual = $extract->invoke($tipper, $value);

        if ($actual !== $expected) {
            throw new \RuntimeException('Top Tipper accepted invalid request ID or lost a valid Joomla slug.');
        }
    }

    echo "Joomla 5/6 Finder and Top Tipper ID normalization OK.\n";
}
