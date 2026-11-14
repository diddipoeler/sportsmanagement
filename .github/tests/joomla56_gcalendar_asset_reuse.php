<?php
/**
 * Test repeatable GCalendar WebAssetManager registration without a Joomla install.
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

namespace SportsManagement\Tests {
    /**
     * Record the registry and activation calls normally handled by Joomla.
     *
     * An existing asset must never be registered a second time, whereas both
     * module instances must be able to mark it for output.
     */
    final class FakeAssets
    {
        public array $registrations = [];
        public array $uses = [];

        /**
         * Report whether an asset was registered by an earlier calendar instance.
         *
         * @param string $type Web asset type.
         * @param string $name Shared module asset name.
         * @return bool Whether the registry already contains the asset.
         */
        public function assetExists(string $type, string $name): bool
        {
            return isset($this->registrations[$type . ':' . $name]);
        }

        /**
         * Record the stylesheet's first registration and its cache options.
         *
         * @param string $name Web asset identifier.
         * @param string $uri Relative CSS file path.
         * @param array $options Asset URL options.
         * @return void
         */
        public function registerStyle(string $name, string $uri, array $options): void
        {
            $this->registrations['style:' . $name] = [$uri, $options];
        }

        /**
         * Record the script's first registration and its defer attribute.
         *
         * @param string $name Web asset identifier.
         * @param string $uri Relative JavaScript path.
         * @param array $options Asset URL options.
         * @param array $attributes HTML script attributes.
         * @return void
         */
        public function registerScript(string $name, string $uri, array $options, array $attributes): void
        {
            $this->registrations['script:' . $name] = [$uri, $options, $attributes];
        }

        /**
         * Record each module's stylesheet activation.
         *
         * @param string $name Registered asset name.
         * @return void
         */
        public function useStyle(string $name): void
        {
            $this->uses[] = 'style:' . $name;
        }

        /**
         * Record each module's deferred-script activation.
         *
         * @param string $name Registered asset name.
         * @return void
         */
        public function useScript(string $name): void
        {
            $this->uses[] = 'script:' . $name;
        }
    }
}

namespace Joomla\CMS\Document {
    class HtmlDocument
    {
        /**
         * Provide the shared fake registry for multiple module instances.
         *
         * @param \SportsManagement\Tests\FakeAssets $assets Shared fake asset manager.
         */
        public function __construct(private \SportsManagement\Tests\FakeAssets $assets) {}

        /**
         * Return the document's registry, matching Joomla's public API.
         *
         * @return \SportsManagement\Tests\FakeAssets Shared web asset registry.
         */
        public function getWebAssetManager(): \SportsManagement\Tests\FakeAssets
        {
            return $this->assets;
        }
    }
}

namespace {
    define('_JEXEC', 1);

    require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_gcalendar/src/Dispatcher/Dispatcher.php';

    $registry = new \SportsManagement\Tests\FakeAssets();
    $document = new \Joomla\CMS\Document\HtmlDocument($registry);
    $dispatcher = new \Diddipoeler\Module\SportsManagementGcalendar\Site\Dispatcher\Dispatcher();
    $register = new \ReflectionMethod($dispatcher, 'registerCalendarAssets');

    // Two instances share the document; repeating a registration must be harmless.
    $register->invoke($dispatcher, $document);
    $first = $registry->registrations;
    $register->invoke($dispatcher, $document);

    $name = 'mod_sportsmanagement_gcalendar.calendar';
    $css = $first['style:' . $name] ?? null;
    $js = $first['script:' . $name] ?? null;

    if (count($first) !== 2 || $first !== $registry->registrations
        || $css !== ['modules/mod_sportsmanagement_gcalendar/tmpl/gcalendar.css', ['version' => 'auto']]
        || $js !== ['modules/mod_sportsmanagement_gcalendar/js/gcalendar.js', ['version' => 'auto'], ['defer' => true]]
        || $registry->uses !== [
            'style:' . $name, 'script:' . $name,
            'style:' . $name, 'script:' . $name,
        ]) {
        fwrite(STDERR, "GCalendar shared web assets are not reused safely.\n");
        exit(1);
    }

    echo "GCalendar assets: one registration and two activations per type.\n";
}
