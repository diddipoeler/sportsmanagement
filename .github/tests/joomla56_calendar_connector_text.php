<?php
/**
 * Standalone regression for Joomla 5/6 calendar connector text conversion.
 *
 * Corrupted imported text and unknown character encodings must not raise
 * PHP 8 warnings or prevent the calendar module from rendering.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);

/** Minimal parent for loading the isolated calendar runtime. */
class PHPCalendar
{
}

/** Minimal module configuration for the conversion-enabled branch. */
class SportsmanagementConnector
{
    public static object $params;
}

require dirname(__DIR__, 2)
    . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarRuntime.php';

$runtime = \Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarRuntime::class;

set_error_handler(static function (int $severity, string $message): never {
    throw new ErrorException($message, 0, $severity);
});

try {
    // Invalid connector values must never cause Array-to-string notices
    // or an object conversion error, even if conversion is disabled.
    foreach ([null, [], ['nested'], new stdClass()] as $invalid) {
        if ($runtime::jl_utf8_convert($invalid) !== '') {
            throw new RuntimeException('Invalid calendar connector text was cast to a string.');
        }
    }

    $stringable = new class implements Stringable {
        /** @return string Human-readable legacy field value. */
        public function __toString(): string
        {
            return 'Team name';
        }
    };

    if ($runtime::jl_utf8_convert($stringable) !== 'Team name'
        || $runtime::jl_utf8_convert('Unchanged') !== 'Unchanged') {
        throw new RuntimeException('Valid calendar connector text was discarded.');
    }

    SportsmanagementConnector::$params = new class {
        /** @return int Enable conversion for this test. */
        public function get(string $name, mixed $default = 0): int
        {
            return $name === 'convert' ? 1 : (int) $default;
        }
    };

    foreach ([[], new stdClass()] as $invalid) {
        if ($runtime::jl_utf8_convert($invalid) !== '') {
            throw new RuntimeException('Conversion-enabled calendar accepted malformed text.');
        }
    }

    // The normal Latin-1 to UTF-8 conversion must continue to work.
    if ($runtime::jl_utf8_convert("\xFC", 'ISO-8859-1', 'UTF-8') !== 'ü') {
        throw new RuntimeException('Valid calendar charset conversion failed.');
    }

    // Unknown encodings may emit warnings or throw ValueError, depending on
    // the PHP version and installed extensions; retain original content.
    if ($runtime::jl_utf8_convert('Original', 'invalid-charset-jsm', 'UTF-8') !== 'Original'
        || $runtime::jl_utf8_convert('Original', 'UTF-8', 'invalid-charset-jsm') !== 'Original') {
        throw new RuntimeException('Malformed calendar charset discarded source text.');
    }
} finally {
    restore_error_handler();
}

echo "Joomla 5/6 calendar connector text conversion OK\n";
