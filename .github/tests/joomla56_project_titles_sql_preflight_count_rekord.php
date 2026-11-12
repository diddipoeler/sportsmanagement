<?php
/**
 * Verify Joomla 5/6 project titles, SQL importer preflight and record-count database selectors.
 *
 * Usage: php .github/tests/joomla56_project_titles_sql_preflight_count_rekord.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installer {
    /** Controlled replacement for Joomla's installer SQL parser. */
    final class Installer
    {
        public static array $statements = [];

        /**
         * Return the configured SQL statements without accessing a database.
         *
         * @param string $sql Input SQL script text.
         * @return array Fixture statements.
         */
        public static function splitSql(string $sql): array
        {
            return self::$statements;
        }
    }
}

namespace Joomla\Database {
    /** Minimal SQL driver contract for test execution. */
    interface DatabaseInterface
    {
        /**
         * Prepare the SQL query for the import.
         *
         * @param string $statement SQL statement.
         * @return self Fluent test driver.
         */
        public function setQuery(string $statement): self;

        /**
         * Execute the most recently prepared query.
         *
         * @return bool Successful test execution.
         */
        public function execute(): bool;
    }
}

namespace Joomla\Registry {
    /** Minimal Registry configuration for the record-count module. */
    final class Registry
    {
        /**
         * Construct the module settings fixture.
         *
         * @param array<string,mixed> $values Module setting values.
         */
        public function __construct(private array $values = [])
        {
        }

        /**
         * Read a module setting without Joomla's full registry.
         *
         * @param string $key Setting name.
         * @param mixed $default Fallback value.
         * @return mixed Configured value or fallback.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return array_key_exists($key, $this->values) ? $this->values[$key] : $default;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Helper/ProjectTitleHelper.php';
    require_once $root . '/admin/src/Helper/SqlImportHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_count_rekord/src/Helper/CountRekordHelper.php';

    $title = \Diddipoeler\Component\SportsManagement\Site\Helper\ProjectTitleHelper::class;
    $info = $title::createInfo('Fixtures');
    $info->divisionName = 'Premier';
    $info->personName = ['unexpected'];
    $info->team1Name = (object) ['malformed' => 'value'];
    $info->leagueName = 'League';

    if ($title::format($info, 1) !== 'Fixtures: Premier | League') {
        throw new \RuntimeException('Division-only project title or malformed legacy title field failed.');
    }

    $info->projectName = 'Season cup';
    if ($title::format($info, 0) !== 'Fixtures: Season cup - Premier') {
        throw new \RuntimeException('Valid project title segments have regressed.');
    }

    $database = new class implements \Joomla\Database\DatabaseInterface {
        public array $executed = [];

        /**
         * Save a SQL query prepared by the importer.
         *
         * @param string $statement SQL text.
         * @return self Test driver for fluent execution.
         */
        public function setQuery(string $statement): self
        {
            $this->executed[] = $statement;
            return $this;
        }

        /**
         * Indicate that prepared SQL was executed.
         *
         * @return bool Always successful for valid fixture SQL.
         */
        public function execute(): bool
        {
            return true;
        }
    };

    $sqlFile = tempnam(sys_get_temp_dir(), 'sm-joomla-sql-');
    if ($sqlFile === false) {
        throw new \RuntimeException('Unable to create SQL fixture.');
    }
    file_put_contents($sqlFile, 'SELECT 1; SELECT 2;');

    try {
        $importer = \Diddipoeler\Component\SportsManagement\Administrator\Helper\SqlImportHelper::class;
        \Joomla\CMS\Installer\Installer::$statements = ['SELECT 1', ['query' => ['malformed']]];

        try {
            $importer::importFile($database, $sqlFile);
            throw new \RuntimeException('Malformed later SQL must abort before any execution.');
        } catch (\RuntimeException $error) {
            if ($error->getMessage() === 'Malformed later SQL must abort before any execution.') {
                throw $error;
            }
        }

        if ($database->executed !== []) {
            throw new \RuntimeException('SQL preflight allowed a partial import before validation.');
        }

        \Joomla\CMS\Installer\Installer::$statements = ['SELECT 1', ['query' => 'SELECT 2']];
        $importer::importFile($database, $sqlFile);
        if ($database->executed !== ['SELECT 1', 'SELECT 2']) {
            throw new \RuntimeException('Valid SQL imports no longer execute in order.');
        }
    } finally {
        unlink($sqlFile);
    }

    $counter = new \Diddipoeler\Module\SportsManagementCountRekord\Site\Helper\CountRekordHelper();
    $selector = new \ReflectionMethod($counter, 'databaseSelector');
    foreach ([null, -1, 2, '2', ' 1', '1a', true, false, [], 1.0] as $invalid) {
        if ($selector->invoke(null, $invalid) !== null) {
            throw new \RuntimeException('Invalid record-count database selector was accepted.');
        }

        $params = new \Joomla\Registry\Registry([
            'jsm_stat_spielpaarungen' => 1,
            'cfg_which_database' => $invalid,
        ]);
        if ($counter->getData($params, (object) [], $database) !== []) {
            throw new \RuntimeException('Invalid record-count selector reached the query path.');
        }
    }

    foreach ([[0, 0], [1, 1], ['0001', 1], ['0000', 0]] as [$raw, $expected]) {
        if ($selector->invoke(null, $raw) !== $expected) {
            throw new \RuntimeException('Valid record-count database selector was rejected.');
        }
    }

    echo "Joomla 5/6 project titles, SQL preflight and record-count selectors: OK\n";
}
