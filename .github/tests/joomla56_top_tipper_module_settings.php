<?php
/**
 * Top Tipper module legacy numeric settings and safety bounds.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}

namespace Joomla\Registry {
    final class Registry {
        public function __construct(private array $values = []) {}
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_top_tipper/src/Helper/TopTipperHelper.php';

    $helper = new \Diddipoeler\Module\SportsManagementTopTipper\Site\Helper\TopTipperHelper();
    $selector = new \ReflectionMethod($helper, 'databaseSelector');
    $bounded = new \ReflectionMethod($helper, 'boundedLimit');

    foreach ([
        [0, 0], ['000', 0], [1, 1], ['0001', 1],
        [14, 14], ['0014', 14], [-1, null], ['-1', null],
        ['14junk', null], [true, null], [['14'], null],
        ['14.5', null], ['9999999999999999999999999999', null],
    ] as [$raw, $expected]) {
        if ($selector->invoke(null, $raw) !== $expected) {
            throw new \RuntimeException('Malformed Top Tipper database selector accepted.');
        }
    }

    foreach ([
        [5, 5], ['0005', 5], ['0', 1], ['000', 1],
        [100, 100], [9999, 100], ['9999', 100],
        [-3, 5], ['3garbage', 5], [true, 5],
        [['7'], 5], ['999999999999999999999999999', 5],
    ] as [$raw, $expected]) {
        if ($bounded->invoke(null, $raw) !== $expected) {
            throw new \RuntimeException('Top Tipper list limit was unbounded or invalid.');
        }
    }

    $moduleConfig = new \ReflectionMethod($helper, 'moduleConfig');
    $settings = $moduleConfig->invoke($helper, new \Joomla\Registry\Registry(['limit' => 9999]));

    if (($settings['limit'] ?? null) !== 100) {
        throw new \RuntimeException('Top Tipper configuration did not bound ranking output.');
    }

    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {};
    foreach ([-1, '2junk', ['1'], '99999999999999999999999999'] as $bad) {
        $data = $helper->getData(
            new \Joomla\Registry\Registry(['pg' => 7, 'cfg_which_database' => $bad]),
            (object) ['id' => 1],
            $app
        );
        if ($data['predictionGame'] !== null || $data['rankingRows'] !== []) {
            throw new \RuntimeException('Invalid Top Tipper database selector reached model loading.');
        }
    }

    echo "Top Tipper database selector and bounded result limit OK\n";
}
