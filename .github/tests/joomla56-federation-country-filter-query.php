<?php
/**
 * Test Joomla native federation filter state and parameter-bound SQL country clause.
 *
 * Run: php .github/tests/joomla56-federation-country-filter-query.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\MVC\Factory {
    /** Minimal native factory interface for the isolated model fixture. */
    interface MVCFactoryInterface
    {
    }
}

namespace Joomla\Database {
    /** Minimal binding type used by Joomla's native query builder. */
    final class ParameterType
    {
        public const STRING = 'string';
        public const INTEGER = 'integer';
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    /**
     * Model base that emulates Joomla ListModel's bracketed filter input.
     *
     * The production model is loaded unchanged and its actual getListQuery
     * implementation builds the query in the test.
     */
    class SportsManagementListModel
    {
        /** @var array<string,mixed> Joomla list state fixture. */
        public array $savedState = [];
        /** @var object Query database fixture. */
        public object $database;

        /**
         * Initialize a fake Joomla-compatible list model.
         *
         * @param array $config List-model settings.
         * @param mixed $factory Optional Joomla factory.
         */
        public function __construct($config = [], $factory = null)
        {
            $this->database = new class {
                /** @var array<int,string> Generated where expressions. */
                public array $conditions = [];
                /** @var array<string,mixed> Bound SQL parameter values. */
                public array $bindings = [];

                /**
                 * Return this fixture as a new query builder.
                 *
                 * @return self SQL builder.
                 */
                public function createQuery(): self
                {
                    $this->conditions = [];
                    $this->bindings = [];

                    return $this;
                }

                /**
                 * Quote an identifier for the fake SQL builder.
                 *
                 * @param string $name SQL identifier.
                 * @param string|null $alias Optional alias.
                 * @return string Quoted SQL column or alias.
                 */
                public function quoteName(string $name, ?string $alias = null): string
                {
                    return $alias === null ? '"' . $name . '"' : '"' . $name . '" AS "' . $alias . '"';
                }

                /**
                 * Accept a SELECT expression.
                 *
                 * @param mixed $expression Selected column set.
                 * @return self Query builder.
                 */
                public function select(mixed $expression): self
                {
                    return $this;
                }

                /**
                 * Accept a FROM expression.
                 *
                 * @param string $table Source table.
                 * @return self Query builder.
                 */
                public function from(string $table): self
                {
                    return $this;
                }

                /**
                 * Accept a JOIN expression.
                 *
                 * @param string $type Join type.
                 * @param string $condition Join expression.
                 * @return self Query builder.
                 */
                public function join(string $type, string $condition): self
                {
                    return $this;
                }

                /**
                 * Escape a Joomla LIKE search fragment for this fake query.
                 *
                 * @param string $value Raw search term.
                 * @param bool $extra Apply extra Joomla LIKE escaping.
                 * @return string Unchanged safe test term.
                 */
                public function escape(string $value, bool $extra = false): string
                {
                    return $value;
                }

                /**
                 * Record each country or state WHERE expression.
                 *
                 * @param string $condition SQL condition.
                 * @return self Query builder.
                 */
                public function where(string $condition): self
                {
                    $this->conditions[] = $condition;

                    return $this;
                }

                /**
                 * Record a bound country value rather than interpolated SQL.
                 *
                 * @param string $key Named SQL placeholder.
                 * @param mixed $value Parameter value.
                 * @param mixed $type Joomla binding type.
                 * @return self Query builder.
                 */
                public function bind(string $key, mixed $value, mixed $type): self
                {
                    $this->bindings[$key] = $value;

                    return $this;
                }

                /**
                 * Accept a sort expression.
                 *
                 * @param string $expression Sort expression.
                 * @return self Query builder.
                 */
                public function order(string $expression): self
                {
                    return $this;
                }
            };
        }

        /**
         * Emulate state populated by Joomla from filter[search_nation].
         *
         * @param string|null $ordering Default column.
         * @param string|null $direction Default direction.
         * @return void
         */
        protected function populateState($ordering = null, $direction = null)
        {
            foreach (($GLOBALS['nativeFederationFilters'] ?? []) as $key => $value) {
                $this->savedState['filter.' . $key] = $value;
            }
        }

        /**
         * Resolve one of the stored native Joomla list state properties.
         *
         * @param string $key State property name.
         * @param mixed $default Default state.
         * @return mixed State value.
         */
        public function getState($key, $default = null): mixed
        {
            return $this->savedState[$key] ?? $default;
        }

        /**
         * Return the same fake database for getListQuery.
         *
         * @return object Database fixture.
         */
        public function getDatabase(): object
        {
            return $this->database;
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextfederationsModel;

    \define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Model/JlextfederationsModel.php';

    $model = new JlextfederationsModel();
    $populate = new \ReflectionMethod(JlextfederationsModel::class, 'populateState');
    $query = new \ReflectionMethod(JlextfederationsModel::class, 'getListQuery');

    $GLOBALS['nativeFederationFilters'] = ['search_nation' => 'DEU'];
    $populate->invoke($model);
    $db = $query->invoke($model);

    if (($db->bindings[':country'] ?? null) !== 'DEU'
        || !in_array('"objassoc.country" = :country', $db->conditions, true)) {
        throw new \RuntimeException('Selected federation country did not reach bound SQL WHERE.');
    }

    $GLOBALS['nativeFederationFilters'] = ['search_nation' => 'FRA'];
    $populate->invoke($model);
    $db = $query->invoke($model);

    if (($db->bindings[':country'] ?? null) !== 'FRA') {
        throw new \RuntimeException('Changed federation country was not applied to SQL.');
    }

    $GLOBALS['nativeFederationFilters'] = ['search_nation' => ''];
    $populate->invoke($model);
    $db = $query->invoke($model);

    if (array_key_exists(':country', $db->bindings)
        || in_array('"objassoc.country" = :country', $db->conditions, true)) {
        throw new \RuntimeException('Clearing federation country did not remove SQL filter.');
    }

    // Invalid nested values must never cause PHP 8 array-to-string warnings
    // or produce an unintended SQL country, search or publication predicate.
    foreach ([
        ['search_nation' => ['DEU'], 'search' => ['malformed'], 'state' => ['1']],
        ['search_nation' => "DEU\n", 'search' => "unsafe\0", 'state' => '1trailing'],
        ['search_nation' => 'DEUtrailing', 'search' => str_repeat('x', 256), 'state' => '2'],
    ] as $invalidFilters) {
        $GLOBALS['nativeFederationFilters'] = $invalidFilters;
        $populate->invoke($model);
        $db = $query->invoke($model);

        if (isset($db->bindings[':country']) || isset($db->bindings[':searchToken'])
            || isset($db->bindings[':publishedState'])
            || in_array('"objassoc.country" = :country', $db->conditions, true)) {
            throw new \RuntimeException('Malformed native federation filters reached SQL.');
        }
    }

    $GLOBALS['nativeFederationFilters'] = [
        'search_nation' => 'deu',
        'search' => 'Schalke',
        'state' => '0',
    ];
    $populate->invoke($model);
    $db = $query->invoke($model);

    if (($db->bindings[':country'] ?? null) !== 'DEU'
        || ($db->bindings[':searchToken'] ?? null) !== '%Schalke%'
        || ($db->bindings[':publishedState'] ?? null) !== 0) {
        throw new \RuntimeException('Valid combined federation filters did not reach SQL.');
    }

    echo "Joomla 5/6 federation country SQL filter regression passed.\n";
}
