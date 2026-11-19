<?php
/**
 * Regression checks for truncated LZW PDF input and identity stream filtering.
 *
 * Usage: php .github/tests/joomla56-pdf-lzw-truncated-stream.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

defined('_JEXEC') || define('_JEXEC', 1);

require_once dirname(__DIR__, 2) . '/site/helpers/scoresheet/FilterLZW.php';
require_once dirname(__DIR__, 2) . '/site/helpers/scoresheet/FilterStandard.php';

set_error_handler(static function (int $severity, string $message): never {
    throw new ErrorException($message, 0, $severity);
});

try {
    $filter = new FilterLZW();
    foreach (['', "\x00", "\x80"] as $truncated) {
        if ($filter->decode($truncated) !== '') {
            throw new RuntimeException('Truncated LZW input was not handled safely.');
        }
    }

    $raw = new FilterStandard();
    foreach (['', "\x00\xFF\x01", 'SportsManagement'] as $source) {
        if ($raw->decode($raw->encode($source)) !== $source) {
            throw new RuntimeException('Uncompressed PDF stream was changed.');
        }
    }
} finally {
    restore_error_handler();
}

echo "Joomla 5/6 truncated LZW PDF regression checks passed.\n";
