<?php
/**
 * Verify Top Tipper module switches reject malformed values on PHP 8.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Registry {
    class Registry
    {
        public function __construct(private array $values = [])
        {
        }

        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_top_tipper/src/Helper/TopTipperHelper.php';

    $class = \Diddipoeler\Module\SportsManagementTopTipper\Site\Helper\TopTipperHelper::class;
    $helper = (new ReflectionClass($class))->newInstanceWithoutConstructor();
    $moduleConfig = new ReflectionMethod($class, 'moduleConfig');
    $params = new \Joomla\Registry\Registry([
        'show_project_name' => 1,
        'show_rankingnav' => '1',
        'show_user_icon' => ['1'],
        'show_tip_details' => '1garbage',
        'show_count_tips' => true,
        'show_count_joker' => 9,
        'limit' => '0007',
    ]);
    $config = $moduleConfig->invoke($helper, $params);

    $expected = [
        'show_project_name' => 1,
        'show_rankingnav' => 1,
        'show_user_icon' => 0,
        'show_tip_details' => 0,
        'show_count_tips' => 0,
        'show_count_joker' => 0,
        'limit' => 7,
    ];

    foreach ($expected as $key => $value) {
        if (($config[$key] ?? null) !== $value) {
            fwrite(STDERR, "Top Tipper flag validation failed: {$key}" . PHP_EOL);
            exit(1);
        }
    }

    echo "Top Tipper flag checks passed.\n";
}
