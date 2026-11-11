<?php
/**
 * Joomla 5/6 administrator country options and safe flag regressions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        public static bool $css = false;

        /**
         * Return the Joomla component country display configuration.
         * @param string $component Requested component identifier.
         * @return object Test Registry supporting the get() method.
         */
        public static function getParams(string $component): object
        {
            return new class {
                /**
                 * Resolve CSS flag options without loading the CMS configuration.
                 * @param string $key Component parameter name.
                 * @param mixed $default Fallback for unconfigured keys.
                 * @return mixed Configured CSS flag toggle or default.
                 */
                public function get(string $key, mixed $default = null): mixed
                {
                    return $key === 'cfg_flags_css' ? (int) ComponentHelper::$css : $default;
                }
            };
        }
    }
}

namespace Joomla\CMS\HTML {
    final class HTMLHelper
    {
        /**
         * Return a predictable HTML option or untranslated locale key.
         * @return mixed Test option record or original translation key.
         * @param string $task HTML select task.
         * @param string $value Option value.
         * @param string $label Option label.
         */
        public static function _(string $task, string $value, string $label): object
        {
            return (object) ['value' => $value, 'text' => $label];
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return a predictable HTML option or untranslated locale key.
         * @return mixed Test option record or original translation key.
         * @param string $key Translation identifier.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        /**
         * Provide an HTTPS installation root for country flag URL tests.
         * @return string Site root including its installation subdirectory.
         */
        public static function root(): string
        {
            return 'https://sports.example/joomla/';
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $errors = [];

        /**
         * Collect country database errors without writing to the Joomla log.
         * @param string $message Log diagnostic supplied by the helper.
         * @param int $level Joomla log severity.
         * @param string $category Joomla component log category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$errors[] = [$message, $level, $category];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Helper/CountryOptionsHelper.php';

    final class CountryQuery
    {
        /**
         * Accept requested database columns for the simulated country query.
         * @param mixed $v Requested SQL columns.
         * @return self Fluent test query object.
         */
        public function select(mixed $v): self { return $this; }
        /**
         * Accept the source country table for the simulated database query.
         * @param mixed $v SQL source table expression.
         * @return self Fluent test query object.
         */
        public function from(mixed $v): self { return $this; }
        /**
         * Accept a filter expression for the simulated country query.
         * @param mixed $v SQL condition expression.
         * @return self Fluent test query object.
         */
        public function where(mixed $v): self { return $this; }
    }

    final class CountryDatabase implements \Joomla\Database\DatabaseInterface
    {
        public int $queries = 0;
        public bool $throw = false;
        public mixed $result = 'DEU';
        public array $countries = [
            ['alpha3' => 'DEU', 'name' => 'Germany'],
            ['alpha3' => 'BAD<script>', 'name' => 'Invalid'],
            ['alpha3' => 'FRA', 'name' => 'France'],
        ];
        public ?object $country = null;

        /**
         * Create a fluent country query and simulate configured failures.
         * @return CountryQuery New query fixture, unless failure is enabled.
         * @throws \\RuntimeException When the fixture simulates SQL failure.
         */
        public function createQuery(): CountryQuery
        {
            ++$this->queries;

            if ($this->throw) {
                throw new \RuntimeException('SQLSTATE private password=secret');
            }

            return new CountryQuery();
        }

        /**
         * Return a field identifier without database-specific quoting.
         * @param string $name Country table or field name.
         * @return string Unmodified field identifier.
         */
        public function quoteName(string $name): string { return $name; }
        /**
         * Wrap fixture string values in SQL quote characters.
         * @param string $s Country code to quote.
         * @return string Quoted fixture value.
         */
        public function quote(string $s): string { return "'" . $s . "'"; }
        /**
         * Accept a simulated query without executing a real database statement.
         * @param object $query Country query fixture.
         * @param int $offset First requested result row.
         * @param int $limit Maximum number of rows.
         * @return void
         */
        public function setQuery(object $query, int $offset = 0, int $limit = 0): void {}
        /**
         * Return stored country fixture rows for option sorting checks.
         * @return array Country rows with valid and invalid ISO codes.
         */
        public function loadAssocList(): array { return $this->countries; }
        /**
         * Return the configured ISO-3 lookup result for conversion checks.
         * @return mixed Scalar or malformed result fixture.
         */
        public function loadResult(): mixed { return $this->result; }
        /**
         * Return the configured country flag record, if present.
         * @return object|null Flag data fixture, or null.
         */
        public function loadObject(): ?object { return $this->country; }
    }

    $db = new CountryDatabase();
    $helper = \Diddipoeler\Component\SportsManagement\Administrator\Helper\CountryOptionsHelper::class;
    $safeUrl = new \ReflectionMethod($helper, 'imageUrl');

    foreach ([
        'images/flags/de.png' => 'https://sports.example/joomla/images/flags/de.png',
        '/images/flags/de.png' => 'https://sports.example/joomla/images/flags/de.png',
        'https://cdn.example/de.png' => 'https://cdn.example/de.png',
        '//evil.example/de.png' => '',
        'javascript:alert(1)' => '',
        'data:image/svg+xml,evil' => '',
        'images/../secret.png' => '',
        'images/%2e%2e/secret.png' => '',
        'images/%252e%252e/secret.png' => '',
        'images/%0dsecret.png' => '',
        'images/flag.png?cache=1' => '',
        'images\backslash.png' => '',
        'https://user:secret@cdn.example/de.png' => '',
    ] as $raw => $expected) {
        if ($safeUrl->invoke(null, $raw) !== $expected) {
            throw new \RuntimeException('Unsafe administrator country flag URL: ' . var_export($raw, true));
        }
    }

    $options = $helper::getOptions($db);

    if (array_map(static fn (object $o): string => $o->value, $options) !== ['FRA', 'DEU']) {
        throw new \RuntimeException('Administrator country options include malformed ISO codes or sort incorrectly.');
    }

    if ($helper::iso2To3($db, 'de') !== 'DEU') {
        throw new \RuntimeException('Valid alpha-2 to alpha-3 conversion failed.');
    }

    $queries = $db->queries;
    if ($helper::iso2To3($db, 'D_') !== 'DEU' || $helper::getFlag($db, 'B<script>') !== ''
        || $db->queries !== $queries) {
        throw new \RuntimeException('Malformed country codes unexpectedly queried the database.');
    }

    $db->country = (object) ['alpha2' => 'de', 'name' => 'Germany', 'picture' => ''];
    $flag = $helper::getFlag($db, 'DEU');

    if (!str_contains($flag, 'src="https://sports.example/joomla/images/com_sportsmanagement/database/flags/de.png"')) {
        throw new \RuntimeException('Valid administrator country flag disappeared.');
    }

    $db->country = (object) [
        'alpha2' => 'invalid-code',
        'name' => 'Example <country>',
        'picture' => 'javascript:alert(1)',
    ];

    if ($helper::getFlag($db, 'TST') !== '') {
        throw new \RuntimeException('Administrator country flag accepted an executable URI.');
    }

    \Joomla\CMS\Component\ComponentHelper::$css = true;
    if ($helper::getFlag($db, 'TST') !== '') {
        throw new \RuntimeException('Administrator flag CSS accepted an invalid country code.');
    }

    // Broken imported country records and nested component parameters must
    // never emit PHP 8 array-to-string warnings in administrator selectors.
    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $db->result = ['broken-alpha3'];
        if ($helper::iso2To3($db, 'FR') !== 'DEU') {
            throw new \RuntimeException('Malformed ISO-3 country result was accepted.');
        }

        \Joomla\CMS\Component\ComponentHelper::$css = false;
        $db->country = (object) [
            'alpha2' => ['broken'],
            'name' => ['broken'],
            'picture' => ['broken'],
        ];

        if ($helper::getFlag($db, 'FRA') !== '') {
            throw new \RuntimeException('Malformed country flag row produced invalid markup.');
        }

        $db->country = (object) [
            'alpha2' => 'de',
            'name' => ['broken'],
            'picture' => null,
        ];

        $flag = $helper::getFlag($db, 'DEU');
        if (!str_contains($flag, 'alt="DEU"')
            || !str_contains($flag, 'src="https://sports.example/joomla/images/com_sportsmanagement/database/flags/de.png"')) {
            throw new \RuntimeException('Malformed flag label did not use country-code fallback.');
        }
    } finally {
        restore_error_handler();
    }

    $db->throw = true;
    $before = count(\Joomla\CMS\Log\Log::$errors);

    if ($helper::getOptions($db) !== [] || $helper::iso2To3($db, 'FR') !== 'DEU'
        || $helper::getFlag($db, 'FRA') !== ''
        || count(\Joomla\CMS\Log\Log::$errors) !== $before + 3) {
        throw new \RuntimeException('Administrator country queries did not recover from failures.');
    }

    foreach (\Joomla\CMS\Log\Log::$errors as $entry) {
        if (!str_contains($entry[0], 'password=secret') || $entry[2] !== 'com_sportsmanagement') {
            throw new \RuntimeException('Failed country queries were not logged privately.');
        }
    }

    echo "Administrator country query and safe flag regressions passed.\n";
}
