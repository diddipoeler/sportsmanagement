<?php
/**
 * Additive Joomla 5/6 schema migration regressions without a SQL server.
 *
 * Usage: php .github/tests/joomla56_admin_additive_schema_migration.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace {
    \define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/admin/src/Helper/InstallSchemaStatementParser.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Helper/SchemaAdditiveMigrationHelper.php';

    /**
     * Simulate Joomla's schema metadata and SQL execution without a database.
     *
     * Only CREATE TABLE IF NOT EXISTS and ADD operations are accepted. SQL
     * updates modify fixture metadata so rerunning the upgrade can be tested
     * for idempotence rather than simply comparing static SQL strings.
     */
    final class AdditiveSchemaTestDatabase implements \Joomla\Database\DatabaseInterface
    {
        /** @var array<string,array{columns:array<string,object>,indexes:array<string,object>}> */
        public array $tables;
        /** @var list<string> */
        public array $executed = [];
        public bool $failAlter = false;
        private string $currentQuery = '';

        /**
         * Provide already-existing tables and their Joomla metadata.
         * @param array<string,array{columns:array<string,object>,indexes:array<string,object>}> $tables Fake table metadata.
         */
        public function __construct(array $tables)
        {
            $this->tables = $tables;
        }

        /**
         * Store a pending SQL command exactly as Joomla's setQuery does.
         * @param string $sql Bundled CREATE or planned additive ALTER SQL.
         * @return void
         */
        public function setQuery(string $sql): void
        {
            $this->currentQuery = $sql;
        }

        /**
         * Apply a fake table declaration or additive column/key mutation.
         *
         * @return bool Whether the simulated driver accepted the statement.
         */
        public function execute(): bool
        {
            $sql = $this->currentQuery;
            $this->executed[] = $sql;
            $helper = \Diddipoeler\Component\SportsManagement\Administrator\Helper\SchemaAdditiveMigrationHelper::class;

            if (str_starts_with($sql, 'CREATE')) {
                $table = trim($helper::describe($sql)['table'], chr(96));
                $this->tables[$table] ??= ['columns' => [], 'indexes' => []];
                return true;
            }

            if ($this->failAlter || !str_starts_with($sql, 'ALTER TABLE ')) {
                return false;
            }

            if (preg_match('/\AALTER TABLE \x60(#__sportsmanagement_[a-z0-9_]+)\x60 ADD COLUMN \x60([a-z0-9_]+)\x60 /i', $sql, $found) === 1) {
                $this->tables[$found[1]]['columns'][strtolower($found[2])] = (object) ['Field' => $found[2]];
                return true;
            }

            if (preg_match('/\AALTER TABLE \x60(#__sportsmanagement_[a-z0-9_]+)\x60 ADD (?:PRIMARY KEY|(?:UNIQUE |FULLTEXT |SPATIAL )?(?:KEY|INDEX))/i', $sql, $found) === 1) {
                $name = preg_match('/ ADD PRIMARY KEY\s*\(/i', $sql) === 1
                    ? 'PRIMARY'
                    : (preg_match('/ ADD (?:UNIQUE |FULLTEXT |SPATIAL )?(?:KEY|INDEX) \x60([^\x60]+)\x60/i', $sql, $match) === 1 ? $match[1] : '');
                $this->tables[$found[1]]['indexes'][strtolower($name)] = (object) ['Key_name' => $name];
                return $name !== '';
            }

            return false;
        }

        /**
         * Return Joomla's associative table-column metadata for one table.
         * @param string $table Component table name with its Joomla prefix tag.
         * @param bool $typeOnly Whether to request simplified column metadata.
         * @return array<string,object> Existing columns.
         */
        public function getTableColumns(string $table, bool $typeOnly = true): array
        {
            return $this->tables[$table]['columns'] ?? [];
        }

        /**
         * Return Joomla's index metadata using Key_name properties.
         * @param string $table Component table name with its Joomla prefix tag.
         * @return list<object> Existing index objects.
         */
        public function getTableKeys(string $table): array
        {
            return array_values($this->tables[$table]['indexes'] ?? []);
        }
    }

    /**
     * Stop a standalone migration check when an invariant does not hold.
     *
     * @param bool $condition Whether the SQL migration behaves safely.
     * @param string $message Failure description for GitHub Actions.
     * @return void
     */
    function requireSchemaSafety(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    $parser = \Diddipoeler\Component\SportsManagement\Administrator\Helper\InstallSchemaStatementParser::class;
    $helper = \Diddipoeler\Component\SportsManagement\Administrator\Helper\SchemaAdditiveMigrationHelper::class;
    $sql = file_get_contents(dirname(__DIR__, 2) . '/admin/sql/install.mysql.utf8.sql');
    requireSchemaSafety(is_string($sql), 'Bundled installation schema is missing.');
    $statements = $parser::split((string) $sql);

    requireSchemaSafety(count($statements) === 75, 'The full bundled schema should contain 75 tables.');

    $tables = [];
    $totalFields = 0;
    $totalIndexes = 0;

    foreach ($statements as $statement) {
        $table = $helper::describe($statement);
        $name = trim($table['table'], chr(96));
        requireSchemaSafety(!isset($tables[$name]), 'Duplicate SportsManagement table in install schema.');

        $columns = [];
        $indexes = [];

        foreach ($table['columns'] as $field => $definition) {
            $columns[$field] = (object) ['Field' => $field];
            ++$totalFields;
        }

        foreach ($table['indexes'] as $index => $definition) {
            $indexes[$index] = (object) ['Key_name' => $index];
            ++$totalIndexes;
        }

        $tables[$name] = ['columns' => $columns, 'indexes' => $indexes];
        $plan = $helper::plan($statement, $columns, array_values($indexes));

        requireSchemaSafety($plan === ['columns' => [], 'indexes' => []], 'Present schema objects unexpectedly scheduled for ALTER.');
    }

    requireSchemaSafety($totalFields > 500 && $totalIndexes > 200, 'Schema planner did not inspect all bundled columns and indexes.');

    $database = new AdditiveSchemaTestDatabase($tables);
    $summary = $helper::migrate($database, $statements);

    requireSchemaSafety($summary === ['tables' => 75, 'columns' => 0, 'indexes' => 0], 'Existing tables must not be altered.');
    requireSchemaSafety(count($database->executed) === 75, 'One no-op CREATE is required per table for missing-table support.');

    $club = '#__sportsmanagement_club';
    unset($tables[$club]['columns']['logo_big'], $tables[$club]['indexes']['country']);
    $database = new AdditiveSchemaTestDatabase($tables);
    $summary = $helper::migrate($database, $statements);

    requireSchemaSafety(
        $summary === ['tables' => 75, 'columns' => 1, 'indexes' => 1],
        'Missing columns and indexes were not added exactly once.'
    );

    $changes = array_values(array_filter($database->executed, static fn (string $sql): bool => str_starts_with($sql, 'ALTER TABLE ')));

    requireSchemaSafety(
        count($changes) === 2
            && str_contains(implode("\n", $changes), 'ADD COLUMN ')
            && str_contains(implode("\n", $changes), ' ADD KEY ')
            && preg_match('/\b(?:DROP|CHANGE|MODIFY)\b/i', implode("\n", $changes)) !== 1,
        'Generated SQL must contain only additive column and index mutations.'
    );

    $second = $helper::migrate($database, $statements);
    requireSchemaSafety(
        $second === ['tables' => 75, 'columns' => 0, 'indexes' => 0],
        'A second schema migration must not change already reconciled tables.'
    );

    $broken = new AdditiveSchemaTestDatabase($tables);
    $broken->failAlter = true;

    try {
        $helper::migrate($broken, $statements);
        throw new \RuntimeException('Failed SQL ALTER did not abort the migration.');
    } catch (\RuntimeException $error) {
        if (!str_contains($error->getMessage(), 'ADD statement failed')) {
            throw $error;
        }
    }

    $quoted = chr(96);
    $fixture = 'CREATE TABLE IF NOT EXISTS ' . $quoted . '#__sportsmanagement_fixture' . $quoted
        . ' (' . $quoted . 'name' . $quoted . " VARCHAR(150) DEFAULT 'hello, (sports)', "
        . $quoted . 'code' . $quoted . " VARCHAR(16) DEFAULT 'a,b', "
        . 'UNIQUE INDEX ' . $quoted . 'club, season' . $quoted . ' (' . $quoted . 'name' . $quoted . ' ASC, ' . $quoted . 'code' . $quoted . ' ASC)) ENGINE=MyISAM';
    $description = $helper::describe($fixture);

    requireSchemaSafety(
        count($description['columns']) === 2
            && isset($description['indexes']['club, season'])
            && str_contains($description['columns']['name'], "'hello, (sports)'"),
        'A comma inside an SQL string or composite index was split incorrectly.'
    );

    foreach ([
        'DROP TABLE ' . $quoted . '#__sportsmanagement_club' . $quoted,
        'CREATE TABLE ' . $quoted . 'users' . $quoted . ' (id INT)',
        'CREATE TABLE IF NOT EXISTS ' . $quoted . '#__sportsmanagement_bad' . $quoted . ' (FOREIGN KEY (id) REFERENCES users(id))',
    ] as $invalid) {
        try {
            $helper::describe($invalid);
            throw new \RuntimeException('Unsafe schema declaration was accepted.');
        } catch (\RuntimeException $exception) {
            if ($exception->getMessage() === 'Unsafe schema declaration was accepted.') {
                throw $exception;
            }
        }
    }

    echo "Joomla 5/6 additive schema migrations passed: 75 tables, {$totalFields} columns, {$totalIndexes} indexes.\n";
}
