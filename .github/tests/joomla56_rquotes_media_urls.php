<?php
/**
 * Check native and legacy random-quote media URL rules on Joomla 5 and 6.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);
require_once dirname(__DIR__, 2)
    . '/modules/mod_sportsmanagement_rquotes/src/Helper/RquotesHelper.php';

use Diddipoeler\Module\SportsManagementRquotes\Site\Helper\RquotesHelper;

$local = 'https://joomla.example.test/';
$baseCases = [
    [0, 'https://cdn.example.test/a', $local],
    ['0', 'https://cdn.example.test/a', $local],
    [1, 'https://cdn.example.test/a/', 'https://cdn.example.test/a/'],
    ['0001', 'http://media.example.test', 'http://media.example.test/'],
    [1, 'https://user:secret@media.example.test/a', ''],
    [1, 'https://media.example.test/a?token=secret', ''],
    [1, 'https://media.example.test/a#frag', ''],
    [1, 'javascript:alert(1)', ''],
    [1, '//media.example.test/image', ''],
    [1, ['url'], ''],
    [true, 'https://cdn.example.test/a', $local],
];

foreach ($baseCases as [$selector, $remote, $expected]) {
    if (RquotesHelper::pictureServer($selector, $remote, $local) !== $expected) {
        throw new RuntimeException('Invalid image server accepted: ' . var_export([$selector, $remote], true));
    }
}

$helper = new RquotesHelper();
$server = 'https://cdn.example.test/images/';
$mediaCases = [
    [['person_picture' => 'persons/test picture.png'], 'https://cdn.example.test/images/persons/test%20picture.png'],
    [['picture' => 'images/test.jpg'], 'https://cdn.example.test/images/images/test.jpg'],
    [['picture' => '../private/logo.jpg'], ''],
    [['person_picture' => '//evil.example.test/x.png'], ''],
    [['person_picture' => 'javascript:alert(1)'], ''],
    [['person_picture' => 'https://a:b@evil.example.test/x.jpg'], ''],
    [['person_picture' => 'https://valid.example.test/a.png'], 'https://valid.example.test/a.png'],
    [['person_picture' => "test\n.png"], ''],
];

foreach ($mediaCases as [$record, $expected]) {
    $actual = $helper->pictureUrl((object) $record, $server);
    if ($actual !== $expected) {
        throw new RuntimeException('Unsafe quote image URL or unexpected output for ' . json_encode($record));
    }
}

if ($helper->pictureUrl((object) ['picture' => 'relative.jpg'], '') !== '') {
    throw new RuntimeException('Relative image URL escaped an unavailable remote media server.');
}

echo "Joomla 5/6 native/legacy quote media URL validation passed.\n";
