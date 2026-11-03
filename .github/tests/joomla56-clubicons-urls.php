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

echo "Clubicons URL validation checks passed.\n";
