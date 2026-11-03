<?php
/**
 * Regression checks for external club icon URL validation.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);
require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_clubicons/src/Helper/ClubiconsHelper.php';

$class = \Diddipoeler\Module\SportsManagementClubicons\Site\Helper\ClubiconsHelper::class;
$helper = (new ReflectionClass($class))->newInstanceWithoutConstructor();
$validate = new ReflectionMethod($class, 'externalWebsiteUrl');
$cases = [
    ['https://example.org/logo.png', 'https://example.org/logo.png'],
    ['http://example.org/club', 'http://example.org/club'],
    ['javascript:alert(1)', ''],
    ['data:text/html,test', ''],
    ['//example.org/club', ''],
    ['https://user:password@example.org/image.png', ''],
    ["https://example.org/evil\nheader", ''],
    ['https://', ''],
];
foreach ($cases as [$input, $expected]) {
    if ($validate->invoke($helper, $input) !== $expected) {
        fwrite(STDERR, 'Clubicons URL validation regression.' . PHP_EOL);
        exit(1);
    }
}
$safeLogoPath = new ReflectionMethod($class, 'safeRelativeLogoPath');
foreach ([
    ['images/teams/club logo.png', 'images/teams/club logo.png'],
    ['teams/münchen.png', 'teams/münchen.png'],
    ['logo.png', 'logo.png'],
    ['../private/config.php', ''],
    ['images/../config.php', ''],
    ['images/./logo.png', ''],
    ['/absolute/logo.png', ''],
    ['//remote.example/logo.png', ''],
    ['https://remote.example/logo.png', ''],
    ['images\\\\private.png', ''],
    ['images/%2e%2e/config.php', ''],
    ['images/logo.png?token=1', ''],
    ['images/logo.png#fragment', ''],
    ['images//logo.png', ''],
    ["images/logo\\n.png", ''],
    [false, ''],
    [['logo.png'], ''],
] as [$path, $expected]) {
    if ($safeLogoPath->invoke(null, $path) !== $expected) {
        fwrite(STDERR, 'Clubicons relative image path regression.' . PHP_EOL);
        exit(1);
    }
}

$firstId = new ReflectionMethod($class, 'firstId');
foreach ([
    [7, 7], ['0007', 7], ['0007:club-2', 7],
    [['0007', '8'], 7], ['0', 0], ['000', 0],
    ['7invalid', 0], ['7:bad/slug', 0], ['7:bad:alias', 0],
    ['7:valid_alias', 7], [false, 0], [['7'], 0],
    [PHP_INT_MAX . '0', 0],
    [['bad', '0008:club'], 8],
    [[null, '0', '0009'], 9],
    [[['9'], '10'], 10],
    [array_fill(0, 100, 'bad') + [100 => '7'], 0],
    [str_repeat('1', 256), 0],
] as [$raw, $expected]) {
    if ($firstId->invoke($helper, $raw) !== $expected) {
        fwrite(STDERR, 'Clubicons project ID and slug regression.' . PHP_EOL);
        exit(1);
    }
}

$nonNegativeInteger = new ReflectionMethod($class, 'nonNegativeInteger');
foreach ([
    [0, 0], [5, 5], ['0002', 2], ['000', 0],
    [true, 0], [false, 0], [null, 0], [['2'], 0],
    ['2invalid', 0], [' 2', 0], [-1, 0],
    [PHP_INT_MAX . '0', 0],
] as [$raw, $expected]) {
    if ($nonNegativeInteger->invoke(null, $raw) !== $expected) {
        fwrite(STDERR, 'Clubicons numeric module selector regression.' . PHP_EOL);
        exit(1);
    }
}

$routeSlug = new ReflectionMethod($class, 'validatedRouteSlug');
foreach ([
    ['7:team-main', 7, '7:team-main'],
    ['0007:städtisch', '0007', '7:städtisch'],
    ['7:other', 8, '8'],
    ['7:bad/path', 7, '7'],
    ['7:bad?query', 7, '7'],
    ['7:bad%2falias', 7, '7'],
    ['7:bad:alias', 7, '7'],
    ['7:bad#fragment', 7, '7'],
    [str_repeat('x', 256), 7, '7'],
    [['7:bad'], 7, '7'],
    ['7:good', ['7'], ''],
    ['7:good', 0, ''],
    ['7:good', PHP_INT_MAX . '0', ''],
    ['7:good', true, ''],
    [null, 7, '7'],
] as [$rawSlug, $id, $expected]) {
    if ($routeSlug->invoke(null, $rawSlug, $id) !== $expected) {
        fwrite(STDERR, 'Clubicons route slug validation regression.' . PHP_EOL);
        exit(1);
    }
}

$imageServerBase = new ReflectionMethod($class, 'safeImageServerBase');
foreach ([
    ['https://images.example.org/assets/', 'https://images.example.org/assets'],
    ['http://images.example.org', 'http://images.example.org'],
    ['https://images.example.org/assets?version=1', ''],
    ['https://images.example.org/assets#logo', ''],
    ['https://user:pass@images.example.org', ''],
    ['javascript:alert(1)', ''],
    ['//images.example.org', ''],
    [null, ''],
    [['https://images.example.org'], ''],
] as [$server, $expected]) {
    if ($imageServerBase->invoke(null, $server) !== $expected) {
        fwrite(STDERR, 'Clubicons remote image server regression.' . PHP_EOL);
        exit(1);
    }
}

echo "Clubicons URL validation checks passed.\n";
