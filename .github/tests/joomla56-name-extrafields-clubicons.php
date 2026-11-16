<?php
/**
 * Regression checks for site initials, safe extra-field writes and shared module assets.
 *
 * Run from the repository root:
 * php .github/tests/joomla56-name-extrafields-clubicons.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;

define('_JEXEC', 1);

$root = dirname(__DIR__, 2);
require_once $root . '/site/src/Helper/PersonNameFormatter.php';

$invalid = "Bad\xC3\x28";

if (PersonNameFormatter::format(null, $invalid, '', 'Doe', 8) !== 'Doe') {
    throw new RuntimeException('Malformed UTF-8 generated a broken frontend initial.');
}

if (PersonNameFormatter::format(null, 'Änne', '', 'Doe', 8) !== 'Ä. Doe') {
    throw new RuntimeException('Valid UTF-8 names must retain their initials.');
}

$extraFile = $root . '/admin/src/Helper/ExtraFieldsSaveHelper.php';
$extraSource = file_get_contents($extraFile);

if (!is_string($extraSource)) {
    throw new RuntimeException('Unable to load administrator extra-field helper source.');
}

// Verify that invalid UTF-8 cannot reach the database deletion path.
$conversion = strpos($extraSource, '$value = (string) $rawValue;');
$validation = strpos($extraSource, "preg_match('//u', " . '$value' . ")");
$delete = strpos($extraSource, '->delete($db->quoteName(');

if ($conversion === false || $validation === false || $delete === false
    || !($conversion < $validation && $validation < $delete)) {
    throw new RuntimeException('Extra-field value validation must precede deletion.');
}

$moduleSource = file_get_contents(
    $root . '/modules/mod_sportsmanagement_clubicons/src/Dispatcher/Dispatcher.php'
);

if (!is_string($moduleSource)
    || !str_contains($moduleSource, "assetExists('style', " . '$styleName' . ")")
    || !str_contains($moduleSource, '$wam->registerStyle(')
    || !str_contains($moduleSource, '$wam->useStyle($styleName)')) {
    throw new RuntimeException('Clubicons stylesheet must be registered once and enabled per render.');
}

echo "Joomla 5/6 initials, extra-field and clubicons regressions passed.\n";
