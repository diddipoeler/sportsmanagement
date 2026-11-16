<?php
/**
 * Test Joomla 5/6 Landesverband parent field and association hierarchy writes.
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
        public const INTEGER = 'integer';
        public const STRING = 'string';
    }
}

namespace Joomla\CMS\Application {
    final class AdministratorApplication {
        public object $input;
        /**
         * Expose the current edit form request to the mocked Joomla field.
         *
         * @return object Input request fixture.
         */
        public function getInput(): object { return $this->input; }
    }
}

namespace Joomla\CMS\Factory {
    // Kept empty; the fixture below declares Joomla\CMS\Factory as a class.
}

namespace Joomla\CMS {
    final class Factory {
        /**
         * Provide the isolated Joomla application service container.
         *
         * @return object Test container.
         */
        public static function getContainer(): object {
            return $GLOBALS['associationEditContainer'];
        }
    }
}

namespace Joomla\CMS\Filter {
    final class OutputFilter {
        /**
         * Convert test association names to deterministic Joomla-style aliases.
         *
         * @param string $name Association name.
         * @return string Simple URL-safe alias.
         */
        public static function stringURLSafe(string $name): string {
            return trim(strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $name)), '-');
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text {
        /**
         * Return the validation language key for simple test assertions.
         *
         * @param string $key Translation key.
         * @return string Untranslated key.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\Log {
    final class Log {
        public const ERROR = 1;
        public static array $entries = [];
        /**
         * Capture private SQL validation diagnostics.
         *
         * @param string $message Safe diagnostic.
         * @param int $severity Log severity.
         * @param string $category Joomla component.
         * @return void
         */
        public static function add(string $message, int $severity, string $category): void {
            self::$entries[] = [$message, $severity, $category];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Field {
    class SportsManagementListField {
        public array $element = ['targettable' => 'associations'];
        /**
         * Preserve the no-parent entry supplied through Joomla form XML.
         *
         * @return array<int,object> Inherited select options.
         */
        protected function getOptions(): array {
            return [(object) ['value' => '0', 'text' => '-- No parent --']];
        }
        /**
         * Return the same SportsManagement association database as the table.
         *
         * @return \Joomla\Database\DatabaseInterface Fake association database.
         */
        protected function getSportsManagementDatabase(): \Joomla\Database\DatabaseInterface {
            return $GLOBALS['associationEditDatabase'];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Table {
    class SportsManagementTable {
        public int $id = 0;
        public mixed $parent_id = 0;
        public mixed $country = '';
        public string $name = '';
        public string $alias = '';
        public array $errors = [];
        private \Joomla\Database\DatabaseInterface $database;

        /**
         * Keep the tested table connected to its injected component database.
         *
         * @param string $table Joomla association SQL table.
         * @param string $key Primary key.
         * @param \Joomla\Database\DatabaseInterface $db Database double.
         */
        public function __construct(string $table, string $key, \Joomla\Database\DatabaseInterface $db) {
            $this->database = $db;
        }
        /**
         * Expose the Joomla table's current database for parent-chain queries.
         *
         * @return \Joomla\Database\DatabaseInterface Active database.
         */
        public function getDatabase(): \Joomla\Database\DatabaseInterface { return $this->database; }
        /**
         * Preserve a safe table validation error for the administrator form.
         *
         * @param string $error Joomla language key describing invalid parent.
         * @return void
         */
        public function setError(string $error): void { $this->errors[] = $error; }
    }
}

namespace {
    \define('_JEXEC', 1);

    /**
     * Assert that the edit field and table preserve parent hierarchy rules.
     *
     * @param bool $valid Whether the tested invariant passed.
     * @param string $message Diagnostic for failing CI regressions.
     * @return void
     */
    function associationEditAssert(bool $valid, string $message): void {
        if (!$valid) {
            throw new \RuntimeException($message);
        }
    }

    final class AssociationEditQuery {
        public array $columns = [];
        public string $table = '';
        public array $bindings = [];
        /**
         * Collect columns selected by a Joomla form field or parent validator.
         *
         * @param array|string $columns SQL columns.
         * @return self Current fluent query.
         */
        public function select(array|string $columns): self { $this->columns = (array) $columns; return $this; }
        /**
         * Retain the table expression used to load associations.
         *
         * @param string $table SQL table identifier.
         * @return self Current fluent query.
         */
        public function from(string $table): self { $this->table = $table; return $this; }
        /**
         * Accept the selector's SQL WHERE clause.
         *
         * @param string $condition SQL condition.
         * @return self Current fluent query.
         */
        public function where(string $condition): self { return $this; }
        /**
         * Accept a country or parent association sorting expression.
         *
         * @param string|array $order SQL sorting columns.
         * @return self Current fluent query.
         */
        public function order(string|array $order): self { return $this; }
        /**
         * Record parameter-bound country and association IDs.
         *
         * @param string $name SQL named placeholder.
         * @param mixed $value Bound value.
         * @param mixed $type Database parameter type.
         * @return self Current fluent query.
         */
        public function bind(string $name, mixed $value, mixed $type): self {
            $this->bindings[$name] = [$value, $type];
            return $this;
        }
    }

    final class AssociationEditDatabase implements \Joomla\Database\DatabaseInterface {
        public array $records = [];
        public ?AssociationEditQuery $query = null;
        public bool $fail = false;
        public array $readParentIds = [];
        /**
         * Return an independent Joomla-compatible query fixture.
         *
         * @return AssociationEditQuery Empty test query builder.
         */
        public function createQuery(): AssociationEditQuery { return new AssociationEditQuery(); }
        /**
         * Keep source identifiers visible for verifying query construction.
         *
         * @param string $name Column or table identifier.
         * @return string Same identifier.
         */
        public function quoteName(string $name): string { return $name; }
        /**
         * Store the most recently prepared SQL expression.
         *
         * @param AssociationEditQuery $query Query supplied by the field or table.
         * @return self This database.
         */
        public function setQuery(AssociationEditQuery $query): self {
            $this->query = $query;
            return $this;
        }
        /**
         * Resolve an existing association's country for edit-mode filtering.
         *
         * @return string|null Country code for the selected association.
         */
        public function loadResult(): ?string {
            $id = $this->query->bindings[':selectedId'][0] ?? null;
            return is_int($id) && isset($this->records[$id])
                ? (string) $this->records[$id]->country : null;
        }
        /**
         * Load all association tree rows or only rows in the selected country.
         *
         * @return array<int,object> Selected association records.
         */
        public function loadObjectList(): array {
            $country = $this->query->bindings[':country'][0] ?? null;
            return array_values(array_filter(
                $this->records,
                static fn (object $row): bool => $country === null || $row->country === $country
            ));
        }
        /**
         * Resolve one ancestor by its integer ID for save-time validation.
         *
         * @return object|null Parent association or no row.
         */
        public function loadObject(): ?object {
            if ($this->fail) {
                throw new \RuntimeException('SQL password=never_expose');
            }
            $id = $this->query->bindings[':associationParentId'][0] ?? null;
            if (is_int($id)) {
                $this->readParentIds[] = $id;
                return $this->records[$id] ?? null;
            }
            return null;
        }
    }

    $db = new AssociationEditDatabase();
    $db->records = [
        10 => (object) ['id' => 10, 'name' => 'North DEU', 'country' => 'DEU', 'parent_id' => 0],
        11 => (object) ['id' => 11, 'name' => 'West DEU', 'country' => 'DEU', 'parent_id' => 10],
        12 => (object) ['id' => 12, 'name' => 'District DEU', 'country' => 'DEU', 'parent_id' => 11],
        20 => (object) ['id' => 20, 'name' => 'Austria', 'country' => 'AUT', 'parent_id' => 0],
        30 => (object) ['id' => 30, 'name' => 'Cycle A', 'country' => 'DEU', 'parent_id' => 31],
        31 => (object) ['id' => 31, 'name' => 'Cycle B', 'country' => 'DEU', 'parent_id' => 30],
        45 => (object) ['id' => 45, 'name' => 'Invalid country', 'country' => 'D/', 'parent_id' => 0],
    ];
    $GLOBALS['associationEditDatabase'] = $db;

    $input = new class {
        public string $view = 'jlextassociation';
        public int $id = 0;
        public array $form = [];
        public object $post;
        /**
         * Expose the current edit view and option parameter.
         *
         * @param string $name Input parameter.
         * @param string $default Fallback value.
         * @return string Current view or component value.
         */
        public function getCmd(string $name, string $default = ''): string {
            return $name === 'view' ? $this->view : $default;
        }
        /**
         * Resolve the row ID Joomla is currently editing.
         *
         * @param string $name Request parameter.
         * @param mixed $default Default identifier.
         * @param string $filter Joomla filter type.
         * @return mixed Current numeric edit ID.
         */
        public function get(string $name, mixed $default = 0, string $filter = 'raw'): mixed {
            return $name === 'id' ? $this->id : $default;
        }
    };
    $input->post = new class($input) {
        /**
         * Hold the shared form input so country selections are accessible.
         *
         * @param object $input Mutable edit request fixture.
         */
        public function __construct(private object $input) {}
        /**
         * Return the selected jform country or empty filter on a first load.
         *
         * @param string $key POST parameter.
         * @param mixed $default Empty default.
         * @param string $filter Joomla input type.
         * @return mixed Submitted form data.
         */
        public function get(string $key, mixed $default = [], string $filter = 'array'): mixed {
            return $key === 'jform' ? $this->input->form : $default;
        }
    };
    $app = new \Joomla\CMS\Application\AdministratorApplication();
    $app->input = $input;
    $GLOBALS['associationEditContainer'] = new class($app) {
        /**
         * Hold the fake Joomla administrator service for field resolution.
         *
         * @param object $app Joomla app stub.
         */
        public function __construct(private object $app) {}
        /**
         * Resolve the administrator application by its Joomla class identifier.
         *
         * @param string $class Service identifier.
         * @return object Current test administrator app.
         */
        public function get(string $class): object { return $this->app; }
    };

    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Field/AssociationslistField.php';
    require_once $root . '/admin/src/Table/JlextassociationTable.php';
    $field = new \Diddipoeler\Component\SportsManagement\Administrator\Field\AssociationslistField();
    $getOptions = new \ReflectionMethod($field, 'getOptions');

    // New association: show all known country-qualified candidate parents,
    // instead of the previous empty parent list when the record ID was zero.
    $options = $getOptions->invoke($field);
    $labels = array_column(array_map(
        static fn (object $option): array => ['value' => $option->value, 'text' => $option->text],
        $options
    ), 'text', 'value');
    associationEditAssert(count($options) === 7
        && $labels['0'] === '-- No parent --'
        && $labels['10'] === '(DEU) North DEU'
        && $labels['20'] === '(AUT) Austria'
        && !isset($labels['45']),
        'New Landesverband form does not show valid country-qualified parents.');

    // On a POST retry with a country, show only that country's hierarchy.
    $input->form = ['country' => 'AUT'];
    $options = $getOptions->invoke($field);
    associationEditAssert(count($options) === 2
        && $options[1]->value === '20',
        'Submitted Austrian country did not restrict parent options.');

    // Existing association: exclude itself plus descendants to prevent a
    // self-linked or cyclic parent from being offered for edit.
    $input->id = 10;
    $input->form = [];
    $options = $getOptions->invoke($field);
    associationEditAssert(count($options) === 1 && $options[0]->value === '0',
        'Editing a root association offered itself or descendants as parents.');
    $input->id = 11;
    $options = $getOptions->invoke($field);
    associationEditAssert(count($options) === 2 && $options[1]->value === '10',
        'Editing a child should offer valid ancestors, not itself or its descendants.');

    $tableClass = \Diddipoeler\Component\SportsManagement\Administrator\Table\JlextassociationTable::class;
    $table = new $tableClass($db);
    $table->name = 'Valid Association';
    $table->country = 'DEU';
    $table->parent_id = 0;
    associationEditAssert($table->check() === true && $table->alias === 'valid-association',
        'Saving a top-level association must remain possible.');

    $table->parent_id = 12;
    associationEditAssert($table->check() === true && $db->readParentIds === [12, 11, 10],
        'Saving a new association below a valid same-country parent chain failed.');
    $table->id = 10;
    $table->parent_id = 10;
    associationEditAssert($table->check() === false,
        'An association cannot select itself as parent.');
    $table->parent_id = 12;
    associationEditAssert($table->check() === false,
        'A root association cannot be attached beneath its own descendant.');
    $table->id = 0;
    $table->country = 'DEU';
    $table->parent_id = 20;
    associationEditAssert($table->check() === false,
        'A German association cannot select an Austrian parent.');
    $table->parent_id = 999;
    associationEditAssert($table->check() === false,
        'A parent ID not present in the active database must be rejected.');
    $table->parent_id = 30;
    associationEditAssert($table->check() === false,
        'Existing cyclic parent chains must not be extended.');
    $table->parent_id = 'abc';
    associationEditAssert($table->check() === false
        && end($table->errors) === 'COM_SPORTSMANAGEMENT_ASSOCIATION_INVALID_PARENT',
        'Invalid form parent IDs should produce a translated validation error.');

    // SQL failure remains a generic form validation error, not a raw private
    // database exception or a partial hierarchy mutation.
    $table->parent_id = 10;
    $db->fail = true;
    associationEditAssert($table->check() === false
        && end($table->errors) === 'COM_SPORTSMANAGEMENT_ASSOCIATION_INVALID_PARENT',
        'A database lookup failure must not save an unverified parent.');
    associationEditAssert(count(\Joomla\CMS\Log\Log::$entries) === 1
        && !str_contains(\Joomla\CMS\Log\Log::$entries[0][0], 'password')
        && \Joomla\CMS\Log\Log::$entries[0][2] === 'com_sportsmanagement',
        'Sensitive ancestor lookup details must not be logged or displayed.');

    $form = simplexml_load_file($root . '/admin/forms/jlextassociation.xml');
    associationEditAssert($form instanceof \SimpleXMLElement,
        'The native Landesverband edit form is not valid XML.');
    $parents = $form->xpath('//field[@name="parent_id"]');
    associationEditAssert(count($parents) === 1
        && (string) $parents[0]['type'] === 'Associationslist'
        && (string) $parents[0]['default'] === '0'
        && (string) $parents[0]->option['value'] === '0',
        'The edit form must expose an explicit no-parent option alongside candidates.');

    echo "Joomla 5/6 new/edit Landesverband parent selection and hierarchy checks passed.\n";
}
