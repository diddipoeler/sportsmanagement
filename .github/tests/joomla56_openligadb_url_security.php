<?php
/**
 * Joomla 5/6 OpenLigaDB preview host restrictions, JSON and summaries.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType { public const INTEGER = 'integer'; public const STRING = 'string'; }
}

namespace Joomla\Http {
    final class HttpFactory
    {
        public static int $creates = 0;
        public static int $gets = 0;
        public static string $body = '[]';
        public static int $status = 200;
        public static array $options = [];
        public static array $transports = [];
        public function getHttp(array $options = [], array $transports = []): object
        {
            self::$creates++;
            self::$options = $options;
            self::$transports = $transports;
            return new class {
                public function get(string $url, array $headers = [], int $timeout = 0): object
                {
                    \Joomla\Http\HttpFactory::$gets++;
                    if ($timeout !== 30 || ($headers['Accept'] ?? '') !== 'application/json') {
                        throw new \RuntimeException('OpenLigaDB API request contract changed.');
                    }
                    return new class {
                        public function getStatusCode(): int { return \Joomla\Http\HttpFactory::$status; }
                        public function getBody(): string { return \Joomla\Http\HttpFactory::$body; }
                    };
                }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Service/OpenLigaDbPreviewService.php';

    final class DbStub implements \Joomla\Database\DatabaseInterface {}
    $service = new \Diddipoeler\Component\SportsManagement\Site\Service\OpenLigaDbPreviewService(new DbStub());
    $http = \Joomla\Http\HttpFactory::class;

    $validUrl = 'https://api.openligadb.de/getmatchdata/bl1/2026';
    $unsafeUrls = [
        'http://api.openligadb.de/getmatchdata/bl1/2026',
        'http://127.0.0.1/admin',
        'https://127.0.0.1/',
        'https://localhost/',
        'https://169.254.169.254/latest/meta-data/',
        'https://evil.example/?to=api.openligadb.de',
        'https://api.openligadb.de.evil.example/matches',
        'https://user:secret@api.openligadb.de/matches',
        'https://api.openligadb.de:8443/matches',
        'ftp://api.openligadb.de/matches',
        '//api.openligadb.de/matches',
        "https://api.openligadb.de/\r\nHost: localhost",
        'https://api.openligadb.de\\@127.0.0.1/matches',
        '',
    ];

    foreach ($unsafeUrls as $url) {
        try {
            $service->fetchMatches($url);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'Invalid OpenLigaDB preview URL.') {
                continue;
            }
            throw $e;
        }
        throw new \RuntimeException('Untrusted OpenLigaDB URL was accepted: ' . $url);
    }

    if ($http::$creates !== 0 || $http::$gets !== 0) {
        throw new \RuntimeException('Rejected URL unexpectedly contacted HTTP.');
    }

    $http::$body = json_encode([
        ['Team1' => ['TeamId' => 2], 'Team2' => ['TeamId' => 4], 'Goals' => [['id' => 1]]],
        'unexpected list entry',
        ['Team1' => ['TeamId' => 3], 'Location' => ['LocationID' => 9]],
    ], JSON_THROW_ON_ERROR);

    $rows = $service->fetchMatches($validUrl);
    if (count($rows) !== 2
        || $http::$options !== ['follow_location' => false]
        || $http::$transports !== ['Curl', 'Stream']) {
        throw new \RuntimeException('OpenLigaDB API parsing or redirect prevention failed.');
    }

    $summary = $service->summarize([
        ...$rows,
        [
            'Team1' => ['TeamId' => ['nested' => 'bad']],
            'Team2' => ['TeamId' => 2],
            'Location' => ['LocationID' => ['bad']],
            'Goals' => [[], []],
        ],
    ]);

    if ($summary !== ['matches' => 3, 'teams' => 3, 'playgrounds' => 1, 'goals' => 3]) {
        throw new \RuntimeException('OpenLigaDB summarization accepted malformed IDs.');
    }

    $http::$body = '[]';
    foreach ([301, 302, 307, 401, 503] as $status) {
        $http::$status = $status;
        try {
            $service->fetchMatches($validUrl);
        } catch (\RuntimeException) {
            continue;
        }
        throw new \RuntimeException('OpenLigaDB HTTP response unexpectedly accepted ' . $status);
    }

    $http::$status = 200;
    $http::$body = '{bad JSON';
    try {
        $service->fetchMatches($validUrl);
        throw new \RuntimeException('Malformed OpenLigaDB JSON was accepted.');
    } catch (\RuntimeException $error) {
        if ($error->getMessage() !== 'OpenLigaDB response is invalid JSON.') {
            throw $error;
        }
    }

    foreach (['api.openligadb.de', 'www.openligadb.de', 'openligadb.de'] as $host) {
        $http::$body = '[]';
        if ($service->fetchMatches('https://' . $host . '/getmatchdata/bl1/2026') !== []) {
            throw new \RuntimeException('Official OpenLigaDB host failed: ' . $host);
        }
    }

    // The legacy preview bridge must use the public Joomla DI container
    // and must not leak infrastructure exceptions into administrator notices.
    $view = file_get_contents($root . '/site/extensions/jsmopenligadb/admin/views/jsmopenligadb/view.html.php');
    $model = file_get_contents($root . '/site/extensions/jsmopenligadb/admin/models/jsmopenligadb.php');
    if (!str_contains($view, "enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED')")
        || !str_contains($view, 'Factory::getContainer()->get(DatabaseInterface::class)')
        || !str_contains($model, 'Factory::getContainer()->get(DatabaseInterface::class)')) {
        throw new \RuntimeException('OpenLigaDB bridges must use the public DI container and safe notices.');
    }

    echo "Joomla 5/6 OpenLigaDB URL, JSON and legacy bridge contracts OK\n";
}
