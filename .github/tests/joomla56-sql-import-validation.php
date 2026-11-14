<?php
/**
 * Standalone Joomla 5/6 SQL import regression checks.
 *
 * Exercises fail-closed parser validation, all-or-nothing preflight and valid
 * query execution with isolated Joomla/database stubs, without modifying a Joomla installation.
 *
 * Usage: php .github/tests/joomla56-sql-import-validation.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    /** Database contract stub for an isolated SQL import test. */
    interface DatabaseInterface
    {
        public function setQuery(string $statement): static;
        public function execute(): bool;
    }
}

namespace Joomla\CMS\Installer {
    /** SQL splitter stub testing modern and malformed statement shapes. */
    final class Installer
    {
        /**
         * Return valid or malformed statement shapes for the import helper.
         *
         * @param string $sql Non-empty SQL file content.
         * @return array<int,mixed> Statements using supported and unsupported types.
         */
        public static bool $malformed = true;

        public static function splitSql(string $sql): array
        {
            if (self::$malformed) {
                // Invalid parser records must abort before any SQL is executed.
                return [' SELECT 1 ', ['query' => ' SELECT 2 '], ['query' => []], new \stdClass(), '', ['query' => null]];
            }

            return [' SELECT 1 ', ['query' => ' SELECT 2 '], '', ['query' => '']];
        }
    }
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Helper/SqlImportHelper.php';

    use Diddipoeler\Component\SportsManagement\Administrator\Helper\SqlImportHelper;

    /** Captures SQL statements instead of executing database changes. */
    final class SmSqlImportTestDatabase implements \Joomla\Database\DatabaseInterface
    {
        public array $statements = [];

        /**
         * Record each valid SQL statement sent to the Joomla driver.
         *
         * @param string $statement Parsed SQL query.
         * @return static Fluent database stub.
         */
        public function setQuery(string $statement): static
        {
            $this->statements[] = $statement;
            return $this;
        }

        /**
         * Indicate that the synthetic query has been handled.
         *
         * @return bool Whether this synthetic database execution succeeded.
         */
        public function execute(): bool
        {
            return true;
        }
    }

    $db = new SmSqlImportTestDatabase();
    $file = tempnam(sys_get_temp_dir(), 'jsm-sql-');

    if ($file === false) {
        throw new \RuntimeException('Unable to create SQL import test fixture.');
    }

    try {
        file_put_contents($file, 'SELECT 1; SELECT 2;');

        // Reject a malformed installer parser result without executing even
        // the valid statements that precede the broken record.
        try {
            SqlImportHelper::importFile($db, $file);
            throw new \RuntimeException('The SQL importer accepted a malformed parser record.');
        } catch (\RuntimeException $exception) {
            if ($exception->getMessage() !== 'SQL import parser returned an invalid statement.') {
                throw $exception;
            }
        }

        if ($db->statements !== []) {
            throw new \RuntimeException('Malformed SQL parser output caused a partial import.');
        }

        // The supported Joomla string and query-array shapes must still
        // execute in their original order, ignoring only blank statements.
        \Joomla\CMS\Installer\Installer::$malformed = false;
        SqlImportHelper::importFile($db, $file);

        if ($db->statements !== ['SELECT 1', 'SELECT 2']) {
            throw new \RuntimeException('The SQL importer did not execute the valid statements.');
        }
    } finally {
        unlink($file);
    }

    echo "Joomla 5/6 SQL import validation passed.\n";
}
