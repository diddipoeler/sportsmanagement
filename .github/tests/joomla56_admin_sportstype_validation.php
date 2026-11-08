<?php
/**
 * Verify Joomla 5/6 sports-type form field validation and database privacy.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel
    {
        public static mixed $form = null;
        public static ?object $db = null;
        public array $errors = [];

        /**
         * Provide the configured fake Joomla administrator form.
         *
         * @param array $data Form values supplied by the editor.
         * @param bool $loadData Whether to load stored values.
         * @return mixed Current Joomla form fixture or false.
         */
        public function getForm($data = [], $loadData = true)
        {
            return self::$form;
        }

        /**
         * Resolve the database injected into the native administrator model.
         *
         * @return object Database used by this test case.
         */
        protected function getDatabase(): object
        {
            return self::$db;
        }

        /**
         * Record a user-facing generic Joomla administrator model error.
         *
         * @param string $error Joomla translated message.
         * @return void
         */
        public function setError(string $error): void
        {
            $this->errors[] = $error;
        }
    }
}

namespace Joomla\CMS\Component {
    class ComponentHelper
    {
        public static mixed $mediaTool = 'media';

        /**
         * Provide the component's configured media field type.
         *
         * @param string $component Joomla extension identifier.
         * @return object Simulated component parameter registry.
         */
        public static function getParams(string $component): object
        {
            return new class {
                /**
                 * Return the configured media-field option.
                 *
                 * @param string $key Configuration key.
                 * @param mixed $default Fallback value.
                 * @return mixed Configured value.
                 */
                public function get(string $key, mixed $default = null): mixed
                {
                    return ComponentHelper::$mediaTool;
                }
            };
        }
    }
}

namespace Joomla\CMS\Language {
    class Text
    {
        /**
         * Return a standard generic Joomla database failure message.
         *
         * @param string $key Joomla translation identifier.
         * @return string Harmless user-facing error.
         */
        public static function _(string $key): string
        {
            return $key === 'JERROR_AN_ERROR_HAS_OCCURRED'
                ? 'A generic administrator error occurred.'
                : $key;
        }
    }
}

namespace Joomla\CMS\Log {
    class Log
    {
        public const ERROR = 1;
        public static array $messages = [];

        /**
         * Record private SQL diagnostics in the Joomla component log.
         *
         * @param string $message Internal exception text.
         * @param int $level Error severity.
         * @param string $category Component logging channel.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$messages[] = [$message, $level, $category];
        }
    }
}

namespace Joomla\Database {
    class ParameterType
    {
        public const INTEGER = 'integer';
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Model/SportstypeModel.php';

    class SportstypeForm
    {
        public array $attributes = [];

        /**
         * Check whether a Joomla form field exists in this fixture.
         *
         * @param string $field Joomla field name.
         * @return bool Whether the edit field exists.
         */
        public function getField(string $field): bool
        {
            return in_array($field, ['icon', 'name'], true);
        }

        /**
         * Capture changed attributes for the native sports-type form.
         *
         * @param string $field Edited Joomla form field name.
         * @param string $attribute Attribute being changed.
         * @param string $value New attribute value.
         * @return void
         */
        public function setFieldAttribute(string $field, string $attribute, string $value): void
        {
            $this->attributes[$field][$attribute] = $value;
        }
    }

    class SportstypeQuery
    {
        public array $bindings = [];

        /**
         * Accept the SQL SELECT requested by the sports-type model.
         *
         * @param string $columns Requested column expressions.
         * @return self Fluent query builder.
         */
        public function select(string $columns): self { return $this; }

        /**
         * Accept the native sports-type database table.
         *
         * @param string $name Joomla table name.
         * @return self Fluent query builder.
         */
        public function from(string $name): self { return $this; }

        /**
         * Accept the SQL match restriction for a sports-type record.
         *
         * @param string $condition SQL placeholder clause.
         * @return self Fluent query builder.
         */
        public function where(string $condition): self { return $this; }

        /**
         * Record the bound sports-type identifier from Joomla SQL.
         *
         * @param string $key Named SQL parameter.
         * @param int $value Selected sports-type identifier.
         * @param string $type Joomla binding type.
         * @return self Fluent query builder.
         */
        public function bind(string $key, int $value, string $type): self
        {
            $this->bindings[$key] = $value;
            return $this;
        }
    }

