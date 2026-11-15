<?php
/**
 * Joomla 5/6 SQL schema parser and administrator import migration regression.
 *
 * Usage: php .github/tests/joomla56_admin_install_schema_parser.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

\define('_JEXEC', 1);

require_once dirname(__DIR__, 2) . '/admin/src/Helper/InstallSchemaStatementParser.php';

use Diddipoeler\Component\SportsManagement\Administrator\Helper\InstallSchemaStatementParser;

/**
 * Require the SQL parser to refuse a malformed or foreign statement.
 *
 * @param string $sql SQL fixture which must not be allowed in the installer.
 * @param string $reason Expected rejection condition.
 * @return void
 * @throws RuntimeException If unsafe SQL is accepted.
 */
function expectSchemaParserRejection(string $sql, string $reason): void
{
    try {
        InstallSchemaStatementParser::split($sql);
    } catch (\RuntimeException $exception) {
        return;
    }

    throw new \RuntimeException('SQL schema parser accepted ' . $reason . '.');
}

$root = dirname(__DIR__, 2);
$sql = file_get_contents($root . '/admin/sql/install.mysql.utf8.sql');

if (!is_string($sql)) {
    throw new \RuntimeException('Bundled installation SQL is unavailable.');
}

$tables = InstallSchemaStatementParser::split($sql);

if (count($tables) !== 75) {
    throw new \RuntimeException('Expected 75 original SportsManagement CREATE TABLE statements, got ' . count($tables));
}

$seen = [];
foreach ($tables as $index => $statement) {
    if (preg_match('/\ACREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+\x60(#__sportsmanagement_[a-z0-9_]+)\x60\s*\(/i', $statement, $match) !== 1) {
        throw new \RuntimeException('Bundled schema contains an unsupported statement: ' . $index);
    }

    if (isset($seen[$match[1]])) {
        throw new \RuntimeException('Bundled schema contains the same table more than once.');
    }

    $seen[$match[1]] = true;
}

if (!isset($seen['#__sportsmanagement_club'], $seen['#__sportsmanagement_match'])) {
    throw new \RuntimeException('Bundled schema lost core club or match tables.');
}

$q = chr(96);
$one = 'CREATE TABLE IF NOT EXISTS ' . $q . '#__sportsmanagement_semicolon' . $q;
$two = 'CREATE TABLE IF NOT EXISTS ' . $q . '#__sportsmanagement_second' . $q;
$fixture = "-- comment containing a ; and SQL keywords\n"
    . $one . " (name VARCHAR(255) DEFAULT 'semi;colon', path TEXT DEFAULT \"double;quoted\");\n"
    . "/* block ; comment */\n"
    . '# ignored comment with ;' . "\n"
    . $two . " (name TEXT DEFAULT 'one'';two');\n";

$statements = InstallSchemaStatementParser::split($fixture);

if (count($statements) !== 2
    || !str_contains($statements[0], "'semi;colon'")
    || !str_contains($statements[0], '"double;quoted"')
    || !str_contains($statements[1], "'one'';two'")
    || str_contains($statements[0], '-- comment')
    || str_contains($statements[1], 'block ; comment')) {
    throw new \RuntimeException('SQL parser split quoted defaults or retained SQL comments.');
}

expectSchemaParserRejection('DROP TABLE ' . $q . '#__sportsmanagement_club' . $q . ';', 'DROP TABLE');
expectSchemaParserRejection('ALTER TABLE ' . $q . '#__sportsmanagement_club' . $q . ' ADD id INT;', 'ALTER TABLE');
expectSchemaParserRejection('CREATE TABLE IF NOT EXISTS ' . $q . 'joomla_users' . $q . ' (id INT);', 'a foreign table');
expectSchemaParserRejection($one . " (text TEXT DEFAULT 'unfinished);", 'unterminated quoted SQL');
expectSchemaParserRejection($one . ' (id INT)', 'SQL lacking a final delimiter');
expectSchemaParserRejection($one . ' (id INT); /* unfinished comment', 'unterminated SQL comment');
expectSchemaParserRejection(str_repeat('x', 2097153), 'oversized SQL');
expectSchemaParserRejection('', 'empty SQL');
expectSchemaParserRejection(str_repeat($one . ' (id INT);', 251), 'an excessive number of tables');

$updater = file_get_contents($root . '/admin/assets/updates/jsm_update_db_tables.php');

if (!is_string($updater)
    || !str_contains($updater, 'InstallSchemaStatementParser::split($imports)')
    || !str_contains($updater, '(new SportsManagementDatabaseResolver())->resolve(null, $defaultDatabase)')
    || !str_contains($updater, "authorise('core.admin', 'com_sportsmanagement')")
    || !str_contains($updater, "->checkToken('post')")
    || str_contains($updater, 'sportsmanagementHelper::getDBConnection()')
    || str_contains($updater, "explode(';', \$imports)")
    || str_contains($updater, '$db->splitSql(')) {
    throw new \RuntimeException('Administrator database update lost native Joomla SQL and security contracts.');
}

echo "Joomla 5/6 administrator SQL schema parser regressions passed (75 bundled tables).\n";
