<?php
/**
 * Isolated Joomla 5/6 prediction game and rounds field regression fixtures.
 *
 * Calls the real administrator form fields with an in-memory application,
 * form and Joomla query builder to check ID validation and bound predicates.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application {
    final class AdministratorApplication {}
}

namespace Joomla\CMS {
    final class Factory
    {
        /**
         * Supply an in-memory Joomla dependency injection container.
         *
         * @return object Container with the current fixture application.
         */
        public static function getContainer(): object
        {
            return new class {
                /**
                 * Return the in-memory administrator application service.
                 *
                 * @param string $name Joomla application service identifier.
                 * @return object Current application fixture.
                 */
                public function get(string $name): object
                {
                    return \SelectorFixtureApplication::$instance;
                }
            };
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Use stable language keys instead of starting Joomla translations.
         *
         * @param string $key Joomla translation key.
         * @return string The untranslated key.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\Database {
    final class ParameterType
    {
        public const INTEGER = 'integer';
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Helper {
    final class SportsManagementAdministratorApplicationResolver
    {
        /**
         * Resolve the test application for prediction team options.
         *
         * @return object Prepared backend application fixture.
         */
        public static function resolve(): object
        {
            return \SelectorFixtureApplication::$instance;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    final class SportsManagementAdministratorApplicationResolver
    {
        /**
         * Resolve the test application for fallback project inputs.
         *
         * @return object Prepared backend application fixture.
         */
        public static function resolve(): object
        {
            return \SelectorFixtureApplication::$instance;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Field {
    class SportsManagementListField
    {
        protected mixed $form = null;
        protected array $element = [];
        protected int $size = 1;

        /**
         * Store nothing in the stub; real Joomla handles native form setup.
         *
         * @param \SimpleXMLElement $element Field XML.
         * @param mixed $value Saved field values.
         * @param string|null $group Form group.
         * @return bool Successful fixture setup.
         */
        public function setup(\SimpleXMLElement $element, $value, $group = null)
        {
            return true;
        }

        /**
         * Provide a deterministic parent input for multiple-selector tests.
         *
         * @return string Fake HTML control.
         */
        protected function getInput(): string
        {
            return 'native-list';
        }

        /**
         * Return Joomla's inherited empty options in the standalone fixture.
         *
         * @return array<int,object> No parent selector options.
         */
        protected function getOptions(): array
        {
            return [];
        }

        /**
         * Return the current in-memory Joomla database instead of SQL.
         *
         * @return \SelectorFixtureDatabase Recording database fixture.
         */
        protected function getSportsManagementDatabase(): \SelectorFixtureDatabase
        {
            return \SelectorFixtureDatabase::$instance;
        }
    }
}

namespace {
    final class SelectorFixtureQuery
    {
        public array $bindings = [];
        public array $conditions = [];
        public array $orders = [];

        /**
         * Accept selected SQL fields for a native Joomla selector.
         *
         * @param mixed $columns Selected SQL column names.
         * @return self Fluent query instance.
         */
        public function select(mixed $columns): self { return $this; }

        /**
         * Accept the source table for a Joomla query.
         *
         * @param string $source Quoted table expression.
         * @return self Fluent query instance.
         */
        public function from(string $source): self { return $this; }

        /**
         * Accept a join without executing SQL.
         *
         * @param string $kind SQL join type.
         * @param string $expression SQL join expression.
         * @return self Fluent query instance.
         */
        public function join(string $kind, string $expression): self { return $this; }

        /**
         * Collect a SQL predicate for later assertions.
         *
         * @param string $expression SQL WHERE condition.
         * @return self Fluent query instance.
         */
        public function where(string $expression): self
        {
            $this->conditions[] = $expression;
            return $this;
        }

        /**
         * Capture a typed Joomla database query binding.
         *
         * @param string $name SQL named placeholder.
         * @param mixed $value Bound scalar value.
         * @param mixed $type Joomla database parameter type.
         * @return self Fluent query instance.
         */
        public function bind(string $name, mixed $value, mixed $type): self
        {
            $this->bindings[$name] = [$value, $type];
            return $this;
        }

        /**
         * Accept GROUP BY values in a prediction form query.
         *
         * @param mixed $group Grouped columns.
         * @return self Fluent query instance.
         */
        public function group(mixed $group): self { return $this; }

        /**
         * Preserve query sort-order clauses for form field compatibility.
         *
         * @param mixed $order Sort field or expression.
         * @return self Fluent query instance.
         */
        public function order(mixed $order): self
        {
            $this->orders[] = $order;
            return $this;
        }
    }

    final class SelectorFixtureDatabase
    {
        public static self $instance;
        public int $queries = 0;
        public ?SelectorFixtureQuery $last = null;

        /**
         * Construct a standalone Joomla SQL query recorder.
         *
         * @return SelectorFixtureQuery Fresh fluent query builder.
         */
        public function createQuery(): SelectorFixtureQuery
        {
            return new SelectorFixtureQuery();
        }

        /**
         * Support getQuery(true) used by prediction round and team fields.
         *
         * @param bool $new Whether a fresh query should be created.
         * @return SelectorFixtureQuery Fresh query builder.
         */
        public function getQuery(bool $new = true): SelectorFixtureQuery
        {
            return $this->createQuery();
        }

        /**
         * Represent SQL table and field quoting without using a database.
         *
         * @param string $name Joomla table or column name.
         * @param string|null $alias Optional output name.
         * @return string Inspectable quoted name.
         */
        public function quoteName(string $name, ?string $alias = null): string
        {
            return '[' . $name . ']' . ($alias === null ? '' : ' AS [' . $alias . ']');
        }

        /**
         * Capture a prepared Joomla query instead of executing it.
         *
         * @param SelectorFixtureQuery $query Selected field query.
         * @return void
         */
        public function setQuery(SelectorFixtureQuery $query): void
        {
            $this->last = $query;
            ++$this->queries;
        }

        /**
         * Return a representative row for all three selector types.
         *
         * @return array<int,object> One round, project team and match value.
         */
        public function loadObjectList(): array
        {
            return [(object) [
                'id' => 12, 'value' => 12, 'text' => 'Round 12',
                'teamname' => 'FC Example', 'name' => 'Round 12',
            ]];
        }
    }

    final class SelectorFixtureApplication
    {
        public static self $instance;
        public mixed $state = 0;
        public array $inputs = [];

        /**
         * Return the raw saved prediction identifier without coercion.
         *
         * @param string $key Joomla user-state identifier.
         * @param mixed $default Default ID when missing.
         * @return mixed Test scalar or intentionally malformed value.
         */
        public function getUserState(string $key, mixed $default = null): mixed
        {
            return $this->state;
        }

        /**
         * Supply a Joomla request-input object with validated integer getters.
         *
         * @return object Request input fixture.
         */
        public function getInput(): object
        {
            return new class {
                /**
                 * Return one configured Joomla integer request ID.
                 *
                 * @param string $name Request input field.
                 * @param int $default Default ID.
                 * @return int Configured numeric request field.
                 */
                public function getInt(string $name, int $default = 0): int
                {
                    return SelectorFixtureApplication::$instance->inputs[$name] ?? $default;
                }
            };
        }
    }

    final class SelectorFixtureForm
    {
        public array $values = [];

        /**
         * Return a project ID for one Joomla field and group context.
         *
         * @param string $key Field name.
         * @param string $group Form group.
         * @return mixed Saved selection or null when absent.
         */
        public function getValue(string $key, string $group = ''): mixed
        {
            return $this->values[$group . ':' . $key] ?? null;
        }
    }

    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Field/PredictionroundidField.php';
    require_once $root . '/admin/src/Field/PredictionproteamidField.php';
    require_once $root . '/admin/src/Field/RoundsField.php';

    SelectorFixtureDatabase::$instance = new SelectorFixtureDatabase();
    SelectorFixtureApplication::$instance = new SelectorFixtureApplication();
    $app = SelectorFixtureApplication::$instance;
    $db = SelectorFixtureDatabase::$instance;
    $ns = 'Diddipoeler\\Component\\SportsManagement\\Administrator\\Field\\';
    $assertions = 0;

    set_error_handler(static function (int $level, string $message): never {
        throw new \ErrorException($message, 0, $level);
    });

    try {
        foreach (['PredictionroundidField', 'PredictionproteamidField'] as $name) {
            $class = $ns . $name;
            $method = new \ReflectionMethod($class, 'predictionId');

            foreach ([[7, 7], ['0007', 7], ['0000', 0], ['7bad', 0],
                [' 7', 0], [-1, 0], [true, 0], [['7'], 0],
                [str_repeat('9', 70), 0]] as [$input, $expected]) {
                if ($method->invoke(null, $input) !== $expected) {
                    throw new \RuntimeException('Invalid prediction ID was not rejected.');
                }
                ++$assertions;
            }

            foreach (['7wrong', ['7'], true, '9999999999999999999999'] as $invalid) {
                $db->queries = 0;
                $app->state = $invalid;
                $field = new $class();
                $result = (new \ReflectionMethod($class, 'getOptions'))->invoke($field);

                if ($result !== [] || $db->queries !== 0) {
                    throw new \RuntimeException('Malformed prediction game queried unrelated data.');
                }
                ++$assertions;
            }

            $app->state = '0007';
            $db->queries = 0;
            $field = new $class();
            $method = new \ReflectionMethod($class, 'getOptions');
            $result = $method->invoke($field);

            if (count($result) !== 1 || $db->queries !== 1
                || $db->last?->bindings[':predictionId'] !== [7, 'integer']) {
                throw new \RuntimeException('Valid prediction selector lost its bound query.');
            }

            $method->invoke($field);
            if ($db->queries !== 1) {
                throw new \RuntimeException('Prediction field options were queried twice.');
            }
            ++$assertions;
        }

        $projectMethod = new \ReflectionMethod($ns . 'RoundsField', 'projectId');

        foreach ([[17, 17], ['00017', 17], ['17bad', 0], [['17'], 0],
            [false, 0], [str_repeat('9', 75), 0]] as [$value, $expected]) {
            if ($projectMethod->invoke(null, $value) !== $expected) {
                throw new \RuntimeException('Malformed project ID was accepted.');
            }
            ++$assertions;
        }

        $field = new ($ns . 'RoundsField')();
        $form = new SelectorFixtureForm();
        $form->values[':project'] = ['99'];
        $app->inputs = ['project_id' => 12];
        $prop = new \ReflectionProperty($ns . 'SportsManagementListField', 'form');
        $prop->setValue($field, $form);
        $element = new \ReflectionProperty($ns . 'SportsManagementListField', 'element');
        $element->setValue($field, ['required' => 'false', 'order' => ['DESC']]);
        $db->queries = 0;
        $result = (new \ReflectionMethod($ns . 'RoundsField', 'getOptions'))->invoke($field);

        if ($db->queries !== 0 || count($result) !== 1 || $result[0]->value !== '') {
            throw new \RuntimeException('Malformed project ID widened the rounds query.');
        }
        ++$assertions;

        $form->values[':project'] = '00017';
        $element->setValue($field, ['required' => 'false', 'order' => 'DESC']);
        $db->queries = 0;
        $result = (new \ReflectionMethod($ns . 'RoundsField', 'getOptions'))->invoke($field);

        if ($db->queries !== 1 || count($result) !== 2
            || $db->last?->bindings[':projectId'] !== [17, 'integer']) {
            throw new \RuntimeException('Valid bound project-round filter was lost.');
        }
        ++$assertions;
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 admin prediction/round selectors: {$assertions} assertions passed.\n";
}
