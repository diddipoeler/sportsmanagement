<?php
/**
 * Joomla 5/6 regressions for administrator countries and site legacy loader.
 *
 * Usage: php .github/tests/joomla56-countries-legacy-loader.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    /** Minimal native database contract for country list regression tests. */
    interface DatabaseInterface
    {
        public function createQuery(): object;
        public function quoteName(string $name): string;
        public function setQuery(object $query): static;
        public function loadAssocList(): array;
    }
}

namespace Joomla\CMS {
    final class Factory
    {
        public static \Joomla\Database\DatabaseInterface $database;

        /**
         * Provide the Joomla container fixture to the legacy country facade.
         * @return object Container exposing the configured test database.
         */
        public static function getContainer(): object
        {
            return new class {
                /**
                 * Return the isolated database instead of a live driver.
                 * @param string $service Requested service identifier.
                 * @return \Joomla\Database\DatabaseInterface Configured fixture.
                 */
                public function get(string $service): \Joomla\Database\DatabaseInterface
                {
                    return Factory::$database;
                }
            };
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        /**
         * Preserve the fixture database for country lookups.
         * @param \Joomla\Database\DatabaseInterface $fallback Default database.
         * @return \Joomla\Database\DatabaseInterface Same isolated connection.
         */
        public static function resolve(\Joomla\Database\DatabaseInterface $fallback): \Joomla\Database\DatabaseInterface
        {
            return $fallback;
        }
    }
}

namespace Joomla\CMS\HTML {
    /** Minimal HTML option factory for a country selector. */
    final class HTMLHelper
    {
        /**
         * Create one option record as Joomla's select helper would.
         *
         * @param string $method HTML helper operation.
         * @param string $code ISO alpha-3 value.
         * @param string $name Translated country display name.
         * @param string $valueKey Option value property.
         * @param string $textKey Option label property.
         * @return object Selection record.
         */
        public static function _(string $method, string $code, string $name, string $valueKey = 'value', string $textKey = 'text'): object
        {
            return (object) [$valueKey => $code, $textKey => $name];
        }
    }
}

namespace Joomla\CMS\Language {
    /** Language facade returning source labels unchanged. */
    final class Text
    {
        /**
         * Return a country label without loading Joomla translations.
         *
         * @param string $key Country label or translation identifier.
         * @return string Same label for deterministic assertions.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);
    \defined('JPATH_ADMINISTRATOR') || \define('JPATH_ADMINISTRATOR', '/nonexistent/administrator');
    \defined('JPATH_SITE') || \define('JPATH_SITE', '/nonexistent/site');

    require_once dirname(__DIR__, 2) . '/admin/src/Helper/CountryOptionsHelper.php';
    require_once dirname(__DIR__, 2) . '/site/src/Service/LegacyPresentationLoader.php';

    use Diddipoeler\Component\SportsManagement\Administrator\Helper\CountryOptionsHelper;
    use Diddipoeler\Component\SportsManagement\Site\Service\LegacyPresentationLoader;

    /** Fluent fake query used to isolate country select logic. */
    final class SmCountryQuery
    {
        /**
         * Accept selected columns for a country query without executing SQL.
         *
         * @param array<string>|string $fields Requested columns.
         * @return static Query for fluent calls.
         */
        public function select(array|string $fields): static { return $this; }

        /**
         * Accept the country table name for an isolated test.
         *
         * @param string $table Selected table.
         * @return static Query for fluent calls.
         */
        public function from(string $table): static { return $this; }
    }

    /** Fake database returning malformed and valid legacy country rows. */
    final class SmCountryDatabase implements \Joomla\Database\DatabaseInterface
    {
        /**
         * Create a stub Joomla query object for the country list lookup.
         *
         * @return object Fluent test query.
         */
        public function createQuery(): object { return new SmCountryQuery(); }

        /**
         * Return column names without adding driver-specific quoting.
         *
         * @param string $name Country table or column identifier.
         * @return string Input identifier.
         */
        public function quoteName(string $name): string { return $name; }

        /**
         * Accept the query without connecting to a real database.
         *
         * @param object $query Query generated by the admin helper.
         * @return static Fake driver.
         */
        public function setQuery(object $query): static { return $this; }

        /**
         * Return country rows containing malformed records to skip safely.
         *
         * @return array<int,mixed> Synthetic country records.
         */
        public function loadAssocList(): array
        {
            return [
                ['alpha3' => 'DEU', 'name' => 'Germany'],
                ['alpha3' => ['BAD'], 'name' => 'Broken country'],
                ['alpha3' => 'FRA', 'name' => ['Broken label']],
                'unexpected scalar row',
                ['alpha3' => 'ESP', 'name' => 'Spain'],
            ];
        }
    }

    $options = CountryOptionsHelper::getOptions(new SmCountryDatabase());
    /**
     * Extract the default option value for order assertions.
     * @param object $entry Country option.
     * @return string ISO country identifier.
     */
    $codes = array_map(static fn (object $entry): string => $entry->value, $options);

    if ($codes !== ['FRA', 'DEU', 'ESP']) {
        throw new \RuntimeException('Country options were not filtered and sorted by name.');
    }

    if ($options[0]->text !== 'FRA') {
        throw new \RuntimeException('Malformed country labels must fall back to the ISO code.');
    }

    \Joomla\CMS\Factory::$database = new SmCountryDatabase();
    require_once dirname(__DIR__, 2) . '/site/helpers/countries.php';
    $legacyOptions = \JSMCountries::getCountryOptions('code', 'label');

    if (count($legacyOptions) !== 3 || $legacyOptions[0]->code !== 'FRA'
        || $legacyOptions[0]->label !== 'FRA' || $legacyOptions[1]->code !== 'DEU'
        || $legacyOptions[2]->code !== 'ESP') {
        throw new \RuntimeException('Legacy country custom option keys or malformed row handling regressed.');
    }

    if (\JSMCountries::getIso2Flag('DE') !== 'images/com_sportsmanagement/database/flags/de.png'
        || \JSMCountries::getIso2Flag('../private') !== null
        || \JSMCountries::getIso2Flag(['de']) !== null) {
        throw new \RuntimeException('Malformed legacy flag identifier accepted.');
    }

    $before = count(spl_autoload_functions() ?: []);
    LegacyPresentationLoader::register();
    $afterFirst = count(spl_autoload_functions() ?: []);
    LegacyPresentationLoader::register();
    $afterSecond = count(spl_autoload_functions() ?: []);

    if ($afterFirst !== $before + 1 || $afterSecond !== $afterFirst) {
        throw new \RuntimeException('Legacy presentation autoloader was registered more than once.');
    }

    echo "Joomla 5/6 country options and legacy autoloader regressions passed.\n";
}
