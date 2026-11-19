<?php
/**
 * Check legacy Sofee XML imports under PHP 8 without fatal process exits.
 *
 * Usage: php .github/tests/joomla56-admin-sofee-xml-parser.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU Lesser General Public License version 2.1 or later
 */
declare(strict_types=1);

defined('_JEXEC') || define('_JEXEC', 1);

if (!function_exists('xml_parse_into_struct')) {
    fwrite(STDERR, "The PHP XML extension is required.\n");
    exit(2);
}

require_once dirname(__DIR__, 2) . '/admin/helpers/SofeeXmlParser.php';

$parser = new SofeeXmlParser();
$parser->parseString('<root><club id="42">Team</club></root>');
$tree = $parser->getTree();

if (($tree['root']['club']['value'] ?? null) !== 'Team'
    || ($tree['root']['club']['id'] ?? null) !== '42') {
    throw new RuntimeException('Sofee XML element hierarchy or attributes changed.');
}

$parser->parseString('<second><value>2</value></second>');
$replacement = $parser->getTree();
if (!isset($replacement['second']) || isset($replacement['root'])) {
    throw new RuntimeException('Sofee XML parser reused stale import data.');
}

foreach (['', '<root>', '<root><broken></root>'] as $broken) {
    try {
        $parser->parseString($broken);
    } catch (RuntimeException) {
        if ($parser->parser !== null || $parser->getTree() !== []) {
            throw new RuntimeException('Sofee XML parser did not reset after an error.');
        }
        continue;
    }

    throw new RuntimeException('Invalid Sofee XML input was accepted.');
}

try {
    $parser->parseFile(sys_get_temp_dir() . '/missing-sportsmanagement-sofee-xml-12345.xml');
    throw new RuntimeException('Nonexistent Sofee XML input was accepted.');
} catch (RuntimeException $exception) {
    if ($exception->getMessage() === 'Nonexistent Sofee XML input was accepted.') {
        throw $exception;
    }
}

echo "Joomla 5/6 Sofee XML import checks passed.\n";
