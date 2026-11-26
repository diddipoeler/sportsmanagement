<?php
/**
 * Verify native frontend document injection and shared asset registration.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Document {
    class Document {}
    final class HtmlDocument extends Document
    {
        /**
         * Store a shared web asset registry for the HTML document.
         * @param object $assets Fake Joomla web asset manager.
         */
        public function __construct(private object $assets) {}
        /**
         * Return the same registry across successive view injections.
         * @return object Shared manager.
         */
        public function getWebAssetManager(): object { return $this->assets; }
    }
}
namespace Joomla\CMS\MVC\View {
    class HtmlView
    {
        public ?\Joomla\CMS\Document\Document $document = null;
        /**
         * Store the document as Joomla's native base view does.
         * @param \Joomla\CMS\Document\Document $document Response document.
         * @return void
         */
        public function setDocument(\Joomla\CMS\Document\Document $document): void { $this->document = $document; }

        /** Return the current response document. @return \Joomla\CMS\Document\Document|null Injected document. */
        public function getDocument(): ?\Joomla\CMS\Document\Document { return $this->document; }
    }
}
namespace Joomla\CMS\Uri {
    final class Uri
    {
        /**
         * Provide the installation subdirectory used by asset URLs.
         * @param bool $pathOnly Return the path portion.
         * @return string Test site root.
         */
        public static function root(bool $pathOnly = false): string { return '/joomla'; }
    }
}
namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/site/src/View/SportsManagementHtmlView.php';
    require dirname(__DIR__, 2) . '/site/src/View/SportsManagementProjectHtmlView.php';
    $assets = new class {
        public array $registered = [];
        public array $used = [];
        public array $styles = [];
        public array $usedStyles = [];
        public array $dependencies = [];
        /** Check the registry for a named script. @param string $type Asset kind. @param string $name Identifier. @return bool Presence. */
        public function assetExists(string $type, string $name): bool { return $type === 'style' ? isset($this->styles[$name]) : isset($this->registered[$name]); }
        /** Record a unique script and its configuration. @param string $name Asset ID. @param string $url Script URI. @param array $options Cache options. @param array $attributes HTML attributes. @return void */
        public function registerScript(string $name, string $url, array $options, array $attributes, array $dependencies = []): void
        {
            if (isset($this->registered[$name])) {
                throw new \RuntimeException('Duplicate asset registration.');
            }
            $this->registered[$name] = [$url, $options, $attributes];
            $this->dependencies[$name] = $dependencies;
        }
        /** Activate a registered script. @param string $name Asset identifier. @return void */
        public function useScript(string $name): void { $this->used[] = $name; }
        /** Register a stylesheet only once. @param string $name Asset identifier. @param string $url Source. @return void */
        public function registerStyle(string $name, string $url): void
        {
            if (isset($this->styles[$name])) {
                throw new \RuntimeException('Duplicate stylesheet registration.');
            }
            $this->styles[$name] = $url;
        }
        /** Activate the shared stylesheet. @param string $name Asset name. @return void */
        public function useStyle(string $name): void { $this->usedStyles[] = $name; }
    };
    $view = new class extends \Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView {
        /** Bypass request initialization to isolate document injection. */
        public function __construct() {}
    };
    $plain = new \Joomla\CMS\Document\Document();
    $view->setDocument($plain);
    if ($view->document !== $plain || $assets->registered !== []) {
        throw new \RuntimeException('Non-HTML document injection was not preserved.');
    }
    $html = new \Joomla\CMS\Document\HtmlDocument($assets);
    $other = clone $view;
    $view->setDocument($html);
    $other->setDocument($html);
    $view->setDocument($html);
    $name = 'com_sportsmanagement.site.modal-image-popup';
    if ($view->document !== $html || $other->document !== $html
        || $assets->registered !== [$name => ['/joomla/components/com_sportsmanagement/assets/js/modal-image-popup.js', ['version' => 'auto'], ['defer' => true]]]
        || $assets->used !== [$name, $name, $name]) {
        throw new \RuntimeException('Shared HTML assets were not registered and activated correctly.');
    }
    $projectView = new class extends \Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView {
        /** Construct an isolated project view without a Joomla request. */
        public function __construct()
        {
            $this->params = new class {
                /** Supply default presentation settings. @param string $key Parameter name. @param mixed $default Fallback. @return mixed Value. */
                public function get(string $key, mixed $default = null): mixed { return $default; }
            };
        }
        /** Keep legacy helper loading out of this native asset test. @return bool Always false. */
        protected function requiresLegacyPresentationDependencies(): bool { return false; }
    };
    $base = \Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView::class;
    $common = new \ReflectionMethod($base, 'loadPresentationDependencies');
    $optional = new \ReflectionMethod($base, 'loadPresentationAssets');
    $projectView->setDocument($plain);
    $common->invoke($projectView);
    $optional->invoke($projectView);
    if ($assets->styles !== []) {
        throw new \RuntimeException('Non-HTML project view registered styles.');
    }
    $secondProjectView = clone $projectView;
    foreach ([$projectView, $secondProjectView, $projectView] as $instance) {
        $instance->setDocument($html);
        $common->invoke($instance);
        $optional->invoke($instance);
    }
    $jce = 'com_sportsmanagement.site.jcemediabox';
    if (count($assets->styles) !== 7 || count($assets->registered) !== 2
        || $assets->dependencies[$jce] !== ['jquery']
        || count($assets->usedStyles) !== 21) {
        throw new \RuntimeException('Project presentation assets were not reused across views.');
    }
    echo "Native document injection and shared project assets OK\n";
}
