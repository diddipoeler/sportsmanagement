<?php
/**
 * Joomla 5/6 regression for Events Ranking and Project Map WebAssetManager.
 *
 * Tests two event-ranking modules and two project maps using one HTML document.
 * Duplicate registrations must be avoided, and Project Map must preserve its
 * Joomla-core -> initializer -> worldmap dependency chain.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Dispatcher {
    class AbstractModuleDispatcher {}
}

namespace Joomla\CMS\Helper {
    interface HelperFactoryAwareInterface {}
    trait HelperFactoryAwareTrait {}
}

namespace SportsManagement\AssetTests {
    final class FakeAssets
    {
        /** @var array<string,array> Registered asset arguments by kind/name. */
        public array $registrations = [];

        /** @var array<int,string> The sequence of asset activations. */
        public array $uses = [];

        /**
         * Return the registration state shared by both frontend modules.
         *
         * @param string $kind Joomla asset kind, style or script.
         * @param string $name Unique module asset name.
         * @return bool True if an earlier instance registered this asset.
         */
        public function assetExists(string $kind, string $name): bool
        {
            return array_key_exists($kind . ':' . $name, $this->registrations);
        }

        /**
         * Register one versioned Events Ranking stylesheet exactly once.
         *
         * @param string $name CSS asset identifier.
         * @param string $url Relative CSS file path.
         * @param array $options URL/cache options.
         * @return void
         */
        public function registerStyle(string $name, string $url, array $options = []): void
        {
            $key = 'style:' . $name;

            if ($this->assetExists('style', $name)) {
                throw new \RuntimeException('Duplicate Events Ranking CSS registration.');
            }

            $this->registrations[$key] = [$url, $options];
        }

        /**
         * Enable the existing Events Ranking stylesheet for each module.
         *
         * @param string $name Pre-registered stylesheet asset name.
         * @return void
         */
        public function useStyle(string $name): void
        {
            if (!$this->assetExists('style', $name)) {
                throw new \RuntimeException('Events Ranking stylesheet used before registration.');
            }

            $this->uses[] = 'style:' . $name;
        }

        /**
         * Register one map script and remember its transitive prerequisites.
         *
         * @param string $name JavaScript asset name.
         * @param string $url Site-relative JavaScript URL.
         * @param array $options Cache options.
         * @param array $attributes Script HTML attributes.
         * @param array $dependencies Required script asset names.
         * @return void
         */
        public function registerScript(
            string $name,
            string $url,
            array $options = [],
            array $attributes = [],
            array $dependencies = []
        ): void {
            $key = 'script:' . $name;

            if ($this->assetExists('script', $name)) {
                throw new \RuntimeException('Duplicate Project Map JS registration.');
            }

            $this->registrations[$key] = [$url, $options, $attributes, $dependencies];
        }

        /**
         * Enable an already registered map script for every module instance.
         *
         * @param string $name Shared script identifier.
         * @return void
         */
        public function useScript(string $name): void
        {
            if (!$this->assetExists('script', $name)) {
                throw new \RuntimeException('Project Map script used before registration.');
            }

            $this->uses[] = 'script:' . $name;
        }
    }
}

namespace Joomla\CMS\Document {
    final class HtmlDocument
    {
        /**
         * Store the WebAssetManager shared by all rendered module instances.
         *
         * @param \SportsManagement\AssetTests\FakeAssets $assets Test asset registry.
         */
        public function __construct(private \SportsManagement\AssetTests\FakeAssets $assets) {}

        /**
         * Provide the current document's WebAssetManager for both modules.
         *
         * @return \SportsManagement\AssetTests\FakeAssets Shared registry.
         */
        public function getWebAssetManager(): \SportsManagement\AssetTests\FakeAssets
        {
            return $this->assets;
        }
    }
}

namespace {
    \define('_JEXEC', 1);
    require \dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_eventsranking/src/Dispatcher/Dispatcher.php';
    require \dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_projectmap/src/Dispatcher/Dispatcher.php';

    $eventsClass = \Diddipoeler\Module\SportsManagementEventsRanking\Site\Dispatcher\Dispatcher::class;
    $projectMapClass = \Diddipoeler\Module\SportsManagementProjectMap\Site\Dispatcher\Dispatcher::class;
    $events = new $eventsClass();
    $projectMap = new $projectMapClass();

    $assets = new \SportsManagement\AssetTests\FakeAssets();
    $document = new \Joomla\CMS\Document\HtmlDocument($assets);

    $useEvents = new \ReflectionMethod($events, 'useEventsRankingStylesheet');
    $useMap = new \ReflectionMethod($projectMap, 'useProjectMapScripts');

    $useEvents->invoke($events, $document);
    $useMap->invoke($projectMap, $document);
    $useEvents->invoke($events, $document);
    $useMap->invoke($projectMap, $document);

    $style = 'mod_sportsmanagement_eventsranking';
    $init = 'mod_sportsmanagement_projectmap.init';
    $worldmap = 'mod_sportsmanagement_projectmap.worldmap';

    $expected = [
        'style:' . $style => [
            'modules/mod_sportsmanagement_eventsranking/css/mod_sportsmanagement_eventsranking.css',
            ['version' => 'auto'],
        ],
        'script:' . $init => [
            'modules/mod_sportsmanagement_projectmap/htmlworldmap/projectmap-init.js',
            [], [], ['core'],
        ],
        'script:' . $worldmap => [
            'modules/mod_sportsmanagement_projectmap/htmlworldmap/worldmap.js',
            [], [], [$init],
        ],
    ];

    if ($assets->registrations !== $expected) {
        throw new \RuntimeException('Module asset registration or Project Map script dependency chain changed.');
    }

    $expectedUses = [
        'style:' . $style, 'script:' . $init, 'script:' . $worldmap,
        'style:' . $style, 'script:' . $init, 'script:' . $worldmap,
    ];

    if ($assets->uses !== $expectedUses) {
        throw new \RuntimeException('Event/Map assets were not activated on both renders in dependency order.');
    }

    // Test attachment to a different page: registrations are scoped to a
    // single document, so each new document must register its own assets.
    $secondAssets = new \SportsManagement\AssetTests\FakeAssets();
    $secondDocument = new \Joomla\CMS\Document\HtmlDocument($secondAssets);
    $useEvents->invoke($events, $secondDocument);
    $useMap->invoke($projectMap, $secondDocument);

    if ($secondAssets->registrations !== $expected
        || $secondAssets->uses !== array_slice($expectedUses, 0, 3)) {
        throw new \RuntimeException('Another Joomla page incorrectly reused a previous document registry.');
    }

    echo "Events Ranking and Project Map: reusable CSS/JS with preserved map dependencies OK.\n";
}
