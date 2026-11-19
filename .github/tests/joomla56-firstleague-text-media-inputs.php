<?php
/**
 * Regression tests for first-league module UTF-8 labels and media URL controls.
 *
 * Usage: php .github/tests/joomla56-firstleague-text-media-inputs.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

defined('_JEXEC') || define('_JEXEC', 1);

require_once dirname(__DIR__, 2)
    . '/modules/mod_sportsmanagement_firstleagueoverview/src/Helper/FirstLeagueOverviewHelper.php';

$class = \Diddipoeler\Module\SportsManagementFirstLeagueOverview\Site\Helper\FirstLeagueOverviewHelper::class;
$label = new ReflectionMethod($class, 'leagueText');
$url = new ReflectionMethod($class, 'mediaUrl');

foreach ([['Bundesliga', 'Bundesliga'], ['Österreich', 'Österreich'], [[], ''], [null, ''],
    ["\xC3\x28", ''], ["League\nName", ''], [str_repeat('x', 2049), ''],
] as [$value, $expected]) {
    if ($label->invoke(null, $value) !== $expected) {
        throw new RuntimeException('First league label normalisation regressed.');
    }
}

foreach (["images/flags/a.png\n", "\timages/flags/a.png", "images/\x00flag.png",
    "\xC3\x28", str_repeat('p', 4097), 'javascript:alert(1)',
] as $invalid) {
    if ($url->invoke(null, $invalid) !== '') {
        throw new RuntimeException('Unsafe first league media input was accepted.');
    }
}

// Deeply encoded traversal and protocol-relative URLs must not bypass
// the first-league module's local image URL validation.
foreach (['%C3%28', 'images/%C3%28'] as $invalidEncoding) {
    if ($url->invoke(null, $invalidEncoding) !== '') {
        throw new RuntimeException('Encoded invalid UTF-8 image path was accepted.');
    }
}

foreach (['../outside.png', '//untrusted.example/flag.png', 'javascript:alert(1)'] as $unsafe) {
    for ($layers = 1; $layers <= 10; $layers++) {
        $encoded = $unsafe;

        for ($i = 0; $i < $layers; $i++) {
            // rawurlencode() does not escape all slashes; encode them too
            // so each iteration represents a complete new decoding layer.
            $encoded = str_replace('/', '%2F', rawurlencode($encoded));
        }

        $candidate = $unsafe === '../outside.png' ? 'images/' . $encoded : $encoded;

        if ($url->invoke(null, $candidate) !== '') {
            throw new RuntimeException('Deeply encoded first-league image path was accepted.');
        }
    }
}

echo "Joomla 5/6 first league label and media checks passed.\n";
