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
    $assets = new class {
        public array $registered = [];
        public array $used = [];
        /** Check the registry for a named script. @param string $type Asset kind. @param string $name Identifier. @return bool Presence. */
        public function assetExists(string $type, string $name): bool { return isset($this->registered[$name]); }
        /** Record a unique script and its configuration. @param string $name Asset ID. @param string $url Script URI. @param array $options Cache options. @param array $attributes HTML attributes. @return void */
        public function registerScript(string $name, string $url, array $options, array $attributes): void
        {
            if (isset($this->registered[$name])) {
                throw new \RuntimeException('Duplicate asset registration.');
            }
            $this->registered[$name] = [$url, $options, $attributes];
        }
        /** Activate a registered script. @param string $name Asset identifier. @return void */
        public function useScript(string $name): void { $this->used[] = $name; }
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
    echo "Native document injection and shared assets OK\n";
}
