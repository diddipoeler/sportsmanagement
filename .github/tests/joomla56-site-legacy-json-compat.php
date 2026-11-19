<?php
/**
 * Regression checks for bundled legacy JSON codec on PHP 8.
 *
 * Usage: php .github/tests/joomla56-site-legacy-json-compat.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

defined('_JEXEC') || define('_JEXEC', 1);

require_once dirname(__DIR__, 2) . '/site/helpers/JSON.php';

set_error_handler(static function (int $severity, string $message): never {
    throw new ErrorException($message, 0, $severity);
});

try {
    $codec = new JSMServices_JSON(SERVICES_JSON_LOOSE_TYPE);

    if ($codec->use !== SERVICES_JSON_LOOSE_TYPE) {
        throw new RuntimeException('PHP 8 JSON constructor ignored codec options.');
    }

    if ($codec->decode('{"team":"SportsManagement","score":2}') !== [
        'team' => 'SportsManagement',
        'score' => 2,
    ]) {
        throw new RuntimeException('Legacy JSON associative object decoding failed.');
    }

    $encoded = $codec->encode(['home', 'away', 2]);
    if (!is_string($encoded) || $codec->decode($encoded) !== ['home', 'away', 2]) {
        throw new RuntimeException('Legacy JSON array roundtrip failed.');
    }

    $stream = fopen('php://memory', 'rb');
    if ($stream === false) {
        throw new RuntimeException('Could not open JSON error fixture.');
    }

    try {
        $error = $codec->encode($stream);
        if (!$codec->isError($error) || !($error instanceof JSMServices_JSON_Error)) {
            throw new RuntimeException('Unsupported JSON input did not produce an error object.');
        }
    } finally {
        fclose($stream);
    }

    $codec->Services_JSON(0);
    if ($codec->use !== 0) {
        throw new RuntimeException('Original JSON initializer no longer works.');
    }
} finally {
    restore_error_handler();
}

echo "Joomla 5/6 bundled JSON compatibility checks passed.\n";
