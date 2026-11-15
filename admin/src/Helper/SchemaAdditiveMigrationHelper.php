<?php
/**
 * Additive Joomla 5/6 database schema reconciliation for SportsManagement.
 *
 * @version    5.6.0
 * @author     diddipoeler, SportsManagement contributors
 * @copyright  Copyright (C) 2013-2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/**
 * Inspect the bundled CREATE TABLE definitions without destructive ALTERs.
 *
 * The historical script compared index names incorrectly and attempted DROP
 * and CHANGE statements on production databases. This replacement only adds
 * missing columns and named indexes. Existing definitions are left intact;
 * incompatible existing columns must be addressed by a versioned SQL update.
 */
final class SchemaAdditiveMigrationHelper
{
    /**
     * Extract an allowlisted table name, columns, and indexes from CREATE SQL.
     *
     * Splits comma-separated definitions only at the top level, preserving
     * VARCHAR length tuples, composite indexes, and quoted SQL defaults
     * containing commas or parentheses.
     *
     * @param string $statement One approved CREATE TABLE IF NOT EXISTS command.
     * @return array{table:string,columns:array<string,string>,indexes:array<string,string>} Schema metadata.
     * @throws \RuntimeException When an unsupported definition is encountered.
     */
    public static function describe(string $statement): array
    {
        if (preg_match(
            '/\ACREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+(\x60#__sportsmanagement_[a-z0-9_]+\x60)\s*\(/i',
            $statement,
            $match
        ) !== 1) {
            throw new \RuntimeException('Unsupported SportsManagement table declaration.');
        }

        $table = $match[1];
        $opening = strlen($match[0]) - 1;
        $body = self::tableBody($statement, $opening);
        $columns = [];
        $indexes = [];

        foreach (self::splitDefinitions($body) as $definition) {
            if (preg_match('/\A\x60([a-z0-9_]+)\x60\s+/i', $definition, $field) === 1) {
                $columns[strtolower($field[1])] = $definition;
                continue;
            }

            if (preg_match('/\APRIMARY\s+KEY\s*\(/i', $definition) === 1) {
                $indexes['primary'] = $definition;
                continue;
            }

            if (preg_match(
                '/\A(?:(?:UNIQUE|FULLTEXT|SPATIAL)\s+)?(?:KEY|INDEX)\s+\x60([^\x60]+)\x60\s*\(/i',
                $definition,
                $key
            ) === 1) {
                $indexName = strtolower($key[1]);

                if ($indexName === '' || strlen($indexName) > 128
                    || preg_match('/[\x00-\x1f\x7f]/', $indexName)) {
                    throw new \RuntimeException('Invalid SportsManagement schema index name.');
                }

                $indexes[$indexName] = $definition;
                continue;
            }

            throw new \RuntimeException('Unsupported column or index in SportsManagement schema.');
        }

        if ($columns === []) {
            throw new \RuntimeException('SportsManagement schema contains an empty table.');
        }

        return ['table' => $table, 'columns' => $columns, 'indexes' => $indexes];
    }

    /**
     * Build safe ADD-only SQL from a CREATE statement and Joomla metadata.
     *
     * Existing columns and indexes are recognised case-insensitively from
     * either the associative map or Joomla's column/index metadata objects.
     * The returned queries never modify or remove an existing field or key.
     *
     * @param string $statement Bundled first-party CREATE TABLE SQL.
     * @param array $existingColumns Joomla getTableColumns metadata.
     * @param array $existingKeys Joomla getTableKeys metadata.
     * @return array{columns:list<string>,indexes:list<string>} Missing ADD statements.
     */
    public static function plan(string $statement, array $existingColumns, array $existingKeys): array
    {
        $table = self::describe($statement);
        $knownColumns = [];
        $knownIndexes = [];

        foreach ($existingColumns as $name => $metadata) {
            if (is_string($name) && $name !== '') {
                $knownColumns[strtolower($name)] = true;
            }

            if (is_object($metadata)) {
                $field = $metadata->Field ?? $metadata->field ?? null;

                if (is_string($field) && $field !== '') {
                    $knownColumns[strtolower($field)] = true;
                }
            }
        }

        foreach ($existingKeys as $name => $metadata) {
            if (is_string($name) && $name !== '') {
                $knownIndexes[strtolower($name)] = true;
            }

            if (is_object($metadata)) {
                $keyName = $metadata->Key_name ?? $metadata->key_name ?? null;

                if (is_string($keyName) && $keyName !== '') {
                    $knownIndexes[strtolower($keyName)] = true;
                }
            }
        }

        $columns = [];
        $indexes = [];

        foreach ($table['columns'] as $name => $definition) {
            if (!isset($knownColumns[$name])) {
                $columns[] = 'ALTER TABLE ' . $table['table'] . ' ADD COLUMN ' . $definition;
            }
        }

        foreach ($table['indexes'] as $name => $definition) {
            if (!isset($knownIndexes[$name])) {
                $indexes[] = 'ALTER TABLE ' . $table['table'] . ' ADD ' . $definition;
            }
        }

        return ['columns' => $columns, 'indexes' => $indexes];
    }

