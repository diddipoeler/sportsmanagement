<?php
/**
 * Standalone Joomla 5/6 SQL import regression checks.
 *
 * Exercises the administrator import helper with an isolated installer parser
 * and database stub, without modifying a Joomla installation.
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
         * Return valid and malformed statement shapes for the import helper.
         *
         * @param string $sql Non-empty SQL file content.
         * @return array<int,mixed> Statements using supported and unsupported types.
         */
        public static function splitSql(string $sql): array
        {
            return [' SELECT 1 ', ['query' => ' SELECT 2 '], ['query' => []], new \stdClass(), '', ['query' => null]];
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
        SqlImportHelper::importFile($db, $file);

        if ($db->statements !== ['SELECT 1', 'SELECT 2']) {
            throw new \RuntimeException('SQL importer did not skip malformed statements.');
        }
    } finally {
        unlink($file);
    }

    echo "Joomla 5/6 SQL import validation passed.\n";
}
