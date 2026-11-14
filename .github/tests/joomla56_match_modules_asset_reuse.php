<?php
/**
 * Verify repeatable Joomla WebAssetManager use by Matches and Matches Slider.
 *
 * Exercises the native module dispatchers against one fake HTML document,
 * including multiple layouts, absent optional CSS and repeated slider assets.
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

namespace SportsManagement\MatchModuleTests {
    final class FakeAssets
    {
        public array $registered = [];
        public array $used = [];

        /**
         * Check whether the current HTML response already registered an asset.
         *
         * @param string $type Joomla asset type.
         * @param string $name Unique stylesheet or script identifier.
         * @return bool True when an earlier module owns the asset name.
         */
        public function assetExists(string $type, string $name): bool
        {
            return isset($this->registered[$type . ':' . $name]);
        }

        /**
         * Save the stylesheet path and options, rejecting duplicate names.
         *
         * @param string $name Joomla asset name.
         * @param string $uri Site-relative stylesheet URL.
         * @param array $options Cache version options.
         * @return void
         */
        public function registerStyle(string $name, string $uri, array $options = []): void
        {
            if ($this->assetExists('style', $name)) {
                throw new \RuntimeException('Duplicate match module stylesheet.');
            }

            $this->registered['style:' . $name] = [$uri, $options];
        }

        /**
         * Record every use of a previously registered module stylesheet.
         *
         * @param string $name Existing Joomla stylesheet name.
         * @return void
         */
        public function useStyle(string $name): void
        {
            if (!$this->assetExists('style', $name)) {
                throw new \RuntimeException('Matches stylesheet used before registration.');
            }

            $this->used[] = 'style:' . $name;
        }

        /**
         * Save script path, automatic version and deferred script attributes.
         *
         * @param string $name Script asset identifier.
         * @param string $uri Site-relative script path.
         * @param array $options Version/cache URL options.
         * @param array $attributes Script element attributes.
         * @return void
         */
        public function registerScript(string $name, string $uri, array $options = [], array $attributes = []): void
        {
            if ($this->assetExists('script', $name)) {
                throw new \RuntimeException('Duplicate Matches Slider script.');
            }

            $this->registered['script:' . $name] = [$uri, $options, $attributes];
        }

        /**
         * Record each activation of an existing deferred slider script.
         *
         * @param string $name Shared script name.
         * @return void
         */
        public function useScript(string $name): void
        {
            if (!$this->assetExists('script', $name)) {
                throw new \RuntimeException('Matches Slider script used before registration.');
            }

            $this->used[] = 'script:' . $name;
        }
    }
}

namespace Joomla\CMS\Document {
    final class HtmlDocument
    {
        /**
         * Create the frontend document with a shared fake WebAssetManager.
         *
         * @param \SportsManagement\MatchModuleTests\FakeAssets $assets Shared asset registry.
         */
        public function __construct(private \SportsManagement\MatchModuleTests\FakeAssets $assets) {}

        /**
         * Supply the shared registry to both module dispatchers.
         *
         * @return \SportsManagement\MatchModuleTests\FakeAssets The asset registry.
         */
        public function getWebAssetManager(): \SportsManagement\MatchModuleTests\FakeAssets
        {
            return $this->assets;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = sys_get_temp_dir() . '/jsm-modules-assets-' . bin2hex(random_bytes(8));
    define('JPATH_ROOT', $root);

    require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_matches/src/Dispatcher/Dispatcher.php';
    require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_matchesslider/src/Dispatcher/Dispatcher.php';

    $shared = new \SportsManagement\MatchModuleTests\FakeAssets();
    $document = new \Joomla\CMS\Document\HtmlDocument($shared);
    $matches = new \Diddipoeler\Module\SportsManagementMatches\Site\Dispatcher\Dispatcher();
    $slider = new \Diddipoeler\Module\SportsManagementMatchesSlider\Site\Dispatcher\Dispatcher();
    $matchesStyle = new \ReflectionMethod($matches, 'useMatchesStyles');
    $sliderAssets = new \ReflectionMethod($slider, 'useSliderAssets');

    $layouts = ['default_tableless', 'compact'];
    foreach ($layouts as $layout) {
        $dir = $root . '/modules/mod_sportsmanagement_matches/tmpl/' . $layout;
        if (!is_dir($dir) && !mkdir($dir, 0700, true)) {
            throw new \RuntimeException('Could not create isolated template CSS fixture.');
        }

        if (file_put_contents($dir . '/mod_sportsmanagement_matches.css', '/* test */') === false) {
            throw new \RuntimeException('Could not create optional template stylesheet.');
        }
    }

    try {
        $matchesStyle->invoke($matches, $document, 'default_tableless');
        $matchesStyle->invoke($matches, $document, 'default_tableless');
        $matchesStyle->invoke($matches, $document, 'compact');
        $matchesStyle->invoke($matches, $document, 'missing');
        $sliderAssets->invoke($slider, $document);
        $sliderAssets->invoke($slider, $document);

        $native = 'mod_sportsmanagement_matches.native';
        $prefix = 'mod_sportsmanagement_matches.template.';
        $sliderName = 'mod_sportsmanagement_matchesslider';

        $expected = [
            'style:' . $native => ['modules/mod_sportsmanagement_matches/assets/css/native.css', []],
            'style:' . $prefix . 'default_tableless' => [
                'modules/mod_sportsmanagement_matches/tmpl/default_tableless/mod_sportsmanagement_matches.css',
                [],
            ],
            'style:' . $prefix . 'compact' => [
                'modules/mod_sportsmanagement_matches/tmpl/compact/mod_sportsmanagement_matches.css',
                [],
            ],
            'script:' . $sliderName => [
                'modules/mod_sportsmanagement_matchesslider/assets/js/matchesslider.js',
                ['version' => 'auto'], ['defer' => true],
            ],
            'style:' . $sliderName => [
                'modules/mod_sportsmanagement_matchesslider/assets/css/mod_sportsmanagement_matchesslider.css',
                ['version' => 'auto'],
            ],
        ];

        if ($shared->registered !== $expected) {
            throw new \RuntimeException('Match module CSS and script assets duplicated or lost configuration.');
        }

        $expectedUses = [
            'style:' . $native, 'style:' . $prefix . 'default_tableless',
            'style:' . $native, 'style:' . $prefix . 'default_tableless',
            'style:' . $native, 'style:' . $prefix . 'compact',
            'style:' . $native,
            'script:' . $sliderName, 'style:' . $sliderName,
            'script:' . $sliderName, 'style:' . $sliderName,
        ];

        if ($shared->used !== $expectedUses) {
            throw new \RuntimeException('Match modules failed to activate assets for repeated renders.');
        }
    } finally {
        foreach ($layouts as $layout) {
            $file = $root . '/modules/mod_sportsmanagement_matches/tmpl/'
                . $layout . '/mod_sportsmanagement_matches.css';
            if (is_file($file)) {
                unlink($file);
            }

            $dir = dirname($file);
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }

        $parent = $root . '/modules/mod_sportsmanagement_matches/tmpl';
        foreach ([$parent, dirname($parent), dirname(dirname($parent)), $root] as $dir) {
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }

    echo "Matches and Matches Slider: shared HTML assets and two optional templates OK.\n";
}
