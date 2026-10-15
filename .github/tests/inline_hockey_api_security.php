<?php
/**
 * Regression checks for API credentials and legacy page URLs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);
require_once dirname(__DIR__, 2) . '/site/src/Service/InlineHockeyApiClient.php';

use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyApiClient;

$client = new InlineHockeyApiClient();
$reject = static function (string $url, string $username, string $password, string $error) use ($client): void {
    try {
        $client->fetchJson($url, $username, $password);
    } catch (\RuntimeException $exception) {
        if (str_contains($exception->getMessage(), $error)) {
            return;
        }

        throw new \RuntimeException('Unexpected API URL rejection: ' . $exception->getMessage(), 0, $exception);
    }

    throw new \RuntimeException('Unsafe API URL was accepted: ' . $url);
};

// Rejected before constructing an HTTP client: this test makes no network requests.
$reject('http://example.invalid/api', 'tester', 'secret', 'requires HTTPS');
$reject('https://tester:secret@example.invalid/api', '', '', 'must not contain credentials');
$reject('ftp://example.invalid/api', '', '', 'Invalid Inline-Hockey API URL');
$reject('https://example.invalid/api', 'tester', 'secret', 'trusted ISHD HTTPS');
$reject('https://127.0.0.1/internal', '', '', 'trusted ISHD HTTPS');
$reject('https://www.ishd.de.evil.example/api', '', '', 'trusted ISHD HTTPS');
$reject('https://api.ishd.de:8443/api', '', '', 'trusted ISHD HTTPS');
$reject('http://www.ishd.de/vereine.json', '', '', 'trusted ISHD HTTPS');
$reject("https://www.ishd.de/\nclubs.json", '', '', 'trusted ISHD HTTPS');

if ($client->pageUrl('https://example.invalid/api?page=2', 3) !== 'https://example.invalid/api?page=3') {
    throw new \RuntimeException('Existing API pagination query was not preserved.');
}

if ($client->pageUrl('https://example.invalid/api?filter=active&page=2&page=7&sort=date#results', 4)
    !== 'https://example.invalid/api?filter=active&page=4&sort=date#results') {
    throw new \RuntimeException('Duplicate API pagination parameters were not removed.');
}

if ($client->pageUrl('https://example.invalid/api?homepage=1#results', 0)
    !== 'https://example.invalid/api?homepage=1&page=1#results') {
    throw new \RuntimeException('API pagination changed unrelated query keys or fragment.');
}

// Pagination metadata comes from the remote server. Never trust huge or
// non-scalar values when determining how many HTTP requests to perform.
foreach ([
    [1, 1], [0, 1], [-9, 1], [99, 99], [100, 100], [101, 100],
    [PHP_INT_MAX, 100], ['2', 2], ['0003', 3], ['99999', 100],
    ['-2', 1], ['2junk', 1], ['1e5', 1], ['100000', 1],
    [null, 1], [true, 1], [[], 1], [(object) ['pages' => 999], 1],
] as [$input, $expected]) {
    if ($client->pageCount($input) !== $expected) {
        throw new \\RuntimeException('Invalid ISHD pagination count for ' . json_encode($input));
    }
}

echo "Inline-Hockey API URL security checks OK.\n";
