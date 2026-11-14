<?php
/**
 * Joomla 5/6 repeated WebAssetManager registration across four modules.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Dispatcher { class AbstractModuleDispatcher {} }
namespace Joomla\CMS\Helper {
    interface HelperFactoryAwareInterface {}
    trait HelperFactoryAwareTrait {}
}
namespace SportsManagement\Assets {
    final class Manager
    {
        public array $registered = [];
        public array $uses = [];

        /**
         * Check whether an asset was registered by an earlier module instance.
         *
         * @param string $type Joomla asset kind: script or style.
         * @param string $name Unique asset identifier.
         * @return bool Whether the document owns the named asset.
         */
        public function assetExists(string $type, string $name): bool
        {
            return isset($this->registered[$type . ':' . $name]);
        }

        /**
         * Capture one stylesheet and reject duplicate registration.
         *
         * @param string $name Joomla stylesheet identifier.
         * @param string $url Relative CSS path.
         * @param array $options Cache-version options.
         * @return void
         */
        public function registerStyle(string $name, string $url, array $options = []): void
        {
            if ($this->assetExists('style', $name)) {
                throw new \RuntimeException('Duplicate CSS: ' . $name);
            }
            $this->registered['style:' . $name] = [$url, $options];
        }

        /**
         * Record stylesheet activation per rendered module.
         *
         * @param string $name Existing stylesheet identifier.
         * @return void
         */
        public function useStyle(string $name): void
        {
            if (!$this->assetExists('style', $name)) {
                throw new \RuntimeException('Missing stylesheet: ' . $name);
            }
            $this->uses[] = 'style:' . $name;
        }

        /**
         * Capture a script with its original attributes and dependencies.
         *
         * @param string $name Joomla script identifier.
         * @param string $url Relative JavaScript path.
         * @param array $options Asset URL options.
         * @param array $attributes Deferred script HTML attributes.
         * @param array $dependencies Required core scripts.
         * @return void
         */
        public function registerScript(
            string $name,
            string $url,
            array $options = [],
            array $attributes = [],
            array $dependencies = []
        ): void {
            if ($this->assetExists('script', $name)) {
                throw new \RuntimeException('Duplicate JS: ' . $name);
            }
            $this->registered['script:' . $name] = [$url, $options, $attributes, $dependencies];
        }

        /**
         * Record activation of module JavaScript and Joomla Bootstrap tabs.
         *
         * @param string $name Existing script identifier.
         * @return void
         */
        public function useScript(string $name): void
        {
            if ($name !== 'bootstrap.tab' && !$this->assetExists('script', $name)) {
                throw new \RuntimeException('Missing script: ' . $name);
            }
            $this->uses[] = 'script:' . $name;
        }
    }
}
namespace Joomla\CMS\Document {
    final class HtmlDocument
    {
        /**
         * Assign a single shared asset manager to the current HTML page.
         *
         * @param \SportsManagement\Assets\Manager $assets Page-scoped registry.
         */
        public function __construct(private \SportsManagement\Assets\Manager $assets) {}

        /**
         * Expose the Joomla-like WebAssetManager to all four modules.
         *
         * @return \SportsManagement\Assets\Manager Shared asset registry.
         */
        public function getWebAssetManager(): \SportsManagement\Assets\Manager
        {
            return $this->assets;
        }
    }
}
namespace {
    \define('_JEXEC', 1);
    $base = \dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_';
    require $base . 'ajax_top_navigation_menu/src/Dispatcher/Dispatcher.php';
    require $base . 'navigation_menu/src/Dispatcher/Dispatcher.php';
    require $base . 'teamplayers/src/Dispatcher/Dispatcher.php';
    require $base . 'playgroundplan/src/Dispatcher/Dispatcher.php';

    $ajax = new \Diddipoeler\Module\SportsManagementAjaxTopNavigationMenu\Site\Dispatcher\Dispatcher();
    $nav = new \Diddipoeler\Module\SportsManagementNavigationMenu\Site\Dispatcher\Dispatcher();
    $team = new \Diddipoeler\Module\SportsManagementTeamPlayers\Site\Dispatcher\Dispatcher();
    $plan = new \Diddipoeler\Module\SportsManagementPlaygroundPlan\Site\Dispatcher\Dispatcher();
    $manager = new \SportsManagement\Assets\Manager();
    $doc = new \Joomla\CMS\Document\HtmlDocument($manager);
    $a = new \ReflectionMethod($ajax, 'useNavigationAssets');
    $n = new \ReflectionMethod($nav, 'useMenuAssets');
    $t = new \ReflectionMethod($team, 'useRosterAssets');
    $p = new \ReflectionMethod($plan, 'usePlanAssets');

