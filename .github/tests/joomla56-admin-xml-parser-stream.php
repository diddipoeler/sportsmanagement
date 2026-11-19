<?php
/**
 * Validate the legacy administrator XML parser's safe stream completion.
 *
 * Usage: php .github/tests/joomla56-admin-xml-parser-stream.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

defined('_JEXEC') || define('_JEXEC', 1);

if (!function_exists('xml_parser_create')) {
    fwrite(STDERR, "PHP XML parser extension is required for this test.\n");
    exit(2);
}

require_once dirname(__DIR__, 2) . '/admin/helpers/XMLParser.class.php';
require_once dirname(__DIR__, 2) . '/admin/helpers/parser_php5.php';

$fixture = tempnam(sys_get_temp_dir(), 'jsm-xml-');
if ($fixture === false) {
    throw new RuntimeException('Could not create XML test fixture.');
}

set_error_handler(static function (int $severity, string $message): never {
    throw new ErrorException($message, 0, $severity);
});

try {
    // PHP 8's xml extension defines the built-in XMLParser class.
    // The two bundled adapters must use distinct SportsManagement names.
    if (!class_exists(SportsManagementLegacyXmlParser::class)
        || !class_exists(SportsManagementLegacyPhp5XmlParser::class)) {
        throw new RuntimeException('Legacy XML parser class isolation failed.');
    }

    $parser = new SportsManagementLegacyXmlParser();
    $xml = '<ROOT><ITEM>Fixture</ITEM></ROOT>';
    file_put_contents($fixture, $xml);
    $result = $parser->parse($fixture);

    if (($result[0]['name'] ?? null) !== 'ROOT'
        || ($result[0]['child'][0]['name'] ?? null) !== 'ITEM'
        || ($result[0]['child'][0]['content'] ?? null) !== 'Fixture') {
        throw new RuntimeException('XML parser changed the expected legacy element hierarchy.');
    }

    // Ensure both legacy parsers can coexist with the XML extension's
    // own XMLParser class without any class redeclaration fatal error.
    $objectParser = new SportsManagementLegacyPhp5XmlParser('<root><item>Fixture</item></root>');
    $objectParser->Parse();

    if (($objectParser->document->tagName ?? null) !== 'root'
        || ($objectParser->document->item[0]->tagData ?? null) !== 'Fixture') {
        throw new RuntimeException('Legacy PHP5 XML object parser output regressed.');
    }

    // Check both exact-chunk EOF and incomplete trailing XML markup.
    file_put_contents($fixture, '<ROOT>' . str_repeat(' ', 4096 - strlen('<ROOT></ROOT>')) . '</ROOT>');
    if (strlen((string) file_get_contents($fixture)) !== 4096
        || ($parser->parse($fixture)[0]['name'] ?? null) !== 'ROOT') {
        throw new RuntimeException('Exact-chunk XML parsing regressed.');
    }

    file_put_contents($fixture, '<ROOT>');
    try {
        $parser->parse($fixture);
        throw new RuntimeException('Truncated XML was silently accepted.');
    } catch (RuntimeException $e) {
        if ($e->getMessage() === 'Truncated XML was silently accepted.') {
            throw $e;
        }
    }

    try {
        $parser->parse($fixture . '.missing');
        throw new RuntimeException('Missing XML file was silently accepted.');
    } catch (RuntimeException $e) {
        if ($e->getMessage() === 'Missing XML file was silently accepted.') {
            throw $e;
        }
    }
} finally {
    restore_error_handler();
    @unlink($fixture);
}

echo "Joomla 5/6 administrator XML streaming checks passed.\n";
