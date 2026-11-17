<?php
/**
 * Test the native federation country field when legacy country data is incomplete.
 *
 * Run: php .github/tests/joomla56-federation-edit-country-fallback.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Language {
    /** Deterministic Joomla translation test stub. */
    final class Text
    {
        /**
         * Return the untranslated label from the fixture.
         *
         * @param string $label Selected option label.
         * @return string Unchanged label.
         */
        public static function _(string $label): string
        {
            return $label;
        }
    }
}

namespace Joomla\CMS\Log {
    /** No-op logger for testing optional country table failures. */
    final class Log
    {
        public const WARNING = 2;

        /**
         * Accept an expected lookup warning without writing a Joomla log.
         *
         * @param string $message Diagnostic category.
         * @param int $priority Severity level.
         * @param string $category Log category.
         * @return void
         */
        public static function add(string $message, int $priority, string $category): void
        {
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Field {
    /** Replace Joomla ListField with a minimal standalone options provider. */
    class SportsManagementListField
    {
        /**
         * Return the configured fake database.
         *
         * @return object Database fixture.
         */
        protected function getSportsManagementDatabase(): object
        {
            return $GLOBALS['countryFixture'];
        }

        /**
         * Return the inherited empty-country placeholder.
         *
         * @return array<int,object> Placeholder option.
         */
        protected function getOptions(): array
        {
            return [(object) ['value' => '', 'text' => 'Select country']];
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Field\CountryField;

    \define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Field/CountryField.php';

    $GLOBALS['countryFixture'] = new class {
        /** @var string Last table selected by the fake query. */
        public string $table = '';
        /** @var bool Whether the optional country table is unavailable. */
        public bool $missingCountries = true;

        /**
         * Return the database fixture as its own fluent query.
         *
         * @return self Fluent query fixture.
         */
        public function createQuery(): self
        {
            return $this;
        }

        /**
         * Return a quoted identifier without real SQL.
         *
         * @param string $column Column identifier.
         * @param string|null $alias Optional field alias.
         * @return string SQL column expression.
         */
        public function quoteName(string $column, ?string $alias = null): string
        {
            return $alias === null ? $column : $column . ' AS ' . $alias;
        }

        /**
         * Ignore the select expression in this fixture.
         *
         * @param mixed $value Select expression.
         * @return self Fluent query fixture.
         */
        public function select(mixed $value): self
        {
            return $this;
        }

        /**
         * Track the source table being queried.
         *
         * @param string $table Requested table.
         * @return self Fluent query fixture.
         */
        public function from(string $table): self
        {
            $this->table = $table;

            return $this;
        }

        /**
         * Ignore the source filter.
         *
         * @param string $condition SQL predicate.
         * @return self Fluent query fixture.
         */
        public function where(string $condition): self
        {
            return $this;
        }

        /**
         * Ignore query ordering.
         *
         * @param string $order SQL sort.
         * @return self Fluent query fixture.
         */
        public function order(string $order): self
        {
            return $this;
        }

        /**
         * Accept the prepared query.
         *
         * @param self $query Query fixture.
         * @return self Database fixture.
         */
        public function setQuery(self $query): self
        {
            return $this;
        }

        /**
         * Return the country catalogue or simulate a missing installation table.
         *
         * @return array<int,object> Country records.
         */
        public function loadObjectList(): array
        {
            if ($this->missingCountries) {
                throw new \RuntimeException('Country table unavailable on legacy installation.');
            }

            return [
                (object) ['value' => 'DEU', 'text' => 'Deutschland'],
                (object) ['value' => 'FRA', 'text' => 'France'],
                (object) ['value' => "GBR\n", 'text' => 'Invalid code'],
                (object) ['value' => 'ITA', 'text' => "Invalid label\0"],
            ];
        }

        /**
         * Return ISO codes already assigned to federation records.
         *
         * @return array<int,mixed> Stored country codes.
         */
        public function loadColumn(): array
        {
            if ($this->table !== '#__sportsmanagement_federations') {
                throw new \RuntimeException('Incorrect country fallback source.');
            }

            return ['DEU', 'FRA', 'AUT', "CHE\n", ['bad'], 'AUT'];
        }
    };

    $field = (new \ReflectionClass(CountryField::class))->newInstanceWithoutConstructor();
    $getOptions = new \ReflectionMethod(CountryField::class, 'getOptions');

    $options = $getOptions->invoke($field);
    $values = array_column($options, 'value');

    if ($values !== ['', 'DEU', 'FRA', 'AUT']) {
        throw new \RuntimeException('Country field lost saved federation values without a country table.');
    }

    $GLOBALS['countryFixture']->missingCountries = false;
    $options = $getOptions->invoke($field);
    $byCode = [];

    foreach ($options as $option) {
        $byCode[$option->value] = $option->text;
    }

    if (($byCode['DEU'] ?? '') !== 'Deutschland'
        || ($byCode['FRA'] ?? '') !== 'France'
        || ($byCode['ITA'] ?? '') !== 'ITA'
        || ($byCode['AUT'] ?? '') !== 'AUT'
        || isset($byCode['GBR'], $byCode['CHE'])
        || count($options) !== 5) {
        throw new \RuntimeException('Country catalogue, fallbacks or malformed options were mishandled.');
    }

    echo "Joomla 5/6 federation edit country fallback passed.\n";
}
