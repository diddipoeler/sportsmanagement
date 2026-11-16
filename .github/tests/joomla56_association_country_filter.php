<?php
/**
 * Regression checks for the native Joomla 5/6 association-country dropdown.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType {
        public const STRING = 'string';
        public const INTEGER = 'integer';
    }
}

namespace Joomla\CMS\MVC\Factory {
    interface MVCFactoryInterface {}
}

namespace Joomla\CMS\Log {
    final class Log {
        public const WARNING = 3;
        public static array $messages = [];
        /**
         * Capture safe country filter errors without displaying SQL details.
         *
         * @param string $message Logged diagnostic.
         * @param int $level Joomla severity.
         * @param string $category Component category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void {
            self::$messages[] = [$message, $level, $category];
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text {
        /**
         * Return a stable text label for the isolated template rendering test.
         *
         * @param string $key Translation key.
         * @return string Unmodified translation key.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\HTML {
    final class HTMLHelper {
        /**
         * Stub multiselect, token and list heading calls in the HTML layout.
         *
         * @param string $name Joomla HTML helper selector.
         * @return string HTML fragment for the requested helper.
         */
        public static function _(string $name): string {
            return $name === 'form.token' ? '<input name="mock_token" value="1">' : '';
        }
    }
}

namespace Joomla\CMS\Router {
    final class Route {
        /**
         * Return the native route unchanged for deterministic markup checks.
         *
         * @param string $uri Local administrator URL.
         * @return string Same URI.
         */
        public static function _(string $uri): string { return $uri; }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri {
        /**
         * Provide a harmless installation base for the administrator layout.
         *
         * @return string Test site root.
         */
        public static function root(): string { return 'https://example.test/'; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Helper {
    final class CountryOptionsHelper {
        public static array $registered = [];
        /**
         * Simulate translated countries returned by the already tested helper.
         *
         * @param \Joomla\Database\DatabaseInterface $db Joomla database.
         * @return array Registered option objects.
         */
        public static function getOptions(\Joomla\Database\DatabaseInterface $db): array {
            return self::$registered;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementListModel {
        public array $stateValues = [];

        /**
         * Return selected country or parent filter state for the model tests.
         *
         * @param string $name Joomla state variable name.
         * @param mixed $default Default when no value is set.
         * @return mixed Stored state value.
         */
        public function getState(string $name, mixed $default = null): mixed {
            return $this->stateValues[$name] ?? $default;
        }

        /**
         * Permit the native association model to initialise its list fields.
         *
         * @param array $config Joomla model configuration.
         * @param \Joomla\CMS\MVC\Factory\MVCFactoryInterface|null $factory MVC factory.
         */
        public function __construct($config = [], ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null) {}
        /**
         * Return the configured isolated database double.
         *
         * @return \Joomla\Database\DatabaseInterface Test database connection.
         */
        protected function getDatabase(): \Joomla\Database\DatabaseInterface {
            return $GLOBALS['associationFilterDatabase'];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Model/JlextassociationsModel.php';

    /**
     * Stop the regression on the first violated country filter invariant.
     *
     * @param bool $condition Assertion state.
     * @param string $message Descriptive failure.
     * @return void
     */
    function associationFilterAssert(bool $condition, string $message): void {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    final class AssociationCountryQuery {
        public array $selected = [];
        public string $table = '';
        public array $conditions = [];
        public array $orders = [];

        /**
         * Record DISTINCT SQL expression for the country codes query.
         *
         * @param mixed $fields Requested columns.
         * @return self Fluent query.
         */
        public function select(mixed $fields): self {
            $this->selected[] = $fields;
            return $this;
        }
        /**
         * Record the country-bearing source table.
         *
         * @param string $table Table to read.
         * @return self Fluent query.
         */
        public function from(string $table): self {
            $this->table = $table;
            return $this;
        }
        /**
         * Collect optional null-country query conditions.
         *
         * @param string $condition SQL condition.
         * @return self Fluent query.
         */
        public function where(string $condition): self {
            $this->conditions[] = $condition;
            return $this;
        }
        /**
         * Record country sort ordering for the view.
         *
         * @param string $field Ordering column.
         * @return self Fluent query.
         */
        public function order(string $field): self {
            $this->orders[] = $field;
            return $this;
        }
    }

    final class AssociationCountryDatabase implements \Joomla\Database\DatabaseInterface {
        public array $associationCountries = [];
        public array $parentRows = [];
        public bool $throwOnRead = false;
        public bool $throwOnParentRead = false;
        public int $reads = 0;
        public ?AssociationCountryQuery $query = null;

        /**
         * Build an independent query each time the country dropdown is loaded.
         *
         * @return AssociationCountryQuery Fluent fake query.
         */
        public function createQuery(): AssociationCountryQuery { return new AssociationCountryQuery(); }
        /**
         * Return a quoted SQL identifier in the standalone test double.
         *
         * @param string $identifier Database column or table identifier.
         * @return string Unmodified identifier for query assertions.
         */
        public function quoteName(string $identifier): string { return $identifier; }
        /**
         * Capture the actual table and DISTINCT expression chosen by the model.
         *
         * @param AssociationCountryQuery $query Model's completed query.
         * @return self Current test database.
         */
        public function setQuery(AssociationCountryQuery $query): self {
            $this->query = $query;
            return $this;
        }
        /**
         * Return installed association country values or emulate an SQL fault.
         *
         * @return array Country values from the association table.
         */
        public function loadColumn(): array {
            ++$this->reads;
            if ($this->throwOnRead) {
                throw new \RuntimeException('private_sql_password_should_not_be_rendered');
            }
            return $this->associationCountries;
        }
        /**
         * Supply a realistic association hierarchy for parent dropdown tests.
         *
         * @return array<int,mixed> Association data including malformed rows.
         */
        public function loadObjectList(): array {
            if ($this->throwOnParentRead) {
                throw new \RuntimeException('private_sql_password_should_not_be_rendered');
            }
            return $this->parentRows;
        }
    }

    $db = new AssociationCountryDatabase();
    $GLOBALS['associationFilterDatabase'] = $db;
    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\JlextassociationsModel();
    $helper = \Diddipoeler\Component\SportsManagement\Administrator\Helper\CountryOptionsHelper::class;

    $helper::$registered = [
        (object) ['value' => 'DEU', 'text' => 'Deutschland & Europa'],
        (object) ['value' => 'FRA', 'text' => 'Frankreich <script>alert(1)</script>'],
        (object) ['value' => 'DEU', 'text' => 'Duplicate country'],
        (object) ['value' => '../', 'text' => 'Invalid country code'],
        (object) ['value' => ['array'], 'text' => 'Nested malformed value'],
    ];
    $db->associationCountries = ['DEU', 'fra', 'AUT', 'CAN', 'xx', null, [], 'CAN'];
    $options = $model->getCountryOptions();
    associationFilterAssert(
        array_map(static fn (object $option): string => $option->value, $options)
            === ['AUT', 'CAN', 'DEU', 'FRA'],
        'Filter should show sorted, distinct and validated country codes.'
    );
    associationFilterAssert($options[2]->text === 'Deutschland & Europa'
        && $options[3]->text === 'Frankreich <script>alert(1)</script>',
        'Registered country names should be used ahead of ISO-code fallbacks.');
    associationFilterAssert(
        $db->query !== null
        && $db->query->table === '#__sportsmanagement_associations'
        && $db->query->selected === ['DISTINCT country'],
        'Association country fallback must use the active association database.'
    );

    // Even if no countries are registered, already-installed association
    // country codes must remain available for selection.
    $helper::$registered = [];
    $db->associationCountries = ['FRA', 'DEU'];
    $options = $model->getCountryOptions();
    associationFilterAssert(count($options) === 2
        && $options[0]->value === 'DEU' && $options[1]->value === 'FRA',
        'Country filter disappeared when the separate countries table was empty.');

    // A faulty association lookup keeps registered countries available and
    // does not expose the underlying SQL exception to the administrator.
    $helper::$registered = [(object) ['value' => 'AUT', 'text' => 'Österreich']];
    $db->throwOnRead = true;
    $options = $model->getCountryOptions();
    associationFilterAssert(count($options) === 1 && $options[0]->value === 'AUT',
        'SQL failure discarded valid registered country choices.');
    associationFilterAssert(count(\Joomla\CMS\Log\Log::$messages) === 1
        && !str_contains(\Joomla\CMS\Log\Log::$messages[0][0], 'private_sql_password')
        && \Joomla\CMS\Log\Log::$messages[0][2] === 'com_sportsmanagement',
        'Backend error must be private and free of SQL secrets.');
    $db->throwOnRead = false;

    // Federation filter must offer real parent associations by name rather
    // than require the administrator to know their numeric database IDs.
    $db->parentRows = [
        (object) ['id' => 10, 'parent_id' => 0, 'name' => 'Westdeutscher & Verein', 'country' => 'DEU'],
        (object) ['id' => 11, 'parent_id' => 10, 'name' => 'Region West', 'country' => 'DEU'],
        (object) ['id' => 12, 'parent_id' => 11, 'name' => 'Bezirk West', 'country' => 'DEU'],
        (object) ['id' => 20, 'parent_id' => 0, 'name' => 'Ligue <France>', 'country' => 'FRA'],
        (object) ['id' => 21, 'parent_id' => 20, 'name' => 'Sous-Ligue', 'country' => 'FRA'],
        (object) ['id' => 22, 'parent_id' => 10, 'name' => 'Cross-country link', 'country' => 'FRA'],
        (object) ['id' => 23, 'parent_id' => 99, 'name' => 'Missing parent', 'country' => 'DEU'],
        (object) ['id' => 'bad', 'parent_id' => 10, 'name' => 'Invalid row', 'country' => 'DEU'],
        (object) ['id' => 24, 'parent_id' => [], 'name' => 'Invalid parent', 'country' => 'DEU'],
    ];
    $allParents = $model->getParentAssociationOptions();
    associationFilterAssert(
        array_map(static fn (object $option): string => $option->value, $allParents)
            === ['10', '11', '20'],
        'The filter must only offer valid distinct parents with child associations.'
    );
    associationFilterAssert($allParents[0]->text === '(DEU) Westdeutscher & Verein'
        && $allParents[2]->text === '(FRA) Ligue <France>',
        'Unfiltered parent options need country prefixes and original names.');
    associationFilterAssert($db->query->table === '#__sportsmanagement_associations'
        && count($db->query->selected[0]) === 4,
        'Parent options must be selected from the actual associations hierarchy.');

    $model->stateValues['filter.search_nation'] = 'FRA';
    $frenchParents = $model->getParentAssociationOptions();
    associationFilterAssert(count($frenchParents) === 1
        && $frenchParents[0]->value === '20'
        && $frenchParents[0]->text === 'Ligue <France>',
        'Selecting France must restrict parent options to French associations.');
    $model->stateValues['filter.search_nation'] = 'DEU';
    associationFilterAssert(count($model->getParentAssociationOptions()) === 2,
        'The German country filter must show only German parents.');
    $model->stateValues['filter.search_nation'] = 'DE/';
    associationFilterAssert($model->getParentAssociationOptions() === [],
        'Malformed country options must not generate parent choices.');
    $model->stateValues['filter.search_nation'] = 'FRA';
    $db->throwOnParentRead = true;
    associationFilterAssert($model->getParentAssociationOptions() === [],
        'Parent association SQL failure must leave an empty, usable dropdown.');
    associationFilterAssert(count(\Joomla\CMS\Log\Log::$messages) === 2
        && !str_contains(\Joomla\CMS\Log\Log::$messages[1][0], 'private_sql_password'),
        'Private SQL information must not leak from the parent association filter.');
    $db->throwOnParentRead = false;

    // Native filter schema must exist where SportsManagementListModel adds
    // its Joomla 5/6 form path, not only at the obsolete models/forms path.
    $form = simplexml_load_file($root . '/admin/forms/filter_jlextassociations.xml');
    associationFilterAssert($form instanceof \SimpleXMLElement,
        'The native association filter form is missing or invalid.');
    $fields = $form->xpath('//fields[@name="filter"]/field[@name="search_nation"]');
    associationFilterAssert(count($fields) === 1
        && (string) $fields[0]['type'] === 'Country'
        && str_contains((string) $form->fields['addfieldprefix'], 'Administrator\\Field'),
        'Joomla 5/6 association country filter field is not registered natively.');
    $parentFields = $form->xpath('//fields[@name="filter"]/field[@name="federation"]');
    associationFilterAssert(count($parentFields) === 1
        && (string) $parentFields[0]['type'] === 'Association',
        'The native form must use an association selection instead of a numeric parent ID.');

    // Render the actual Joomla administrator layout with view-owned options.
    // Quotes and angle brackets must be escaped and the current filter value
    // visibly selected. The country input must be a SELECT, never a text box.
    $view = new class {
        public array $items = [];
        public mixed $pagination = null;
        public array $countryOptions = [];
        public array $parentAssociationOptions = [];
        public object $state;

        /**
         * Include the exact native association template in a view context.
         *
         * @param string $root Repository checkout root.
         * @return string Rendered administrator HTML.
         */
        public function render(string $root): string {
            ob_start();
            include $root . '/admin/tmpl/jlextassociations/default.php';
            return (string) ob_get_clean();
        }
    };
    $view->state = new class {
        /**
         * Simulate selected list filter state for the native view template.
         *
         * @param string $name Requested Joomla list state key.
         * @param mixed $default Fallback state.
         * @return mixed Current selected country or supplied default.
         */
        public function get(string $name, mixed $default = ''): mixed {
            return match ($name) {
                'filter.search_nation' => 'FRA',
                'filter.federation' => 20,
                default => $default,
            };
        }
    };
    $view->countryOptions = [
        (object) ['value' => 'DEU', 'text' => 'Deutschland & Europa'],
        (object) ['value' => 'FRA', 'text' => 'Frankreich <script>alert(1)</script>'],
        (object) ['value' => 'BAD/', 'text' => 'Invalid'],
    ];
    $view->parentAssociationOptions = [
        (object) ['value' => '20', 'text' => 'Ligue & France <script>alert(1)</script>'],
        (object) ['value' => '0', 'text' => 'Invalid zero ID'],
        (object) ['value' => 'INVALID', 'text' => 'Invalid identifier'],
    ];
    $html = $view->render($root);
    associationFilterAssert(str_contains($html,
        '<select class="form-select" name="filter[search_nation]" id="filter_search_nation"'),
        'Association country filter must render a proper select element.');
    associationFilterAssert(str_contains($html, 'value="FRA" selected')
        && str_contains($html, 'Deutschland &amp; Europa')
        && str_contains($html, '&lt;script&gt;alert(1)&lt;/script&gt;')
        && !str_contains($html, '<script>')
        && !str_contains($html, 'value="BAD/"')
        && !str_contains($html, 'type="text" name="filter_search_nation"'),
        'Country filter must select the current country and safely escape options.');
    associationFilterAssert(str_contains($html, '&filter[search_nation]='),
        'Clear-filter link must also reset the selected country.');
    associationFilterAssert(str_contains($html,
        '<select class="form-select" name="filter[federation]" id="filter_federation"')
        && str_contains($html, 'value="20" selected')
        && str_contains($html, 'Ligue &amp; France &lt;script&gt;alert(1)&lt;/script&gt;')
        && !str_contains($html, 'value="INVALID"')
        && !str_contains($html, 'type="number" min="0" name="filter_federation"')
        && str_contains($html, "parent.value='0'")
        && str_contains($html, '&filter[federation]=0'),
        'The selected parent dropdown must be escaped and reset when changing country.');

    echo "Joomla 5/6 association country filter regression checks passed.\n";
}
