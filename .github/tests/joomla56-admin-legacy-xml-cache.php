<?php
/**
 * Validate the legacy XML parser's PHP 8 callbacks and cache isolation.
 *
 * Usage: php .github/tests/joomla56-admin-legacy-xml-cache.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

defined('_JEXEC') || define('_JEXEC', 1);

if (!function_exists('xml_parser_create')) {
    fwrite(STDERR, "PHP's XML extension is required.\n");
    exit(2);
}

require_once dirname(__DIR__, 2) . '/admin/helpers/xml_parser.php';

$fixture = tempnam(sys_get_temp_dir(), 'jsm-legacy-xml-');
if ($fixture === false) {
    throw new RuntimeException('Could not create legacy XML fixture.');
}
$cache = $fixture . '.cache';

try {
    $xml = '<root><club id="12">Home</club><club id="13">Away</club></root>';
    file_put_contents($fixture, $xml);

    $parser = new xml_parser_class();
    if ($parser->Parse($xml, true) !== ''
        || ($parser->structure['0']['Tag'] ?? null) !== 'root'
        || ($parser->structure['0,0']['Tag'] ?? null) !== 'club'
        || ($parser->structure['0,0,0'] ?? null) !== 'Home') {
        throw new RuntimeException('Legacy XML callbacks lost nested elements.');
    }

    $parsed = null;
    if (XMLParseFile($parsed, $fixture, 0, $cache) !== ''
        || !$parsed instanceof xml_parser_class
        || ($parsed->structure['0']['Tag'] ?? null) !== 'root') {
        throw new RuntimeException('Legacy XML cache could not be produced.');
    }

    $cached = null;
    if (XMLParseFile($cached, $fixture, 0, $cache) !== ''
        || !$cached instanceof xml_parser_class
        || ($cached->structure['0,0,0'] ?? null) !== 'Home') {
        throw new RuntimeException('Legacy XML cache read changed XML values.');
    }

    // Cache objects of unrelated classes must never be instantiated.
    file_put_contents($cache, serialize(new stdClass()));
    touch($cache, time() + 10);
    $rechecked = null;
    $error = XMLParseFile($rechecked, $fixture, 0, $cache);
    if ($error === '' || !str_contains($error, 'valid cache object')) {
        throw new RuntimeException('Untrusted XML cache class was accepted.');
    }
} finally {
    @unlink($cache);
    @unlink($fixture);
}

echo "Joomla 5/6 legacy XML parser and cache checks passed.\n";
