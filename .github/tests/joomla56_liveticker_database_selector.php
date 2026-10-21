<?php
/**
 * Joomla 5/6 Liveticker database selector and read-only guards.
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
        public function get(string $key, mixed $default = null): mixed {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_liveticker/src/Helper/LivetickerHelper.php';

    $helper = new \Diddipoeler\Module\SportsManagementLiveticker\Site\Helper\LivetickerHelper();
    $selector = new \ReflectionMethod($helper, 'databaseSelector');

    foreach ([
        [0, 0], ['0', 0], ['000', 0], [1, 1], ['1', 1], ['0001', 1],
        [-1, null], [2, null], ['2', null], ['-1', null], ['1junk', null],
        ['1.0', null], [' 1', null], [true, null], [['1'], null],
        ['99999999999999999999999999', null],
    ] as [$raw, $expected]) {
        if ($selector->invoke(null, $raw) !== $expected) {
            throw new \RuntimeException('Invalid Liveticker DB selection was accepted.');
        }
    }

    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        public function isClient(string $name): bool { return $name === 'site'; }
    };
    $module = (object) ['id' => 77];

    // No database service is installed in this test process. Reaching the
    // resolver or container with malformed config would trigger an error.
    foreach (['1junk', '2', -1, true, ['1'], '999999999999999999'] as $invalid) {
        $data = $helper->getData(new \Joomla\Registry\Registry([
            'cfg_which_database' => $invalid,
        ]), $module, $app);

        if ($data !== [
            'listHtml' => '', 'updateTimeout' => 10,
            'cssFile' => 'simple.css', 'moduleId' => 77,
        ]) {
            throw new \RuntimeException('Invalid Liveticker database config triggered rendering.');
        }
    }

    echo "Liveticker database selector and fail-closed rendering OK\n";
}
