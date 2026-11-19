<?php
/**
 * Regression tests for empty and binary PDF stream filters on PHP 8.
 *
 * Usage: php .github/tests/joomla56-pdf-empty-stream-filters.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

defined('_JEXEC') || define('_JEXEC', 1);

require_once dirname(__DIR__, 2) . '/site/helpers/scoresheet/FilterASCIIHex.php';
require_once dirname(__DIR__, 2) . '/site/helpers/scoresheet/FilterFlate.php';

set_error_handler(static function (int $severity, string $message): never {
    throw new ErrorException($message, 0, $severity);
});

try {
    $hex = new FilterASCIIHex();
    foreach (['', "\x00\x01\xFE\xFF", 'SportsManagement'] as $sample) {
        if ($hex->decode($hex->encode($sample)) !== $sample) {
            throw new RuntimeException('ASCIIHex stream roundtrip failed.');
        }
    }

    $flate = new FilterFlate();
    foreach (['', "\x00\x01\xFE\xFF", str_repeat('fixture;', 100)] as $sample) {
        $encoded = $flate->encode($sample);
        if (!is_string($encoded) || $flate->decode($encoded) !== $sample) {
            throw new RuntimeException('Flate stream roundtrip failed.');
        }
    }
} finally {
    restore_error_handler();
}

echo "Joomla 5/6 PDF stream filter checks passed.\n";
