<?php
/**
 * Joomla 5/6 Liveticker stylesheet filename configuration regression.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_liveticker/src/Helper/LivetickerHelper.php';

    $helper = new \Diddipoeler\Module\SportsManagementLiveticker\Site\Helper\LivetickerHelper();
    $method = new \ReflectionMethod($helper, 'cssFile');

    foreach ([
        ['simple.css', 'simple.css'],
        ['my-style_2.css', 'my-style_2.css'],
        ['../bad.css', 'simple.css'],
        ['path/file.css', 'simple.css'],
        ['javascript:alert(1)', 'simple.css'],
        ["broken\n.css", 'simple.css'],
        [['simple.css'], 'simple.css'],
        [true, 'simple.css'],
        ['', 'simple.css'],
        ['../../secret.php', 'simple.css'],
    ] as [$value, $expected]) {
        if ($method->invoke(null, $value) !== $expected) {
            throw new \RuntimeException('Unsafe Liveticker CSS filename accepted.');
        }
    }

    echo "Liveticker CSS filename configuration OK\n";
}
