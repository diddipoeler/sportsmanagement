<?php
/**
 * Joomla 5/6 administrator Matches WebAssetManager and model-error regression.
 *
 * Two views can share one HTML document; CSS and JavaScript must be registered
 * only once. Failed model queries must not be disclosed in user-facing errors.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace SportsManagement\MatchesTests {
    final class FakeAssets
    {
        public array $registered = [];
        public array $used = [];

        /**
         * Check whether the HTML asset registry already owns a named asset.
         *
         * @param string $type Asset type (style or script).
         * @param string $name Component asset name.
         * @return bool True if previously registered.
         */
        public function assetExists(string $type, string $name): bool
        {
            return isset($this->registered[$type . ':' . $name]);
        }

        /**
         * Store a CSS registration and reject duplicate asset declarations.
         *
         * @param string $name Stylesheet asset name.
         * @param string $url Site-relative CSS path.
         * @return void
         */
        public function registerStyle(string $name, string $url): void
        {
            if ($this->assetExists('style', $name)) {
                throw new \RuntimeException('Duplicate Matches CSS registration.');
            }

            $this->registered['style:' . $name] = [$url];
        }

        /**
         * Mark a stylesheet as used by one match-list render.
         *
         * @param string $name Registered stylesheet identifier.
         * @return void
         */
        public function useStyle(string $name): void
        {
            $this->used[] = 'style:' . $name;
        }

        /**
         * Register JavaScript once with its core dependency metadata.
         *
         * @param string $name Script asset identifier.
         * @param string $url Site-relative JavaScript path.
         * @param array $options Joomla script options.
         * @param array $attributes HTML attributes.
         * @param array $dependencies Prerequisite Joomla asset names.
         * @return void
         */
        public function registerScript(
            string $name,
            string $url,
            array $options,
            array $attributes,
            array $dependencies
        ): void {
            if ($this->assetExists('script', $name)) {
                throw new \RuntimeException('Duplicate Matches script registration.');
            }

            $this->registered['script:' . $name] = [$url, $options, $attributes, $dependencies];
        }

        /**
         * Mark the already registered script as used by each render.
         *
         * @param string $name Registered script asset identifier.
         * @return void
         */
        public function useScript(string $name): void
        {
            $this->used[] = 'script:' . $name;
        }
    }
}

namespace Joomla\CMS\Document {
    final class HtmlDocument
    {
        /**
         * Create the HTML document with a shared asset registry.
         *
         * @param \SportsManagement\MatchesTests\FakeAssets $assets The shared manager.
         */
        public function __construct(private \SportsManagement\MatchesTests\FakeAssets $assets) {}

        /**
         * Provide Joomla WebAssetManager functionality to Matches views.
         *
         * @return \SportsManagement\MatchesTests\FakeAssets Shared test registry.
         */
        public function getWebAssetManager(): \SportsManagement\MatchesTests\FakeAssets
        {
            return $this->assets;
        }
    }
}

namespace Joomla\CMS\MVC\View {
    class HtmlView
    {
        public mixed $errors = [];

        /**
         * Supply error records normally returned by the model.
         *
         * @param string $key Joomla MVC model result key.
         * @return mixed Error array or null for unrelated keys.
         */
        public function get(string $key): mixed
        {
            return $key === 'Errors' ? $this->errors : null;
        }

        /**
         * Return the document attached to this specific view.
         *
         * @return mixed HTML or deliberately unsupported document.
         */
        public function getDocument(): mixed
        {
            return $this->document;
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $entries = [];

        /**
         * Record redacted model diagnostics for privacy assertions.
         *
         * @param string $message Diagnostic without SQL details.
         * @param int $level Joomla log severity.
         * @param string $category Component log category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Keep the raw translation key for user-message assertions.
         *
         * @param string $key Joomla translation string key.
         * @return string Original string key.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/admin/src/View/Matches/HtmlView.php';

    $type = \Diddipoeler\Component\SportsManagement\Administrator\View\Matches\HtmlView::class;
    $view = new $type();
    $manager = new \SportsManagement\MatchesTests\FakeAssets();
    $view->document = new \Joomla\CMS\Document\HtmlDocument($manager);
    $activate = new \ReflectionMethod($view, 'registerMatchesAssets');
    $activate->invoke($view);
    $activate->invoke($view);

    if ($manager->registered !== [
        'style:com_sportsmanagement.matches.form' => [
            'administrator/components/com_sportsmanagement/assets/css/form_control.css',
        ],
        'script:com_sportsmanagement.matches.admin' => [
            'administrator/components/com_sportsmanagement/assets/js/matches.js',
            [], [], ['core'],
        ],
    ]) {
        throw new \RuntimeException('Matches CSS/JS asset registrations changed or duplicated.');
    }

    if ($manager->used !== [
        'style:com_sportsmanagement.matches.form', 'script:com_sportsmanagement.matches.admin',
        'style:com_sportsmanagement.matches.form', 'script:com_sportsmanagement.matches.admin',
    ]) {
        throw new \RuntimeException('Matches WebAssetManager did not reuse existing asset entries.');
    }

    // Non-HTML responses must never receive JavaScript or stylesheets.
    $view->document = null;
    $activate->invoke($view);
    $view->document = new \stdClass();
    $activate->invoke($view);

    if (count($manager->registered) !== 2 || count($manager->used) !== 4) {
        throw new \RuntimeException('Matches view attempted to register assets without HTML.');
    }

    $errors = new \ReflectionMethod($view, 'assertNoModelErrors');
    $view->errors = [];
    $errors->invoke($view);
    $view->errors = [
        'SQL SELECT password_hash FROM #__users',
        'Database secret-connection-token',
    ];

    try {
        $errors->invoke($view);
        throw new \RuntimeException('Failing Matches model was not rejected.');
    } catch (\RuntimeException $exception) {
        if ($exception->getMessage() !== 'JERROR_AN_ERROR_HAS_OCCURRED'
            || $exception->getCode() !== 500) {
            throw new \RuntimeException('Matches view exposed internal error text.', 0, $exception);
        }
    }

    $log = \Joomla\CMS\Log\Log::$entries;
    if (count($log) !== 1
        || !str_contains($log[0][0], '2 errors')
        || $log[0][1] !== \Joomla\CMS\Log\Log::ERROR
        || $log[0][2] !== 'com_sportsmanagement'
        || str_contains($log[0][0], 'password_hash')
        || str_contains($log[0][0], 'secret-connection-token')) {
        throw new \RuntimeException('Matches model log leaked SQL details or missed diagnostic count.');
    }

    echo "Administrator Matches: shared HTML assets and redacted model errors OK.\n";
}
