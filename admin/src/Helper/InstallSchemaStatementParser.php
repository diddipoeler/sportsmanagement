<?php
/**
 * Joomla 5/6 SQL statement parser for SportsManagement installation schemas.
 *
 * @version    5.6.0
 * @author     diddipoeler, SportsManagement contributors
 * @copyright  Copyright (C) 2013-2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

/**
 * Split the bundled MySQL CREATE TABLE schema at statement delimiters.
 *
 * The legacy updater exploded every semicolon, which also breaks SQL string
 * defaults and future multiline declarations. The parser removes SQL comments
 * without losing quoted content and refuses unterminated SQL constructs.
 */
final class InstallSchemaStatementParser
{
    private const MAX_SQL_BYTES = 2097152;
    private const MAX_TABLES = 250;

    /**
     * Parse the component's bundled SQL and validate its fixed table scope.
     *
     * Only CREATE TABLE IF NOT EXISTS statements for SportsManagement tables
     * are accepted by this legacy schema-comparison screen. Other SQL belongs
     * in Joomla's versioned schema update mechanism, not this privileged view.
     *
     * @param string $sql Trusted contents of install.mysql.utf8.sql.
     * @return list<string> Complete CREATE TABLE statements without comments.
     * @throws \RuntimeException For malformed, unsupported or unbounded SQL.
     */
    public static function split(string $sql): array
    {
        $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql) ?? $sql;
        $length = strlen($sql);

        if ($length === 0 || $length > self::MAX_SQL_BYTES) {
            throw new \RuntimeException('SportsManagement install SQL is empty or too large.');
        }

        $queries = [];
        $current = '';
        $quote = null;

        for ($i = 0; $i < $length; ++$i) {
            $char = $sql[$i];
            $next = $i + 1 < $length ? $sql[$i + 1] : '';

            if ($quote !== null) {
                $current .= $char;

                // Preserve SQL backslash escapes as two literal bytes.
                if ($char === '\\' && $next !== '') {
                    $current .= $next;
                    ++$i;
                    continue;
                }

                if ($char === $quote) {
                    // SQL quote/backtick doubling represents an escaped quote.
                    if ($next === $quote) {
                        $current .= $next;
                        ++$i;
                    } else {
                        $quote = null;
                    }
                }

                continue;
            }

            if ($char === "'" || $char === '"' || $char === chr(96)) {
                $quote = $char;
                $current .= $char;
                continue;
            }

            if ($char === '/' && $next === '*') {
                $end = strpos($sql, '*/', $i + 2);

                if ($end === false) {
                    throw new \RuntimeException('Unterminated comment in SportsManagement SQL schema.');
                }

                $current .= ' ';
                $i = $end + 1;
                continue;
            }

            // A MySQL double-dash comment requires following whitespace.
            if (($char === '-' && $next === '-'
                    && ($i + 2 === $length || ctype_space($sql[$i + 2])))
                || $char === '#') {
                while ($i + 1 < $length && $sql[$i + 1] !== "\n"
                    && $sql[$i + 1] !== "\r") {
                    ++$i;
                }

                $current .= ' ';
                continue;
            }

            if ($char === ';') {
                self::appendTable($queries, $current);
                $current = '';
                continue;
            }

            $current .= $char;
        }

        if ($quote !== null || trim($current) !== '') {
            throw new \RuntimeException('Incomplete statement in SportsManagement SQL schema.');
        }

        if ($queries === []) {
            throw new \RuntimeException('SportsManagement SQL schema has no table definitions.');
        }

        return $queries;
    }

    /**
     * Add one complete, allowlisted table statement to the resulting schema.
     *
     * The importer performs subsequent column/index comparisons that only
     * understand SportsManagement CREATE TABLE definitions. Explicitly reject
     * any INSERT, ALTER, DROP, foreign table or malformed statement here.
     *
     * @param list<string> $queries Already accepted SQL declarations.
     * @param string $sql Candidate statement before the delimiter.
     * @return void Mutates the accepted statement list.
     * @throws \RuntimeException For unsupported statements or too many tables.
     */
    private static function appendTable(array &$queries, string $sql): void
    {
        $sql = trim($sql);

        if ($sql === '') {
            return;
        }

        if (preg_match(
            '/\ACREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+\x60#__sportsmanagement_[a-z0-9_]+\x60\s*\(/iD',
            $sql
        ) !== 1) {
            throw new \RuntimeException('Unsupported statement in SportsManagement SQL schema.');
        }

        if (count($queries) >= self::MAX_TABLES) {
            throw new \RuntimeException('SportsManagement schema contains too many tables.');
        }

        $queries[] = $sql;
    }
}
