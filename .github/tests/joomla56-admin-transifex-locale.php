<?php
/**
 * Verify administrator Transifex locale mapping and directory validation.
 *
 * Usage: php .github/tests/joomla56-admin-transifex-locale.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

defined('_JEXEC') || define('_JEXEC', 1);

require_once dirname(__DIR__, 2) . '/admin/helpers/transifex.php';

$class = sportsmanagementHelperTransifex::class;

set_error_handler(static function (int $severity, string $message): never {
    throw new ErrorException($message, 0, $severity);
});

try {
    // Mapping directions must not reuse one another's cached key orientation.
    for ($i = 0; $i < 4; $i++) {
        if ($class::getLangCode('de-DE') !== 'de_DE'
            || $class::getLangCode('de_DE', false, false) !== 'de-DE'
            || $class::getLangCode('de_DE', true, true) !== 'de-DE'
            || $class::getLangCode('de-DE', true, false) !== 'de_DE'
            || $class::getLangCode('does-not-exist') !== false
            || $class::getLangCode([], false, true) !== false) {
            throw new RuntimeException('Bidirectional Transifex locale cache regressed.');
        }
    }

    $data = [(object) ['slug' => 'sample']];
    foreach (['../en-GB', 'de-DE/../../tmp', "fr-FR\n", '', '../../de-DE'] as $invalid) {
        if ($class::updatelanguage($data, $invalid) !== $data) {
            throw new RuntimeException('Unsafe Transifex locale was not rejected.');
        }
    }
} finally {
    restore_error_handler();
}

echo "Joomla 5/6 administrator Transifex locale checks passed.\n";
