<?php
/**
 * Joomla 5/6 matrix model project/round/division SQL boundary regressions.
 *
 * Uses isolated Joomla database stubs to verify bound query scope and private
 * error reporting for the native matrix model without a real SQL server.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    final class ParameterType
    {
        public const INTEGER = 'int';
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 400;
        public static array $errors = [];

        /**
         * Save private Joomla model log messages for exposure assertions.
         *
         * @param string $message Original driver failure.
         * @param int $level Joomla log severity.
         * @param string $category Component logging namespace.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$errors[] = [$message, $level, $category];
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return the requested untranslated Joomla error message key.
         *
         * @param string $key Joomla language constant.
         * @return string Same language constant for comparison.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\MVC\Factory {
    interface MVCFactoryInterface {}
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementProjectModel
    {
        protected int $projectId = 42;
        protected int $divisionId = 0;
        public static ?object $database = null;
        public static ?object $application = null;

        /**
         * Return the fake Joomla database with a query-recording builder.
         *
         * @return object Database fixture.
         */
        protected function getDatabase(): object { return self::$database; }

        /**
         * Return the isolated frontend Joomla message recorder.
         *
         * @return object Application fixture for error messages.
         */
        protected function siteApplication(): object { return self::$application; }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Model/MatrixModel.php';

    $db = new class {
        public array $queries = [];
        public int $executions = 0;
        public bool $throw = false;
        public ?object $object = null;
        public array $objects = [];

        /**
         * Begin a new query and retain its SQL fragments and bound parameters.
         *
         * @return object Chainable query fixture.
         */
        public function createQuery(): object
        {
            $query = new class {
                public array $clauses = [];
                public array $bindings = [];

                /**
                 * Track all selected columns without interacting with SQL.
                 *
                 * @param mixed $args Query builder expression.
                 * @return self Chainable query object.
                 */
                public function select(mixed $args): self
                {
                    $this->clauses[] = ['select', $args]; return $this;
                }

                /**
                 * Track the FROM table in the native Joomla model.
                 *
                 * @param mixed $args Table expression.
                 * @return self Chainable query object.
                 */
                public function from(mixed $args): self
                {
                    $this->clauses[] = ['from', $args]; return $this;
                }

                /**
                 * Track joins and the requested division condition.
                 *
                 * @param mixed $a Join type.
                 * @param mixed $b Join expression.
                 * @return self Chainable query object.
                 */
                public function join(mixed $a, mixed $b): self
                {
                    $this->clauses[] = ['join', $a, $b]; return $this;
                }

                /**
                 * Record WHERE predicates for later project isolation assertions.
                 *
                 * @param mixed $args Parameter-bound predicate.
                 * @return self Chainable query object.
                 */
                public function where(mixed $args): self
                {
                    $this->clauses[] = ['where', $args]; return $this;
                }

                /**
                 * Record bind values and ensure project and round IDs are integers.
                 *
                 * @param string $name Named parameter.
                 * @param mixed $value Parameter value.
                 * @param mixed $type Joomla parameter type.
                 * @return self Chainable query object.
                 */
                public function bind(string $name, mixed $value, mixed $type): self
                {
                    $this->bindings[$name] = [$value, $type]; return $this;
                }

                /**
                 * Capture the configured round sort expression.
                 *
                 * @param mixed $args Sorting expression.
                 * @return self Chainable query object.
                 */
                public function order(mixed $args): self
                {
                    $this->clauses[] = ['order', $args]; return $this;
                }
            };

            $this->queries[] = $query;
            return $query;
        }

        /**
         * Preserve Joomla identifier names for SQL predicate comparisons.
         *
         * @param string $name Column or table identifier.
         * @return string Identity representation of the field.
         */
        public function quoteName(string $name): string { return $name; }

        /**
         * Count all actual model queries that reach the driver interface.
         *
         * @param object $query Parameter-bound Joomla query object.
         * @param int $offset Optional pagination offset.
         * @param int $limit Optional row count.
         * @return void
         */
        public function setQuery(object $query, int $offset = 0, int $limit = 0): void
        {
            ++$this->executions;
            if ($this->throw) {
                throw new \RuntimeException('db-secret-password=123');
            }
        }

        /**
         * Return the simulated result of a scoped round lookup.
         *
         * @return object|null Round fixture.
         */
        public function loadObject(): ?object { return $this->object; }

        /**
         * Return simulated match rows selected by the scoped result query.
         *
         * @return array Array of match objects.
         */
        public function loadObjectList(): array { return $this->objects; }
    };

    $app = new class {
        public array $messages = [];

        /**
         * Capture Joomla public feedback and validate error privacy.
         *
         * @param string $message Frontend visitor message.
         * @param string $type Joomla severity.
         * @return void
         */
        public function enqueueMessage(string $message, string $type = 'message'): void
        {
            $this->messages[] = [$message, $type];
        }
    };

    $base = \Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementProjectModel::class;
    $base::$database = $db;
    $base::$application = $app;

    $model = (new \ReflectionClass(
        \Diddipoeler\Component\SportsManagement\Site\Model\MatrixModel::class
    ))->newInstanceWithoutConstructor();

    $class = \Diddipoeler\Component\SportsManagement\Site\Model\MatrixModel::class;

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $class::$roundid = 16;
        $class::$divisionid = 7;
        $db->object = (object) ['id' => 16, 'project_id' => 42];

        $round = $model->getRound();
        $query = end($db->queries);

        if (($round->id ?? null) !== 16
            || ($query->bindings[':roundId'][0] ?? null) !== 16
            || ($query->bindings[':matrixRoundProjectId'][0] ?? null) !== 42) {
            throw new \RuntimeException('Matrix round lookup lost its active-project restriction.');
        }

        $start = $db->executions;
        $class::$roundid = 0;

        if ($model->getRound() !== null || $db->executions !== $start) {
            throw new \RuntimeException('Matrix queried a missing round ID.');
        }

        $class::$roundid = 16;
        if ($model->getMatrixResults(23) !== [] || $db->executions !== $start) {
            throw new \RuntimeException('Matrix queried matches from another requested project.');
        }

        $model->getMatrixResults(42);
        $query = end($db->queries);
        $clauses = json_encode($query->clauses, JSON_THROW_ON_ERROR);

        if (($query->bindings[':matrixProjectId'][0] ?? null) !== 42
            || ($query->bindings[':matrixDivisionId'][0] ?? null) !== 7
            || ($query->bindings[':matrixParentDivisionId'][0] ?? null) !== 7
            || ($query->bindings[':matrixRoundId'][0] ?? null) !== 16
            || !str_contains($clauses, 'd1.id IS NOT NULL')) {
            throw new \RuntimeException('Matrix SQL did not filter requested division and round.');
        }

        $db->throw = true;
        $start = $db->executions;

        if ($model->getMatrixResults(42) !== [] || $db->executions !== $start + 1
            || $app->messages !== [['JERROR_AN_ERROR_HAS_OCCURRED', 'error']]
            || count(\Joomla\CMS\Log\Log::$errors) !== 1
            || !str_contains(\Joomla\CMS\Log\Log::$errors[0][0], 'db-secret-password=123')) {
            throw new \RuntimeException('Matrix query did not privately log database failures.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 matrix bound round, division, project and private errors OK\n";
}
