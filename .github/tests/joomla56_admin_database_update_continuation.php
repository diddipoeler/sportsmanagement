<?php
/**
 * Regression contracts for Joomla 5/6 administrator database update stages.
 *
 * Usage: php .github/tests/joomla56_admin_database_update_continuation.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

/**
 * Check one migration safety invariant and report an actionable failure.
 *
 * @param bool $condition Whether the required guard remains in the script.
 * @param string $message Regression failure description.
 * @return void
 * @throws RuntimeException When the migration violates the contract.
 */
function assertDatabaseUpdateSafety(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$path = dirname(__DIR__, 2) . '/admin/assets/updates/jsm_update_db_tables.php';
$source = file_get_contents($path);

assertDatabaseUpdateSafety(is_string($source), 'Cannot inspect database migration source.');

$source = (string) $source;

// Initial and continuation submissions both require an explicit POST token.
// No stage may be activated by an unconfirmed GET or by JavaScript reload.
assertDatabaseUpdateSafety(
    str_contains($source, "->post->getInt('run_update', 0)")
        && str_contains($source, "->checkToken('post')")
        && str_contains($source, "authorise('core.admin', 'com_sportsmanagement')")
        && substr_count($source, "HTMLHelper::_('form.token')") >= 2
        && str_contains($source, '<input type="hidden" name="run_update" value="1">')
        && !str_contains($source, 'location.reload(true)'),
    'Schema update stages must require component administrator rights and POST confirmation.'
);

// Progress state may only be the two supported stages, or zero when reset.
// The old unchecked increment could overflow or continue a corrupt session.
assertDatabaseUpdateSafety(
    str_contains($source, 'function getUpdatePart(): int')
        && str_contains($source, 'function setUpdatePart(int $val = 1): void')
        && str_contains($source, "in_array(\$stage, [0, 1, 2, '0', '1', '2'], true)")
        && str_contains($source, 'min(2, getUpdatePart() + 1)')
        && str_contains($source, "setUpdatePart(0);"),
    'Database migration stages no longer clamp persisted user state.'
);

// The schema must exist before SQL parsing and the unused legacy splitSql
// method must never be called on Joomla 5/6 database drivers.
assertDatabaseUpdateSafety(
    str_contains($source, "if (!is_string(\$source) || trim(\$source) === '')")
        && str_contains($source, 'InstallSchemaStatementParser::split($source)')
        && str_contains($source, 'SchemaAdditiveMigrationHelper::migrate($database, $statements)')
        && !str_contains($source, '$db->splitSql('),
    'Database update no longer validates its bundled schema or calls a removed API.'
);

// Verify the four requested first-party attribution tags remain present.
foreach (['@version', '@author', '@copyright', '@license'] as $tag) {
    assertDatabaseUpdateSafety(
        str_contains(substr($source, 0, 900), $tag),
        'Database update header is missing the required ' . $tag . ' metadata.'
    );
}

echo "Joomla 5/6 administrator database update continuation contracts passed.\n";
