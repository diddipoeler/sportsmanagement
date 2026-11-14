<?php
/**
 * Test private Joomla 5/6 administrator project-list database error handling.
 *
 * Invalid selectors must never create a query, and database failures must
 * produce a generic model error without retaining SQL or login credentials.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return an untranslated Joomla administrator error key.
         *
         * @param string $key Message key.
         * @return string Original test key.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $entries = [];

        /**
         * Capture a sanitized administrator database failure.
         *
         * @param string $message Safe log message.
         * @param int $level Log severity.
         * @param string $category Joomla log category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace Joomla\Database {
    final class ParameterType
    {
        public const INTEGER = 1;
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementListModel
    {
        public object $database;
        public string $testError = '';
        public int $databaseRequests = 0;

        /**
         * Return the query fixture while counting database accesses.
         *
         * @return object Fake Joomla database with a failing SQL execute.
         */
        protected function getDatabase(): object
        {
            ++$this->databaseRequests;
            return $this->database;
        }

        /**
         * Capture the safe Joomla-facing model error.
         *
         * @param string $message Translated error message.
         * @return void
         */
        public function setError(string $message): void { $this->testError = $message; }
    }
}

namespace {
    \define('_JEXEC', 1);
    require_once \dirname(__DIR__, 2) . '/admin/src/Model/ProjectsModel.php';

    $db = new class {
        public ?object $lastQuery = null;

        /**
         * Return an isolated query tracking bound league and season selectors.
         *
         * @return object Query fixture.
         */
        public function createQuery(): object
        {
            return new class {
                public array $where = [];
                public array $bindings = [];

                /** @return self Prepared selected ID query. */
                public function select(string $value): self { return $this; }
                /** @return self Prepared project source table. */
                public function from(string $table): self { return $this; }
                /** @return self Query with a named condition. */
                public function where(string $condition): self {
                    $this->where[] = $condition;
                    return $this;
                }

                /**
                 * Bind all requested project season IDs in the query.
                 *
                 * @param string $column Season column name.
                 * @param array $ids Valid season IDs.
                 * @param int $type Joomla integer parameter constant.
                 * @return self Updated query.
                 */
                public function whereIn(string $column, array $ids, int $type): self {
                    $this->bindings['seasonIds'] = $ids;
                    return $this;
                }

                /**
                 * Bind a validated league ID to its SQL placeholder.
                 *
                 * @param string $key Parameter placeholder.
                 * @param mixed $value League identifier.
                 * @param int $type Joomla integer parameter constant.
                 * @return self Updated query.
                 */
                public function bind(string $key, mixed $value, int $type): self {
                    $this->bindings[$key] = [$value, $type];
                    return $this;
                }
            };
        }

        /**
         * Return a plain SQL identifier for structural query assertions.
         *
         * @param string $name Joomla database column or table name.
         * @param string|null $alias Optional table alias.
         * @return string Identifier with an optional alias.
         */
        public function quoteName(string $name, ?string $alias = null): string {
            return $name . ($alias !== null ? ' AS ' . $alias : '');
        }

        /**
         * Refuse execution with a credential-bearing database error.
         *
         * @param object $query Query to execute.
         * @param int $offset First result position.
         * @param int $limit Maximum result count.
         * @return never Always throws a synthetic private diagnostic.
         */
        public function setQuery(object $query, int $offset = 0, int $limit = 0): never
        {
            $this->lastQuery = $query;
            throw new \RuntimeException('SQLSTATE password=TOPSECRET user=appadmin SQL SELECT private_table');
        }
    };

    $model = (new \ReflectionClass(
        \Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectsModel::class
    ))->newInstanceWithoutConstructor();
    $model->database = $db;

    if ($model->existcurrentseason(['8bad'], 3) !== null
        || $model->existcurrentseason([8], '3oops') !== null
        || $model->databaseRequests !== 0) {
        throw new \RuntimeException('Malformed project selectors executed a database query.');
    }

    if ($model->existcurrentseason(['008', 12], '0003') !== null
        || ($db->lastQuery->bindings['seasonIds'] ?? null) !== [8, 12]
        || ($db->lastQuery->bindings[':currentSeasonLeagueId'] ?? null) !== [3, \Joomla\Database\ParameterType::INTEGER]) {
        throw new \RuntimeException('Bound project query failed to handle a database error.');
    }

    $entries = \Joomla\CMS\Log\Log::$entries;
    if ($model->testError !== 'JERROR_AN_ERROR_HAS_OCCURRED'
        || count($entries) !== 1
        || ($entries[0][1] ?? null) !== \Joomla\CMS\Log\Log::ERROR
        || ($entries[0][2] ?? null) !== 'com_sportsmanagement'
        || !str_contains($entries[0][0] ?? '', 'RuntimeException')
        || str_contains($entries[0][0] ?? '', 'TOPSECRET')
        || str_contains($model->testError, 'TOPSECRET')) {
        throw new \RuntimeException('Administrator project list exposed database credentials.');
    }

    echo "Joomla 5/6 project-list DB error privacy and typed selectors OK\n";
}
