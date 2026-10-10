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

if ($client->pageUrl('https://example.invalid/api?page=2', 3) !== 'https://example.invalid/api?page=3') {
    throw new \RuntimeException('Existing API pagination query was not preserved.');
}

echo "Inline-Hockey API URL security checks OK.\n";
