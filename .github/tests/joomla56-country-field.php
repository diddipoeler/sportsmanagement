<?php
/**
 * Isolated Joomla 5/6 country field validation regression checks.
 *
 * Tests malformed records and database errors without requiring Joomla or a
 * connected database. Run: php .github/tests/joomla56-country-field.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Language {
    /** Minimal translation facade for standalone country field tests. */
    final class Text
    {
        /**
         * Return an unmodified display label for deterministic assertions.
         *
         * @param string $label Country display name.
         * @return string Original display name.
         */
        public static function _(string $label): string
        {
            return $label;
        }
    }
}

namespace Joomla\CMS\Log {
    /** Capture country query errors without writing Joomla log files. */
    final class Log
    {
        public const WARNING = 3;
        public static array $entries = [];

        /**
         * Capture one server-side warning emitted by the country field.
         *
         * @param string $message Safe diagnostic text.
         * @param int $priority Joomla log severity.
         * @param string $category Log category.
         * @return void
         */
        public static function add(string $message, int $priority, string $category): void
        {
            self::$entries[] = compact('message', 'priority', 'category');
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Field {
    /** Isolated base class providing inherited country select options. */
    class SportsManagementListField
    {
        /**
         * Provide the fake database for the selected country field instance.
         *
         * @return object Database test stub.
         */
        protected function getSportsManagementDatabase(): object
        {
            return $GLOBALS['smCountryFieldDatabase'];
        }

        /**
         * Return one fixed parent option to verify Joomla option merging.
         *
         * @return array<int,object> Placeholder selection option.
         */
        protected function getOptions(): array
        {
            return [(object) ['value' => '', 'text' => '-- Select --']];
        }
    }
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/admin/src/Field/CountryField.php';

    use Diddipoeler\Component\SportsManagement\Administrator\Field\CountryField;
    use Joomla\CMS\Log\Log;

    /** Fluent fake query accepting Joomla country option clauses. */
    final class SmCountryFieldQuery
    {
        /**
         * Accept requested country columns.
         *
         * @param array<int,string> $columns Requested country columns.
         * @return static Fluent query object.
         */
        public function select(array $columns): static { return $this; }

        /**
         * Accept the countries table identifier.
         *
         * @param string $table Table name.
         * @return static Fluent query object.
         */
        public function from(string $table): static { return $this; }

        /**
         * Accept the country name ordering expression.
         *
         * @param string $field Ordered column name.
         * @return static Fluent query object.
         */
        public function order(string $field): static { return $this; }
    }

    /** Fake database returning both valid and malformed country rows. */
    final class SmCountryFieldDatabase
    {
        public bool $fail = false;

        /**
         * Return an empty fluent query used to build country selectors.
         *
         * @return SmCountryFieldQuery Fake SQL query.
         */
        public function createQuery(): SmCountryFieldQuery { return new SmCountryFieldQuery(); }

        /**
         * Quote a column or alias for the fake SQL builder.
         *
         * @param string $name Column or table name.
         * @param string|null $alias Optional output alias.
         * @return string Selected identifier and alias.
         */
        public function quoteName(string $name, ?string $alias = null): string
        {
            return $name . ($alias === null ? '' : ' AS ' . $alias);
        }

        /**
         * Accept the complete fake SQL query before retrieving rows.
         *
         * @param SmCountryFieldQuery $query Completed country query.
         * @return static Fake DB instance.
         */
        public function setQuery(SmCountryFieldQuery $query): static { return $this; }

        /**
         * Return typical and corrupted country records for safety assertions.
         *
         * @return array<int,mixed> Mixed country rows.
         */
        public function loadObjectList(): array
        {
            if ($this->fail) {
                throw new \RuntimeException('Simulated country database failure');
            }

            return [
                (object) ['value' => 'deu', 'text' => 'Germany'],
                (object) ['value' => 'FRA', 'text' => ['unexpected nested label']],
                (object) ['value' => 'ES', 'text' => 'Invalid code'],
                (object) ['value' => ['nested country code'], 'text' => 'Wrong'],
                'unexpected scalar',
            ];
        }
    }

    $db = new SmCountryFieldDatabase();
    $GLOBALS['smCountryFieldDatabase'] = $db;
    $field = new CountryField();
    $method = new \ReflectionMethod(CountryField::class, 'getOptions');
    $options = $method->invoke($field);

    if (count($options) !== 3
        || $options[0]->value !== ''
        || $options[1]->value !== 'DEU'
        || $options[1]->text !== 'Germany'
        || $options[2]->value !== 'FRA'
        || $options[2]->text !== 'FRA') {
        throw new \RuntimeException('Country field failed to filter malformed records.');
    }

    $db->fail = true;
    $options = $method->invoke($field);

    if (count($options) !== 1 || count(Log::$entries) !== 1
        || Log::$entries[0]['category'] !== 'com_sportsmanagement') {
        throw new \RuntimeException('Country field must retain parent options on DB failure.');
    }

    echo "Joomla 5/6 country field regression checks passed.\n";
}
