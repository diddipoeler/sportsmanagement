<?php
/**
 * Runtime regressions for Joomla 5/6 Liveticker AJAX IDs and Inline Hockey URLs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Helper {
    /**
     * Simulate Joomla's strict string comparison of module identifiers.
     */
    final class ModuleHelper
    {
        public static function getModuleById($id): object
        {
            if ($id === '42') {
                return (object) [
                    'id' => 42,
                    'module' => 'mod_sportsmanagement_liveticker',
                    'params' => '{}',
                ];
            }

            return (object) ['id' => 0, 'module' => ''];
        }
    }
}

namespace {
    \define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_liveticker/src/Helper/LivetickerHelper.php';
    require_once dirname(__DIR__, 2)
        . '/site/src/Service/InlineHockeyApiClient.php';

    function assertMigrationRegression(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    $helper = new \Diddipoeler\Module\SportsManagementLiveticker\Site\Helper\LivetickerHelper();
    $requestedModule = new \ReflectionMethod($helper, 'requestedModule');
    $module = $requestedModule->invoke($helper, 42);

    assertMigrationRegression(
        \is_object($module) && (int) ($module->id ?? 0) === 42,
        'Liveticker AJAX must pass a string ID to Joomla ModuleHelper::getModuleById().'
    );
    assertMigrationRegression(
        $requestedModule->invoke($helper, 0) === null
            && $requestedModule->invoke($helper, 43) === null,
        'Liveticker must reject missing and invalid module IDs.'
    );

    $client = new \Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyApiClient();
    $examples = [
        [
            'https://www.ishd.de/api/games#details',
            3,
            'https://www.ishd.de/api/games?page=3#details',
        ],
        [
            'https://www.ishd.de/api/games?season=2#details',
            4,
            'https://www.ishd.de/api/games?season=2&page=4#details',
        ],
        [
            'https://www.ishd.de/api/games?page=2&season=2#details',
            5,
            'https://www.ishd.de/api/games?page=5&season=2#details',
        ],
        [
            'https://www.ishd.de/api/games?PAGE=invalid&season=2#details',
            6,
            'https://www.ishd.de/api/games?page=6&season=2#details',
        ],
        [
            'https://www.ishd.de/api/games#details',
            0,
            'https://www.ishd.de/api/games?page=1#details',
        ],
    ];

    foreach ($examples as [$url, $page, $expected]) {
        assertMigrationRegression(
            $client->pageUrl($url, $page) === $expected,
            'Inline Hockey page URL incorrectly handled query string or fragment: ' . $url
        );
    }

    echo "Joomla 5/6 Liveticker and Inline Hockey regressions: OK\n";
}
