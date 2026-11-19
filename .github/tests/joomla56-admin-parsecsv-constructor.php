<?php
/**
 * Regression checks for the legacy administrator CSV parser constructor on PHP 8.
 *
 * Usage: php .github/tests/joomla56-admin-parsecsv-constructor.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

defined('_JEXEC') || define('_JEXEC', 1);

require_once dirname(__DIR__, 2) . '/admin/helpers/parsecsv.lib.php';

set_error_handler(static function (int $severity, string $message): never {
    throw new ErrorException($message, 0, $severity);
});

try {
    $parser = new parseCSV(null, 2, 10, null);

    if ($parser->offset !== 2 || $parser->limit !== 10) {
        throw new RuntimeException('Legacy CSV constructor did not initialise parser options.');
    }

    // The historic PHP 4 method remains callable by older import workflows.
    $parser->parseCSV(null, 3, 5, null);

    if ($parser->offset !== 3 || $parser->limit !== 5) {
        throw new RuntimeException('Legacy CSV constructor alias regressed.');
    }

    $parser = new parseCSV(null, null, null, ['status' => 'active']);

    if ($parser->conditions !== ['status' => 'active']) {
        throw new RuntimeException('Legacy CSV conditions were not stored.');
    }
    // A quoted field followed by whitespace at EOF must not access the
    // nonexistent next byte, nor need the deprecated Joomla Factory API.
    $parser = new parseCSV();
    $parser->heading = false;
    $parser->parse_string('"fixture"  ');

} finally {
    restore_error_handler();
}

echo "Joomla 5/6 legacy CSV constructor regression checks passed.\n";
