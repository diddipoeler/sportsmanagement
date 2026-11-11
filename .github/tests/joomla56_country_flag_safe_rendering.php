<?php
/**
 * Joomla 5/6 country flag image URI and attributes regression coverage.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        public static string $placeholder = '';
        public static mixed $css = 0;

        /**
         * Supply simulated country display parameters for Joomla site tests.
         * @param string $component Component option requested by the helper.
         * @return object In-memory registry fixture.
         */
        public static function getParams(string $component): object
        {
            return new class {
                /**
                 * Read the simulated country flag mode or placeholder option.
                 * @param string $key Requested component setting.
                 * @param mixed $default Fallback for absent settings.
                 * @return mixed Simulated registry setting or default.
                 */
                public function get(string $key, mixed $default = null): mixed
                {
                    return match ($key) {
                        'cfg_flags_css' => ComponentHelper::$css,
                        'ph_flags' => ComponentHelper::$placeholder,
                        default => $default,
                    };
                }
            };
        }
    }
}

namespace Joomla\CMS\HTML {
    final class HTMLHelper
    {
        public static bool $malformedOptionText = false;

        /**
         * Create the Joomla select option object used by country selectors.
         *
         * @param string $task Joomla HTML helper task identifier.
         * @param string $value Selected ISO country code.
         * @param string $label Visible translated option label.
         * @return object Simulated select option.
         */
        public static function _(string $task, string $value, string $label): object
        {
            if ($task !== 'select.option') {
                throw new \RuntimeException('Unexpected Joomla HTML helper task.');
            }

            return (object) ['value' => $value, 'text' => self::$malformedOptionText ? ['invalid'] : $label];
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return translation fixture text without loading language files.
         * @param string $text Translation key or display label.
         * @return string Unchanged test text.
         */
        public static function _(string $text): string
        {
            return $text;
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 400;
        public static array $messages = [];

        /**
         * Capture country selector errors without a Joomla logging service.
         *
         * @param string $message Logged country query or iterator error.
         * @param int $level Joomla severity level.
         * @param string $category Component logging category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$messages[] = [$message, $level, $category];
        }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        /**
         * Return a stable Joomla installation root for image path checks.
         * @return string HTTPS root of the test site.
         */
        public static function root(): string
        {
            return 'https://sports.example/joomla/';
        }
    }
}

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementSiteApplicationResolver
    {
        /**
         * Build an isolated frontend application with predictable input.
         * @return object Site application test fixture.
         */
        public static function resolve(): object
        {
            return new class {
                /**
                 * Expose a lightweight Joomla input fixture for database selection.
                 * @return object Input object with an integer getter.
                 */
                public function getInput(): object
                {
                    return new class {
                        /**
                         * Supply the configured default for a Joomla request integer.
                         * @param string $key Requested input field.
                         * @param int $default Default input value.
                         * @return int The supplied default.
                         */
                        public function getInt(string $key, int $default = 0): int
                        {
                            return $default;
                        }
                    };
                }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Helper/CountryPresentationHelper.php';

    /**
     * Fluent database query fixture for country selector data.
     */
    final class FrontendCountryOptionQuery
    {
        /**
         * Accept the requested country code and name fields.
         *
         * @param array $columns Country query columns.
         * @return self The query fixture for chaining.
         */
        public function select(array $columns): self
        {
            return $this;
        }

        /**
         * Accept the country table source without SQL execution.
         *
         * @param string $table Country table name.
         * @return self Query fixture for chaining.
         */
        public function from(string $table): self
        {
            return $this;
        }
    }

    /**
     * Simulate a Joomla database containing malformed legacy country rows.
     */
    final class FrontendCountryOptionDatabase implements \Joomla\Database\DatabaseInterface
    {
        public mixed $overrideRows = null;

        /**
         * Provide the query factory required by native Joomla database helpers.
         *
         * @return FrontendCountryOptionQuery New fluent query object.
         */
        public function createQuery(): FrontendCountryOptionQuery
        {
            return new FrontendCountryOptionQuery();
        }

        /**
         * Return column names unchanged in the in-memory query fixture.
         *
         * @param string $name Country table or column name.
         * @return string The requested identifier.
         */
        public function quoteName(string $name): string
        {
            return $name;
        }

        /**
         * Accept the prepared query without executing a SQL statement.
         *
         * @param object $query Query fixture.
         * @return void
         */
        public function setQuery(object $query): void
        {
        }

        /**
         * Return country options with deliberately corrupted imported fields.
         *
         * @return mixed Valid rows or an overridden malformed database return.
         */
        public function loadObjectList(): mixed
        {
            // Repeated ISO codes from old imports must yield one option.
            return $this->overrideRows ?? [
                (object) ['alpha3' => 'DEU', 'name' => 'Germany'],
                ['alpha3' => 'USA', 'name' => 'invalid array row'],
                (object) ['alpha3' => 'fra', 'name' => 'France'],
                (object) ['alpha3' => 'deu', 'name' => 'Duplicate Germany'],
                (object) ['alpha3' => 'NLD', 'name' => ['invalid']],
                (object) ['alpha3' => 'INVALID', 'name' => 'Bad ISO'],
                (object) ['alpha3' => ['invalid'], 'name' => 'Bad value'],
            ];
        }
    }

    $class = \Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper::class;
    // Do not bootstrap Joomla's database for invalid country input, and
    // discard malformed rows before building a frontend country selector.
    if ($class::name('BAD!') !== '') {
        throw new \RuntimeException('Invalid country code triggered a lookup.');
    }

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $options = $class::options(new FrontendCountryOptionDatabase());
        // Database adapters and corrupted imports may unexpectedly return
        // a scalar or a single object instead of a country row collection.
        foreach ([false, 7, 'unexpected', new \stdClass()] as $invalidResult) {
            $database = new FrontendCountryOptionDatabase();
            $database->overrideRows = $invalidResult;

            if ($class::options($database) !== []) {
                throw new \RuntimeException('Non-iterable country result was accepted.');
            }
        }

        // A generator is iterable, but its iterator may fail after the
        // first valid row. The frontend selector must fail closed and log.
        $brokenRows = static function (): \Generator {
            yield (object) ['alpha3' => 'DEU', 'name' => 'Germany'];
            throw new \RuntimeException('broken country row iterator');
        };
        $database = new FrontendCountryOptionDatabase();
        $database->overrideRows = $brokenRows();
        \Joomla\CMS\Log\Log::$messages = [];

        if ($class::options($database) !== []) {
            throw new \RuntimeException('Broken country iterator exposed partial select options.');
        }

        if (count(\Joomla\CMS\Log\Log::$messages) !== 1
            || !str_contains(\Joomla\CMS\Log\Log::$messages[0][0], 'broken country row iterator')
            || \Joomla\CMS\Log\Log::$messages[0][2] !== 'com_sportsmanagement') {
            throw new \RuntimeException('Broken country iterator was not logged privately.');
        }

        // Sorting must not crash when a broken selection helper returns
        // malformed option text values from a legacy override.
        \Joomla\CMS\HTML\HTMLHelper::$malformedOptionText = true;
        \Joomla\CMS\Log\Log::$messages = [];

        try {
            if ($class::options(new FrontendCountryOptionDatabase()) !== []) {
                throw new \RuntimeException('Malformed country sort labels produced a selector.');
            }
        } finally {
            \Joomla\CMS\HTML\HTMLHelper::$malformedOptionText = false;
        }

        if (count(\Joomla\CMS\Log\Log::$messages) !== 1) {
            throw new \RuntimeException('Malformed country option sorting failure was not logged.');
        }

        $codes = array_map(static fn (object $item): string => $item->value, $options);
        $labels = array_map(static fn (object $item): string => $item->text, $options);

        if ($codes !== ['FRA', 'DEU', 'NLD'] || $labels !== ['France', 'Germany', 'NLD']) {
            throw new \RuntimeException('Malformed frontend country rows bypassed ISO validation or sorting.');
        }
    } finally {
        restore_error_handler();
    }

    // Not every translated address format includes %ADDRESS%. Do not
    // append a stray closing div when only a city or name is shown.
    $cityOnly = $class::address('Arena', '', '', '', 'Berlin', '', '%NAME% / %LOCATION%');
    if ($cityOnly !== '<span itemprop="name">Arena</span> / <span itemprop="addressLocality">Berlin</span>&nbsp;') {
        throw new \RuntimeException('Country address layout without street generated unmatched markup.');
    }

    $withStreet = $class::address('Arena', 'Main Street', '', '', 'Berlin', '', '%ADDRESS% %LOCATION%');
    if (!str_contains($withStreet, 'itemtype="https://schema.org/PostalAddress"')
        || !str_ends_with($withStreet, '</div>&nbsp;')) {
        throw new \RuntimeException('Country address layout lost its opened address container.');
    }

    $safeSource = new \ReflectionMethod($class, 'safeFlagSource');

    foreach ([
        'images/flags/de.png' => 'https://sports.example/joomla/images/flags/de.png',
        '/images/flags/de.png' => 'https://sports.example/joomla/images/flags/de.png',
        'https://cdn.example/flag.png' => 'https://cdn.example/flag.png',
        '' => '',
        '//evil.example/image.png' => '',
        '%2f%2fevil.example/image.png' => '',
        '%252f%252fevil.example/image.png' => '',
        'https%3a%2f%2fevil.example/image.png' => '',
        'javascript%3aalert(1)' => '',
        'data%253atext/html,evil' => '',
        'images/%2f%2fevil.example/image.png' => 'https://sports.example/joomla/images/%2f%2fevil.example/image.png',
        'javascript:alert(1)' => '',
        'data:text/html,evil' => '',
        'images/../secret.png' => '',
        'images/%2e%2e/secret.png' => '',
        'images/%252e%252e/secret.png' => '',
        'images/%0dlogo.png' => '',
        'images/flag.png?bad=1' => '',
        'images/flag.png#fragment' => '',
        'images\backslash.png' => '',
        'https://user:secret@cdn.example/flag.png' => '',
    ] as $input => $expected) {
        $value = $safeSource->invoke(null, $input);

        if ($value !== $expected) {
            throw new \RuntimeException('Country flag URI invalid for ' . var_export($input, true));
        }
    }

    // Fully encoded scheme or protocol-relative prefixes cannot be
    // interpreted as local flag filenames, even after nested decoding.
    foreach (['//evil.example/flag.png', 'javascript:alert(1)'] as $unsafePath) {
        $nested = $unsafePath;

        for ($depth = 1; $depth <= 10; ++$depth) {
            $nested = rawurlencode($nested);

            if ($safeSource->invoke(null, $nested) !== '') {
                throw new \RuntimeException('Encoded flag URL scheme accepted at depth ' . $depth);
            }
        }
    }

    // Reject deeply nested percent encoding that becomes ../ only after
    // several URL-decoding passes; allow ordinary encoded image names.
    $encodedTraversal = '../private.png';

    for ($depth = 1; $depth <= 10; ++$depth) {
        $encodedTraversal = rawurlencode($encodedTraversal);

        if ($safeSource->invoke(null, 'images/' . $encodedTraversal) !== '') {
            throw new \RuntimeException('Encoded frontend country flag traversal accepted at depth ' . $depth);
        }
    }

    if ($safeSource->invoke(null, 'images/country%20flag.png')
        !== 'https://sports.example/joomla/images/country%20flag.png') {
        throw new \RuntimeException('Valid URL-encoded country flag filename disappeared.');
    }

    // HTML escaping does not sanitize CSS. Allow safe layout attributes
    // but never render arbitrary style, event handlers or token fragments.
    $safeAttrs = new \ReflectionMethod($class, 'safeImageAttributes');
    $filtered = $safeAttrs->invoke(null,
        'width="20" height=\'30\' aria-label="A & B" style="background:url(javascript:alert(1))" onerror="alert(1)" xclass="fake"'
    );

    if ($filtered !== 'width="20" height="30" aria-label="A &amp; B"') {
        throw new \RuntimeException('Country flag attribute allowlist accepted CSS or malformed tokens: ' . $filtered);
    }

    // Attribute escaping is not enough for invalid size values and browser
    // loading hints. Only bounded pixel dimensions and known enums survive.
    $restrictedAttrs = $safeAttrs->invoke(null,
        'width="120" height="64" loading="LAZY" decoding="ASYNC"'
        . ' class="country-flag" aria-label="Flag & country"'
        . ' width="-10" height="999999" loading="javascript:alert(1)"'
        . ' decoding="invalid"'
    );

    if ($restrictedAttrs !== 'width="120" height="64" loading="lazy" decoding="async"'
        . ' class="country-flag" aria-label="Flag &amp; country"') {
        throw new \RuntimeException('Invalid image dimensions or browser hints escaped validation: ' . $restrictedAttrs);
    }

    foreach ([
        'width="0"', 'height="-1"', 'width="4097"', 'height="100%"',
        'width="999999999999999999999"', 'loading="defer"', 'decoding="eager"',
    ] as $invalidAttribute) {
        if ($safeAttrs->invoke(null, $invalidAttribute) !== '') {
            throw new \RuntimeException('Malformed country image attribute accepted: ' . $invalidAttribute);
        }
    }

    // Duplicate HTML attributes may be interpreted inconsistently. Preserve
    // the first valid option and allow a later valid value after an invalid one.
    $unique = $safeAttrs->invoke(null,
        'width="invalid" width="48" WIDTH="99"'
        . ' loading="wrong" loading="lazy" LOADING="eager"'
        . ' title="First" TITLE="Second" aria-label="Country" ARIA-LABEL="Other"'
    );
    if ($unique !== 'width="48" loading="lazy" title="First" aria-label="Country"') {
        throw new \RuntimeException('Flag attribute duplicates were not normalised: ' . $unique);
    }

    $cache = new \ReflectionProperty($class, 'countries');
    $cache->setValue(null, [
        '0:DEU' => (object) ['name' => 'Germany', 'alpha2' => 'de', 'picture' => ''],
        '0:TST' => (object) ['name' => 'Test', 'alpha2' => 'invalid-code', 'picture' => 'images/test.png'],
    ]);

    $image = $class::flag('DEU', 'class="country-flag" width="20" style="background:url(javascript:alert(1))" onerror="alert(1)" title="A & B"');

    if (!str_contains($image, 'src="https://sports.example/joomla/images/com_sportsmanagement/database/flags/de.png"')
        || !str_contains($image, 'class="country-flag"')
        || !str_contains($image, 'title="A &amp; B"')
        || substr_count($image, ' title="') !== 1
        || str_contains($image, 'onerror')
        || str_contains($image, 'style=')
        || str_contains($image, 'javascript:')
        || !str_contains($image, 'alt="Germany"')) {
        throw new \RuntimeException('Country flag HTML attributes or image source are unsafe: ' . $image);
    }

    $image = $class::flag('TST');

    if (!str_contains($image, 'src="https://sports.example/joomla/images/test.png"')
        || str_contains($image, 'invalid-code')) {
        throw new \RuntimeException('Invalid ISO alpha2 code was used as a CSS or image path.');
    }

    // Old country rows can carry nested alpha2/name/picture fields.
    // Treat warnings as failures, and never render invalid stored paths.
    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $cache->setValue(null, [
            '0:BAD' => (object) [
                'name' => ['invalid'],
                'alpha2' => ['invalid'],
                'picture' => ['invalid'],
            ],
        ]);

        if ($class::flag('BAD') !== '' || $class::name('BAD') !== '') {
            throw new \RuntimeException('Malformed frontend country row rendered invalid output.');
        }
    } finally {
        restore_error_handler();
    }

    // Rehydrate the TST entry after the malformed-data fixture reset, so
    // CSS mode checks a real country record rather than a missing cache key.
    $cache->setValue(null, [
        '0:TST' => (object) ['name' => 'Test', 'alpha2' => 'invalid-code', 'picture' => 'images/test.png'],
    ]);
    // Incorrectly typed configuration must not switch into CSS mode.
    // Keep the valid image fallback for country data without an alpha2 code.
    foreach ([['bad'], new \stdClass(), '1junk', 2] as $invalidCssMode) {
        \Joomla\CMS\Component\ComponentHelper::$css = $invalidCssMode;

        $image = $class::flag('TST');
        if (!str_contains($image, 'src="https://sports.example/joomla/images/test.png"')
            || str_contains($image, 'class="fi ')) {
            throw new \RuntimeException('Invalid flag configuration activated CSS mode.');
        }
    }

    // Both a Joomla integer setting and a numeric-string setting enable CSS.
    $cache->setValue(null, [
        '0:DEU' => (object) ['name' => 'Germany', 'alpha2' => 'de', 'picture' => ''],
        '0:TST' => (object) ['name' => 'Test', 'alpha2' => 'invalid-code', 'picture' => 'images/test.png'],
    ]);
    foreach ([1, '1', true] as $validCssMode) {
        \Joomla\CMS\Component\ComponentHelper::$css = $validCssMode;

        if ($class::flag('DEU') !== '<span class="fi fi-de"></span>') {
            throw new \RuntimeException('Valid flag CSS mode failed for an ISO country.');
        }
    }

    \Joomla\CMS\Component\ComponentHelper::$css = 1;
    $image = $class::flag('TST');

    if ($image !== '') {
        throw new \RuntimeException('Invalid flag CSS country code was rendered.');
    }

    echo "Joomla 5/6 country flag path and HTML safety passed.\n";
}
