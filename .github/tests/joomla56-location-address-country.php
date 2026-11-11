<?php
/**
 * Joomla 5/6 location address and country record regression test.
 *
 * Run without a Joomla bootstrap and treat PHP warnings as test failures.
 *
 * Usage: php .github/tests/joomla56-location-address-country.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Mark country names as translated in the standalone site fixture.
         *
         * @param string $key Country translation key.
         * @return string Marked translation result.
         */
        public static function _(string $key): string
        {
            return 'Translated: ' . $key;
        }
    }
}

namespace {
    \define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/site/src/Helper/LocationAddressHelper.php';

    final class LocationQueryFixture
    {
        /**
         * Accept the selected country-name field.
         *
         * @param string $field Requested SQL field.
         * @return self Fluent query fixture.
         */
        public function select(string $field): self { return $this; }

        /**
         * Accept the countries source table.
         *
         * @param string $table SQL table identifier.
         * @return self Fluent query fixture.
         */
        public function from(string $table): self { return $this; }

        /**
         * Accept the validated country-code lookup expression.
         *
         * @param string $expression WHERE condition.
         * @return self Fluent query fixture.
         */
        public function where(string $expression): self { return $this; }
    }

    final class LocationDatabaseFixture implements \Joomla\Database\DatabaseInterface
    {
        public int $queries = 0;
        public mixed $result = 'Germany';

        /**
         * Create a fake Joomla 5/6 query without legacy getQuery().
         *
         * @return LocationQueryFixture Fluent query fixture.
         */
        public function createQuery(): LocationQueryFixture
        {
            ++$this->queries;
            return new LocationQueryFixture();
        }

        /**
         * Return the selected table or column unchanged.
         *
         * @param string $value Raw identifier.
         * @return string Identifier used by the fixture.
         */
        public function quoteName(string $value): string { return $value; }

        /**
         * Quote a validated country-code SQL value.
         *
         * @param string $value Three-letter country code.
         * @return string Quoted ISO value.
         */
        public function quote(string $value): string { return "'" . $value . "'"; }

        /**
         * Accept a prepared lookup without connecting to a database.
         *
         * @param LocationQueryFixture $query Country query fixture.
         * @param int $offset First result row.
         * @param int $limit Maximum rows.
         * @return void
         */
        public function setQuery(LocationQueryFixture $query, int $offset = 0, int $limit = 0): void
        {
        }

        /**
         * Simulate valid and corrupted country name records.
         *
         * @return mixed Configured country name value.
         */
        public function loadResult(): mixed { return $this->result; }
    }

    $db = new LocationDatabaseFixture();
    $helper = \Diddipoeler\Component\SportsManagement\Site\Helper\LocationAddressHelper::class;

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $location = (object) [
            'address' => 'Main Street 5',
            'state' => 'NRW',
            'location' => ['broken'],
            'city' => 'Essen',
            'zipcode' => '45127',
            'country' => 'deu',
        ];

        if ($helper::build($db, $location)
            !== 'Main Street 5, NRW, 45127 Essen, Translated: Germany'
            || $db->queries !== 1) {
            throw new \RuntimeException('Valid location country or fallback city was lost.');
        }

        // Imported addresses must not inject new lines or control bytes,
        // and non-finite numeric fields must never become "INF" or "NAN".
        $dirty = (object) [
            'address' => "Main\x00 Street\n5",
            'state' => "\tNRW\r",
            'location' => "Essen\x7f",
            'zipcode' => INF,
            'country' => '',
        ];

        if ($helper::build($db, $dirty) !== 'Main  Street 5, NRW, Essen') {
            throw new \RuntimeException('Corrupted location fields contained control bytes or INF.');
        }

        $dirty->zipcode = NAN;
        $dirty->address = "\x00\n";
        $dirty->state = '';
        $dirty->location = '';
        $dirty->city = 'Bochum';
        if ($helper::build($db, $dirty) !== 'Bochum') {
            throw new \RuntimeException('Non-finite address number or blank control text survived.');
        }

        $db->result = ['broken-country-name'];

        if ($helper::build($db, $location) !== 'Main Street 5, NRW, 45127 Essen, DEU'
            || $db->queries !== 2) {
            throw new \RuntimeException('Malformed country result caused PHP warnings or invalid output.');
        }

        $location->country = 'DEU;DROP';
        if ($helper::build($db, $location) !== 'Main Street 5, NRW, 45127 Essen, DEU;DROP'
            || $db->queries !== 2) {
            throw new \RuntimeException('Malformed country identifier unexpectedly queried the database.');
        }

        $location->country = ['invalid'];
        $location->address = new \stdClass();
        $location->state = ['invalid'];
        $location->zipcode = ['invalid'];
        $location->city = 'Bochum';

        if ($helper::build($db, $location) !== 'Bochum' || $db->queries !== 2) {
            throw new \RuntimeException('Malformed address fields were not discarded.');
        }
        // Country labels pass through the same printable-text guard as
        // street and city fields, before and after Joomla translation.
        $countryDb = new LocationDatabaseFixture();
        $countryLocation = (object) ['city' => 'Essen', 'country' => 'DEU'];
        $countryDb->result = "Ger\nmany\x7f";

        if ($helper::build($countryDb, $countryLocation) !== 'Essen, Translated: Ger many') {
            throw new \RuntimeException('Country result control bytes leaked into address.');
        }

        $countryDb->result = "\x00\n";
        if ($helper::build($countryDb, $countryLocation) !== 'Essen, DEU'
            || $countryDb->queries !== 2) {
            throw new \RuntimeException('Control-only country names failed to use ISO fallback.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 location address and country regressions passed.\n";
}
