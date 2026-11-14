<?php
/**
 * Check repeatable Ranking stylesheet registration across Joomla module instances.
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

namespace Joomla\CMS\Document {
    final class HtmlDocument
    {
        /**
         * Supply the fake Joomla WebAssetManager shared by module instances.
         *
         * @param \SportsManagement\RankingTests\FakeAssets $assets Shared registry.
         */
        public function __construct(private \SportsManagement\RankingTests\FakeAssets $assets) {}

        /**
         * Return the same asset manager for every module instance.
         *
         * @return \SportsManagement\RankingTests\FakeAssets Registry and usage recorder.
         */
        public function getWebAssetManager(): \SportsManagement\RankingTests\FakeAssets
        {
            return $this->assets;
        }
    }
}

namespace SportsManagement\RankingTests {
    final class FakeAssets
    {
        public array $registered = [];
        public array $used = [];

        /**
         * Report whether a style name is already registered on the document.
         *
         * @param string $type Joomla asset type.
         * @param string $name Shared Ranking asset identifier.
         * @return bool True if another module registered the style.
         */
        public function assetExists(string $type, string $name): bool
        {
            return array_key_exists($type . ':' . $name, $this->registered);
        }

        /**
         * Record the first stylesheet registration and its version options.
         *
         * @param string $name Shared Joomla asset identifier.
         * @param string $uri Component-relative CSS file.
         * @param array $options Cache version options.
         * @return void
         */
        public function registerStyle(string $name, string $uri, array $options = []): void
        {
            if ($this->assetExists('style', $name)) {
                throw new \RuntimeException('Duplicate ranking CSS registration.');
            }

            $this->registered['style:' . $name] = [$uri, $options];
        }

        /**
         * Record every instance that requests the registered stylesheet.
         *
         * @param string $name Shared Ranking asset identifier.
         * @return void
         */
        public function useStyle(string $name): void
        {
            if (!$this->assetExists('style', $name)) {
                throw new \RuntimeException('Ranking CSS was used before registration.');
            }

            $this->used[] = $name;
        }
    }
}

namespace {
    define('_JEXEC', 1);

    require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_ranking/src/Dispatcher/Dispatcher.php';

    $assetName = 'mod_sportsmanagement_ranking';
    $style = 'modules/mod_sportsmanagement_ranking/css/mod_sportsmanagement_ranking.css';
    $registry = new \SportsManagement\RankingTests\FakeAssets();
    $document = new \Joomla\CMS\Document\HtmlDocument($registry);
    $dispatcher = new \Diddipoeler\Module\SportsManagementRanking\Site\Dispatcher\Dispatcher();

    $method = new \ReflectionMethod($dispatcher, 'useRankingStylesheet');
    $method->invoke($dispatcher, $document, $assetName, $style);
    $method->invoke($dispatcher, $document, $assetName, $style);

    if ($registry->registered !== [
            'style:' . $assetName => [$style, ['version' => 'auto']],
        ] || $registry->used !== [$assetName, $assetName]) {
        throw new \RuntimeException('Ranking module CSS is not reused across module instances.');
    }

    echo "Ranking styles: one registry entry, two module activations.\n";
}
