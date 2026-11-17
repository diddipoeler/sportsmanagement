<?php
/**
 * Runtime regression for Joomla 5/6 federation country dropdown rendering.
 *
 * Run: php .github/tests/joomla56-federation-country-dropdown.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\HTML {
    /** Standalone replacements for template HTML helper calls. */
    final class HTMLHelper
    {
        /**
         * Return harmless markup for the Joomla template helper calls.
         *
         * @param string $key Joomla HTML helper method.
         * @param mixed ...$args Optional helper arguments.
         * @return string Marker-free HTML.
         */
        public static function _(string $key, mixed ...$args): string
        {
            return '';
        }
    }
}

namespace Joomla\CMS\Language {
    /** Return translation keys for deterministic test assertions. */
    final class Text
    {
        /**
         * Return a translation key without needing Joomla language files.
         *
         * @param string $key Text translation key.
         * @return string Raw translation key.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\Router {
    /** Bypass Joomla routing in the isolated administrator test. */
    final class Route
    {
        /**
         * Return the supplied administrator route unchanged.
         *
         * @param string $url Internal Joomla URL.
         * @return string Given URL.
         */
        public static function _(string $url): string
        {
            return $url;
        }
    }
}

namespace Joomla\CMS\Uri {
    /** Provide a deterministic Joomla installation URL. */
    final class Uri
    {
        /**
         * Return the fake site root.
         *
         * @return string Site URL.
         */
        public static function root(): string
        {
            return 'https://example.test/';
        }
    }
}

namespace {
    \define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    $formFile = $root . '/admin/forms/jlextfederation.xml';

    // Check the actual Joomla 5/6 form definition, not a legacy-only copy.
    $form = simplexml_load_file($formFile);

    if ($form === false) {
        throw new \RuntimeException('Native federation form XML could not be loaded.');
    }

    $countryFields = $form->xpath('//fieldset[@name="details"]/field[@name="country"]');

    if (!is_array($countryFields) || count($countryFields) !== 1
        || strtolower((string) $countryFields[0]['type']) !== 'country') {
        throw new \RuntimeException('Federation edit form lacks the native country list field.');
    }

    $countryOptions = [
        (object) ['value' => 'DEU', 'text' => 'Deutschland'],
        (object) ['value' => 'FRA', 'text' => 'France & Europe'],
        (object) ['value' => '../unsafe', 'text' => 'Invalid'],
        (object) ['value' => 'ESP', 'text' => ['invalid', 'label']],
    ];

    $view = new class($countryOptions) {
        /** @var array<int,object> Federation records. */
        public array $items = [];
        /** @var null No pagination in the isolated fixture. */
        public $pagination = null;
        /** @var object Joomla-like state fixture. */
        public object $state;
        /** @var array<int,object> Country options for the list filter. */
        public array $countryOptions;

        /**
         * Create the standalone administrator list view fixture.
         *
         * @param array<int,object> $options Sample country records.
         */
        public function __construct(array $options)
        {
            $this->countryOptions = $options;
            $this->state = new class {
                /**
                 * Retrieve the current selected country and list state.
                 *
                 * @param string $key Joomla list state key.
                 * @param mixed $default Value used for other keys.
                 * @return mixed Selected country or the default.
                 */
                public function get(string $key, mixed $default = ''): mixed
                {
                    return $key === 'filter.search_nation' ? 'DEU' : $default;
                }
            };
        }

        /**
         * Render the real native federation list template in object scope.
         *
         * @param string $path Template path within the repository.
         * @return string Rendered HTML markup.
         */
        public function render(string $path): string
        {
            ob_start();

            try {
                include $path;
                return (string) ob_get_contents();
            } finally {
                ob_end_clean();
            }
        }
    };

    $html = $view->render($root . '/admin/tmpl/jlextfederations/default.php');

    if (!str_contains($html, '<select class="form-select" name="filter_search_nation"')
        || str_contains($html, 'type="text" name="filter_search_nation"')
        || !str_contains($html, '<option value="DEU" selected>Deutschland</option>')
        || !str_contains($html, '<option value="FRA">France &amp; Europe</option>')
        || str_contains($html, 'value="../unsafe"')
        || str_contains($html, 'value="ESP"')
        || !str_contains($html, '&filter_search_nation=')) {
        throw new \RuntimeException('Federation list failed to render a safe, populated country dropdown.');
    }

    $model = file_get_contents($root . '/admin/src/Model/JlextfederationsModel.php');
    $controllerView = file_get_contents($root . '/admin/src/View/Jlextfederations/HtmlView.php');

    if (!is_string($model) || !is_string($controllerView)
        || !str_contains($model, 'function getCountryOptions(): array')
        || !str_contains($model, '#__sportsmanagement_federations')
        || !str_contains($model, 'CountryOptionsHelper::getOptions($db)')
        || !str_contains($controllerView, "\$this->get('CountryOptions')")) {
        throw new \RuntimeException('Federation country dropdown data is not connected to the model.');
    }

    echo "Joomla 5/6 federation country dropdown regressions passed.\n";
}
