<?php
/**
 * Joomla 5/6 regression checks for ranking window and Inline Hockey redirects.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Http {
    final class HttpFactory
    {
        public static array $options = [];
        public static array $transports = [];
        public static array $headers = [];
        public static string $url = '';
        public static int $status = 200;
        public static string $body = '{"pages":1}';
        public function getHttp(array $options = [], array $transports = []): object
        {
            self::$options = $options;
            self::$transports = $transports;
            return new class {
                public function get(string $url, array $headers = [], int $timeout = 0): object
                {
                    \Joomla\Http\HttpFactory::$url = $url;
                    \Joomla\Http\HttpFactory::$headers = $headers;

                    if ($timeout !== 30) {
                        throw new \RuntimeException('Inline Hockey client request timeout changed.');
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
    require_once $root . '/modules/mod_sportsmanagement_ranking/src/Helper/RankingHelper.php';
    require_once $root . '/site/src/Service/InlineHockeyApiClient.php';

    $helper = new \Diddipoeler\Module\SportsManagementRanking\Site\Helper\RankingHelper();
    $window = new \ReflectionMethod($helper, 'shrinkAroundTeam');
    $table = array_map(static fn (int $i): object => (object) [
        'team' => (object) ['id' => $i],
    ], range(1, 10));

    foreach ([
        [1, 5, [1, 2, 3, 4, 5]],
        [2, 5, [1, 2, 3, 4, 5]],
        [6, 5, [4, 5, 6, 7, 8]],
        [9, 5, [6, 7, 8, 9, 10]],
        [10, 5, [6, 7, 8, 9, 10]],
        [10, 1, [10]],
        [10, 15, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]],
        [999, 5, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]],
    ] as [$teamId, $limit, $expected]) {
        $rows = $window->invoke($helper, $table, $teamId, $limit);
        $actual = array_map(static fn (object $row): int => (int) $row->team->id, $rows);
        if ($actual !== $expected) {
            throw new \RuntimeException('Incorrect ranking selection around team ' . $teamId
                . ': ' . json_encode($actual));
        }
    }

    $client = new \Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyApiClient();
    $http = \Joomla\Http\HttpFactory::class;
    $payload = $client->fetchJson('https://api.ishd.de/schedule', 'example', 'secret');

    if ($payload->pages !== 1
        || $http::$options !== ['follow_location' => false]
        || $http::$transports !== ['Curl', 'Stream']
        || ($http::$headers['Authorization'] ?? '') !== 'Basic ' . base64_encode('example:secret')) {
        throw new \RuntimeException('Inline Hockey API must disable redirects for authenticated requests.');
    }

    $http::$status = 302;
    foreach ([
        static fn () => $client->fetchJson('https://api.ishd.de/schedule', 'user', 'secret'),
        static fn () => $client->fetchIshdImage('https://www.ishd.de/clubs/logo.png', 'user', 'secret'),
    ] as $request) {
        try {
            $request();
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'HTTP status 302')
                && $http::$options === ['follow_location' => false]
                && $http::$transports === ['Curl', 'Stream']) {
                continue;
            }
            throw $e;
        }
        throw new \RuntimeException('Inline Hockey followed a redirect for authenticated content.');
    }

    $http::$status = 200;
    foreach ([
        'https://www.ishd.de:8443/clubs/logo.png',
        'https://127.0.0.1/clubs/logo.png',
        'https://user:password@www.ishd.de/clubs/logo.png',
        "https://www.ishd.de/\nlogo.png",
        'https://www.ishd.de\@evil.example/logo.png',
    ] as $url) {
        try {
            $client->fetchIshdImage($url);
        } catch (\RuntimeException) {
            continue;
        }
        throw new \RuntimeException('Unsafe Inline Hockey image URL was accepted: ' . $url);
    }

    echo "Joomla 5/6 ranking window and Inline Hockey redirect safety OK\n";
}