    // List-only and static plan instances must not install optional JS.
    $t->invoke($team, $doc, false);
    $p->invoke($plan, $doc, false);
    if ($manager->assetExists('script', 'mod_sportsmanagement_teamplayers.native')
        || $manager->assetExists('script', 'mod_sportsmanagement_playgroundplan.ticker')) {
        throw new \RuntimeException('Optional scripts enabled in non-carousel/ticker modes.');
    }

    $a->invoke($ajax, $doc);
    $n->invoke($nav, $doc);
    $t->invoke($team, $doc, true);
    $p->invoke($plan, $doc, true);
    $a->invoke($ajax, $doc);
    $n->invoke($nav, $doc);
    $t->invoke($team, $doc, true);
    $p->invoke($plan, $doc, false);

    $expected = [
        'style:mod_sportsmanagement_teamplayers.native' => ['modules/mod_sportsmanagement_teamplayers/css/native.css', ['version' => 'auto']],
        'style:mod_sportsmanagement_playgroundplan' => ['modules/mod_sportsmanagement_playgroundplan/css/mod_sportsmanagement_playgroundplan.css', []],
        'style:mod_sportsmanagement_ajax_top_navigation_menu' => ['modules/mod_sportsmanagement_ajax_top_navigation_menu/css/mod_sportsmanagement_ajax_top_navigation_menu.css', ['version' => 'auto']],
        'style:mod_sportsmanagement_ajax_top_navigation_menu.tabs' => ['modules/mod_sportsmanagement_ajax_top_navigation_menu/css/mod_sportsmanagement_ajax_top_navigation_tabs_sliders.css', ['version' => 'auto']],
        'script:mod_sportsmanagement_ajax_top_navigation_menu.native' => ['modules/mod_sportsmanagement_ajax_top_navigation_menu/js/native.js', ['version' => 'auto'], ['defer' => true], ['core']],
        'style:mod_sportsmanagement_navigation_menu' => ['modules/mod_sportsmanagement_navigation_menu/css/mod_sportsmanagement_navigation_menu.css', ['version' => 'auto']],
        'script:mod_sportsmanagement_navigation_menu' => ['modules/mod_sportsmanagement_navigation_menu/js/mod_sportsmanagement_navigation_menu.js', ['version' => 'auto'], ['defer' => true], []],
        'script:mod_sportsmanagement_teamplayers.native' => ['modules/mod_sportsmanagement_teamplayers/js/native.js', ['version' => 'auto'], ['defer' => true], []],
        'script:mod_sportsmanagement_playgroundplan.ticker' => ['modules/mod_sportsmanagement_playgroundplan/js/ticker.js', [], [], []],
    ];
    if ($manager->registered !== $expected) {
        throw new \RuntimeException('Asset URL/attribute/dependency or reuse regression.');
    }

    $useCounts = array_count_values($manager->uses);
    foreach ([
        'script:bootstrap.tab' => 2,
        'style:mod_sportsmanagement_ajax_top_navigation_menu.tabs' => 2,
        'script:mod_sportsmanagement_ajax_top_navigation_menu.native' => 2,
        'style:mod_sportsmanagement_navigation_menu' => 2,
        'script:mod_sportsmanagement_navigation_menu' => 2,
        'style:mod_sportsmanagement_teamplayers.native' => 3,
        'script:mod_sportsmanagement_teamplayers.native' => 2,
        'style:mod_sportsmanagement_playgroundplan' => 3,
        'script:mod_sportsmanagement_playgroundplan.ticker' => 1,
    ] as $key => $count) {
        if (($useCounts[$key] ?? 0) !== $count) {
            throw new \RuntimeException('Unexpected asset activation frequency: ' . $key);
        }
    }

    $newManager = new \SportsManagement\Assets\Manager();
    $newPage = new \Joomla\CMS\Document\HtmlDocument($newManager);
    $a->invoke($ajax, $newPage);
    $n->invoke($nav, $newPage);
    $t->invoke($team, $newPage, false);
    $p->invoke($plan, $newPage, false);

    if (count($newManager->registered) !== 7
        || $newManager->assetExists('script', 'mod_sportsmanagement_teamplayers.native')
        || $newManager->assetExists('script', 'mod_sportsmanagement_playgroundplan.ticker')) {
        throw new \RuntimeException('New page reused previous document or optional scripts.');
    }

    echo "Joomla 5/6 navigation, roster and playground asset reuse OK.\n";
}