    /**
     * Create missing component tables and add only absent schema objects.
     *
     * Statements are fixed by the bundled SQL allowlist; no request parameter
     * supplies database identifiers. Each unsuccessful statement terminates
     * the migration, allowing the administrator to investigate before retrying
     * rather than incorrectly reporting a fully applied schema update.
     *
     * @param DatabaseInterface $database Active authorised Joomla connection.
     * @param list<string> $statements Validated bundled CREATE TABLE statements.
     * @return array{tables:int,columns:int,indexes:int} Successfully processed changes.
     * @throws \RuntimeException When SQL execution or metadata retrieval fails.
     */
    public static function migrate(DatabaseInterface $database, array $statements): array
    {
        $summary = ['tables' => 0, 'columns' => 0, 'indexes' => 0];

        foreach ($statements as $statement) {
            $table = self::describe($statement);

            $database->setQuery($statement);

            if ($database->execute() === false) {
                throw new \RuntimeException('Could not create SportsManagement table if missing.');
            }

            $name = trim($table['table'], chr(96));
            $columns = $database->getTableColumns($name, false);
            $keys = $database->getTableKeys($name);

            if (!is_array($columns) || !is_array($keys)) {
                throw new \RuntimeException('SportsManagement table metadata could not be loaded.');
            }

            $changes = self::plan($statement, $columns, $keys);

            foreach (['columns', 'indexes'] as $type) {
                foreach ($changes[$type] as $sql) {
                    $database->setQuery($sql);

                    if ($database->execute() === false) {
                        throw new \RuntimeException('SportsManagement schema ADD statement failed.');
                    }

                    ++$summary[$type];
                }
            }

            ++$summary['tables'];
        }

        return $summary;
    }

    /**
     * Locate the balanced table body after the first opening parenthesis.
     *
     * @param string $statement Original trusted CREATE TABLE SQL.
     * @param int $opening Offset of the table's opening parenthesis.
     * @return string SQL declarations without the enclosing parentheses.
     * @throws \RuntimeException For an unmatched parenthesis or quote.
     */
    private static function tableBody(string $statement, int $opening): string
    {
        $depth = 0;
        $quote = null;
        $length = strlen($statement);

        for ($i = $opening; $i < $length; ++$i) {
            $char = $statement[$i];
            $next = $i + 1 < $length ? $statement[$i + 1] : '';

            if ($quote !== null) {
                if ($char === '\\' && $next !== '') {
                    ++$i;
                    continue;
                }

                if ($char === $quote) {
                    if ($next === $quote) {
                        ++$i;
                    } else {
                        $quote = null;
                    }
                }

                continue;
            }

            if ($char === "'" || $char === '"' || $char === chr(96)) {
                $quote = $char;
                continue;
            }

            if ($char === '(') {
                ++$depth;
                continue;
            }

            if ($char === ')' && --$depth === 0) {
                return substr($statement, $opening + 1, $i - $opening - 1);
            }
        }

        throw new \RuntimeException('Unbalanced SportsManagement CREATE TABLE definition.');
    }

    /**
     * Split column/index definitions without splitting nested SQL expressions.
     *
     * @param string $body CREATE TABLE declarations inside outer parentheses.
     * @return list<string> Individual declarations in original SQL order.
     * @throws \RuntimeException When SQL contains an unmatched quote or bracket.
     */
    private static function splitDefinitions(string $body): array
    {
        $parts = [];
        $part = '';
        $depth = 0;
        $quote = null;
        $length = strlen($body);

        for ($i = 0; $i < $length; ++$i) {
            $char = $body[$i];
            $next = $i + 1 < $length ? $body[$i + 1] : '';

            if ($quote !== null) {
                $part .= $char;

                if ($char === '\\' && $next !== '') {
                    $part .= $next;
                    ++$i;
                    continue;
                }

                if ($char === $quote) {
                    if ($next === $quote) {
                        $part .= $next;
                        ++$i;
                    } else {
                        $quote = null;
                    }
                }

                continue;
            }

            if ($char === "'" || $char === '"' || $char === chr(96)) {
                $quote = $char;
                $part .= $char;
                continue;
            }

            if ($char === '(') {
                ++$depth;
            } elseif ($char === ')') {
                --$depth;

                if ($depth < 0) {
                    throw new \RuntimeException('Unexpected closing parenthesis in table definition.');
                }
            }

            if ($char === ',' && $depth === 0) {
                if (trim($part) !== '') {
                    $parts[] = trim($part);
                }

                $part = '';
                continue;
            }

            $part .= $char;
        }

        if ($depth !== 0 || $quote !== null) {
            throw new \RuntimeException('Unbalanced SQL declaration in SportsManagement schema.');
        }

        if (trim($part) !== '') {
            $parts[] = trim($part);
        }

        return $parts;
    }
}