    class SportstypeDatabase
    {
        public ?SportstypeQuery $lastQuery = null;
        public int $queries = 0;
        public int $limit = -1;
        public bool $failColumns = false;
        public bool $failSelect = false;
        public ?object $record = null;

        /**
         * Return the Joomla table prefix for column-size detection.
         *
         * @return string Test site's table prefix.
         */
        public function getPrefix(): string { return 'jos_'; }

        /**
         * Resolve column types or simulate an unavailable schema.
         *
         * @param string $name Fully prefixed sports-type table.
         * @param bool $types Include database type metadata.
         * @return array<string, string> Column types to annotate in the form.
         */
        public function getTableColumns(string $name, bool $types): array
        {
            if ($this->failColumns) {
                throw new \RuntimeException('Optional schema metadata missing');
            }

            return ['icon' => 'varchar(200)', 'name' => 'varchar(80)', 'id' => 'int'];
        }

        /**
         * Create a fake prepared query for a single sports-type record.
         *
         * @return SportstypeQuery SQL query fixture.
         */
        public function createQuery(): SportstypeQuery
        {
            ++$this->queries;
            return new SportstypeQuery();
        }

        /**
         * Return the SQL field or table name unchanged in this fixture.
         *
         * @param string $name SQL identifier.
         * @return string Unquoted identifier.
         */
        public function quoteName(string $name): string { return $name; }

        /**
         * Capture the one-row lookup or simulate a private SQL failure.
         *
         * @param SportstypeQuery $query Scoped query.
         * @param int $offset Joomla result offset.
         * @param int $limit Joomla row cap.
         * @return void
         */
        public function setQuery(SportstypeQuery $query, int $offset = 0, int $limit = 0): void
        {
            $this->lastQuery = $query;
            $this->limit = $limit;
            if ($this->failSelect) {
                throw new \RuntimeException('SQLSTATE password=private');
            }
        }

        /**
         * Return the currently configured sports-type row.
         *
         * @return object|null Sports-type record fixture.
         */
        public function loadObject(): ?object { return $this->record; }
    }

    $base = \Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel::class;
    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\SportstypeModel();
    $settings = \Joomla\CMS\Component\ComponentHelper::class;
    $db = new SportstypeDatabase();
    $base::$db = $db;
    $base::$form = false;

    if ($model->getForm() !== false) {
        throw new \RuntimeException('Missing Joomla sports-type form was accepted.');
    }

    foreach ([[['script'], 'media'], ['../../escape', 'media'],
        ['0002', 'media'], ['custom_media', 'custom_media']] as [$configured, $expected]) {
        $settings::$mediaTool = $configured;
        $form = new SportstypeForm();
        $base::$form = $form;
        if ($model->getForm() !== $form
            || ($form->attributes['icon']['type'] ?? null) !== $expected
            || ($form->attributes['name']['size'] ?? null) !== '80') {
            throw new \RuntimeException('Invalid media field type or SQL column size was accepted.');
        }
    }

    $settings::$mediaTool = 'media';
    $db->failColumns = true;
    if ($model->getForm() !== $base::$form) {
        throw new \RuntimeException('Optional schema metadata failure broke form rendering.');
    }

    if ($model->getSportstype(0) !== null || $db->queries !== 0) {
        throw new \RuntimeException('Invalid sports-type ID reached the database.');
    }

    $row = (object) ['id' => 7, 'name' => 'Football'];
    $db->record = $row;
    if ($model->getSportstype(7) !== $row || $db->limit !== 1
        || $db->lastQuery?->bindings[':sportstypeId'] !== 7) {
        throw new \RuntimeException('Sports-type query lost the bound ID or one-row limit.');
    }

    $db->failSelect = true;
    if ($model->getSportstype(7) !== null
        || $model->errors !== ['A generic administrator error occurred.']
        || count(\Joomla\CMS\Log\Log::$messages) !== 1
        || !str_contains(\Joomla\CMS\Log\Log::$messages[0][0], 'password=private')) {
        throw new \RuntimeException('Sports-type SQL exception was not isolated in the private log.');
    }

    echo "Joomla 5/6 sports-type form validation and SQL privacy passed.\n";
}
