<?php
/**
 * Regression for portable project slugs, extra-field insert errors and malformed project-map rows.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Database {
    /** Standalone Joomla database contract. */
    interface DatabaseInterface {}
    /** Query placeholder value types. */
    final class ParameterType {
        public const INTEGER = 'integer';
        public const STRING = 'string';
    }
}
namespace Joomla\CMS\Application {
    /** Standalone backend application marker. */
    interface CMSApplicationInterface {}
}
namespace Joomla\CMS\Language {
    /** Joomla translation fixture. */
    final class Text {
        /**
         * Return a translation key unchanged.
         * @param string $value Language key.
         * @return string Unchanged value.
         */
        public static function _(string $value): string { return $value; }
    }
}
namespace Joomla\CMS\Log {
    /** Capture expected database error messages. */
    final class Log {
        public const ERROR = 1;
        public static array $messages = [];
        /**
         * Append a Joomla log entry.
         * @param string $message Safe message.
         * @param int $severity Log priority.
         * @param string $category Log channel.
         * @return void
         */
        public static function add(string $message, int $severity, string $category): void {
            self::$messages[] = $message;
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper {
    /** Stub administrator resolver. */
    final class SportsManagementAdministratorApplicationResolver {
        /**
         * Return the fixed backend fixture.
         * @return \Joomla\CMS\Application\CMSApplicationInterface Current admin app.
         */
        public static function resolve(): \Joomla\CMS\Application\CMSApplicationInterface {
            return \MigrationPortableTest::$app;
        }
    }
    /** Stub database resolver. */
    final class SportsManagementDatabaseResolver {
        /**
         * Preserve the caller-injected database.
         * @param mixed $selector Optional legacy database selector.
         * @param \Joomla\Database\DatabaseInterface|null $db Database fixture.
         * @return \Joomla\Database\DatabaseInterface Caller-selected database.
         */
        public function resolve(mixed $selector, ?\Joomla\Database\DatabaseInterface $db): \Joomla\Database\DatabaseInterface {
            return $db;
        }
    }
}
namespace {
    define('_JEXEC', 1);

    /** Shared application fixture. */
    final class MigrationPortableTest {
        public static \Joomla\CMS\Application\CMSApplicationInterface $app;
    }

    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Helper/ProjectNavigationHelper.php';
    require_once $root . '/admin/src/Helper/ExtraFieldsSaveHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_projectmap/src/Helper/ProjectMapHelper.php';

    $navDb = new class implements \Joomla\Database\DatabaseInterface {
        public array $columns = [];
        public mixed $result = null;
        /**
         * Create a fluent SQL fixture.
         * @return self SQL builder.
         */
        public function createQuery(): self { return $this; }
        /**
         * Collect selected fields without executing SQL.
         * @param mixed $columns Selected columns.
         * @return self Fluent SQL fixture.
         */
        public function select(mixed $columns): self {
            $this->columns = is_array($columns) ? $columns : [$columns];
            return $this;
        }
        /**
         * Quote an identifier for a portable SQL fixture.
         * @param string $name Database identifier.
         * @param string|null $alias Optional SQL alias.
         * @return string Quoted identifier.
         */
        public function quoteName(string $name, ?string $alias = null): string { return $name; }
        /**
         * Accept the queried project table.
         * @param string $table SQL table name.
         * @return self SQL fixture.
         */
        public function from(string $table): self { return $this; }
        /**
         * Add a query condition.
         * @param string $expression SQL predicate.
         * @return self SQL fixture.
         */
        public function where(string $expression): self { return $this; }
        /**
         * Bind a SQL placeholder value.
         * @param string $name Placeholder identifier.
         * @param mixed $value Placeholder value.
         * @param mixed $type Joomla parameter type.
         * @return self SQL fixture.
         */
        public function bind(string $name, mixed $value, mixed $type): self { return $this; }
        /**
         * Preserve the requested navigation ordering in the SQL builder fixture.
         * @param string $order SQL ordering.
         * @return self SQL fixture.
         */
        public function order(string $order): self { return $this; }
        /**
         * Accept an unexecuted statement.
         * @param mixed $query Query builder.
         * @param int $offset First row.
         * @param int $limit Maximum rows.
         * @return void
         */
        public function setQuery(mixed $query, int $offset, int $limit): void {}
        /**
         * Provide a selected project row.
         * @return mixed Raw fixture row.
         */
        public function loadObject(): mixed { return $this->result; }
    };

    $navigation = \Diddipoeler\Component\SportsManagement\Site\Helper\ProjectNavigationHelper::class;
    $navDb->result = (object) ['id' => '12', 'alias' => 'winter-league'];
    $next = $navigation::next($navDb, (object) ['league_id' => 5, 'name' => 'Summer', 'id' => 10]);
    if (!is_object($next) || $next->slug !== '12:winter-league'
        || count($navDb->columns) !== 1
        || str_contains(implode(' ', $navDb->columns), 'CONCAT_WS')) {
        throw new \RuntimeException('Navigation still requires a MySQL-only slug expression.');
    }

    foreach ([(object) ['id' => 0, 'alias' => 'bad'], (object) ['id' => '11suffix'], ['bad row']] as $row) {
        $navDb->result = $row;
        if ($navigation::next($navDb, (object) ['league_id' => 5, 'name' => 'Summer', 'id' => 10]) !== null) {
            throw new \RuntimeException('Navigation accepted a malformed result identity.');
        }
    }

    MigrationPortableTest::$app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        /**
         * Provide an admin task fixture.
         * @return object Request input.
         */
        public function getInput(): object {
            return new class {
                /**
                 * Return the normal save command.
                 * @param string $name Input key.
                 * @return string Task value.
                 */
                public function getCmd(string $name): string { return 'save'; }
            };
        }
        /**
         * Receive a generic Joomla administrator error.
         * @param string $message Error translation.
         * @param string $type Display message type.
         * @return void
         */
        public function enqueueMessage(string $message, string $type): void {}
    };

    $saveDb = new class implements \Joomla\Database\DatabaseInterface {
        public string $operation = '';
        public array $executed = [];
        /**
         * Return this fluent database query fixture.
         * @return self Query builder.
         */
        public function createQuery(): self { return $this; }
        /**
         * Accept delete table.
         * @param string $table SQL identifier.
         * @return self Fluent query.
         */
        public function delete(string $table): self { $this->operation = 'delete'; return $this; }
        /**
         * Accept a query condition.
         * @param array $conditions SQL filter list.
         * @return self Fluent query.
         */
        public function where(array $conditions): self { return $this; }
        /**
         * Accept insert table.
         * @param string $table SQL table identifier.
         * @return self Fluent query.
         */
        public function insert(string $table): self { $this->operation = 'insert'; return $this; }
        /**
         * Record the destination field list for a simulated extra-field insert.
         * @param mixed $columns Field name list.
         * @return self Fluent query.
         */
        public function columns(mixed $columns): self { return $this; }
        /**
         * Record the submitted SQL values for a simulated extra-field insert.
         * @param string $values SQL value placeholders.
         * @return self Fluent query.
         */
        public function values(string $values): self { return $this; }
        /**
         * Quote test SQL identifiers.
         * @param mixed $identifier Table or columns.
         * @return mixed Unchanged identifiers.
         */
        public function quoteName(mixed $identifier): mixed { return $identifier; }
        /**
         * Quote one field value.
         * @param string $value Field data.
         * @return string SQL string literal.
         */
        public function quote(string $value): string { return "'" . $value . "'"; }
        /**
         * Prepare a simulated statement.
         * @param self $query Built SQL statement.
         * @return self Current driver.
         */
        public function setQuery(self $query): self { return $this; }
        /**
         * Simulate successful deletion but a failed replacement insert.
         * @return bool Execution result.
         */
        public function execute(): bool {
            $this->executed[] = $this->operation;
            return $this->operation === 'delete';
        }
    };
    $adminSave = new \Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraFieldsSaveHelper();
    $adminSave->save(['extraf' => ['value'], 'extra_id' => ['12']], 5, $saveDb);
    if ($saveDb->executed !== ['delete', 'insert']
        || count(\Joomla\CMS\Log\Log::$messages) !== 1
        || !str_contains(\Joomla\CMS\Log\Log::$messages[0], 'RuntimeException')) {
        throw new \RuntimeException('Silent insert failure was not reported by the backend.');
    }

    $map = new \Diddipoeler\Module\SportsManagementProjectMap\Site\Helper\ProjectMapHelper();
    $mapDb = new class implements \Joomla\Database\DatabaseInterface {
        /**
         * Return an isolated query object.
         * @return self Fluent test query.
         */
        public function createQuery(): self { return $this; }
        /**
         * Quote an SQL identifier in test queries.
         * @param string $identifier SQL table or field.
         * @param string|null $alias Optional SQL alias.
         * @return string Unchanged identifier.
         */
        public function quoteName(string $identifier, ?string $alias = null): string { return $identifier; }
        /**
         * Accept selected columns.
         * @param mixed $value SQL selection.
         * @return self Fluent query.
         */
        public function select(mixed $value): self { return $this; }
        /**
         * Accept the project table.
         * @param string $value Table name.
         * @return self Fluent query.
         */
        public function from(string $value): self { return $this; }
        /**
         * Accept an INNER join.
         * @param string $type SQL join type.
         * @param string $value SQL join expression.
         * @return self Fluent query.
         */
        public function join(string $type, string $value): self { return $this; }
        /**
         * Accept the active-season filters.
         * @param string $value SQL predicate.
         * @return self Fluent query.
         */
        public function where(string $value): self { return $this; }
        /**
         * Accept bounded numeric season IDs.
         * @param string $column SQL column.
         * @param array $ids Season values.
         * @param mixed $type Joomla parameter type.
         * @return self Fluent query.
         */
        public function whereIn(string $column, array $ids, mixed $type): self { return $this; }
        /**
         * Order projects by country and descending ID.
         * @param string $value SQL ordering.
         * @return self Fluent query.
         */
        public function order(string $value): self { return $this; }
        /**
         * Receive a bounded SQL query.
         * @param mixed $query Query builder.
         * @param int $offset First row.
         * @param int $limit Maximum row count.
         * @return void
         */
        public function setQuery(mixed $query, int $offset, int $limit): void {}
        /**
         * Include malformed project rows in the database fixture.
         * @return array Raw project data.
         */
        public function loadObjectList(): array {
            return [
                false, ['invalid'], (object) ['country' => [], 'id' => 1],
                (object) ['country' => 'DEU', 'id' => 'abc'],
                (object) ['country' => 'DEU', 'id' => 9, 'project_alias' => 'first'],
                (object) ['country' => 'DEU', 'id' => 12, 'project_alias' => ['invalid']],
                (object) ['country' => 'FRA', 'id' => 4, 'project_alias' => 'fr'],
            ];
        }
    };
    $projects = $map->getData([1], $mapDb);
    if (count($projects) !== 2 || $projects[0]->project_slug !== '12:'
        || $projects[1]->project_slug !== '4:fr') {
        throw new \RuntimeException('Malformed project map results were not filtered safely.');
    }

    echo "Joomla 5/6 portable navigation, admin insert result and project-map rows: OK\n";
}
