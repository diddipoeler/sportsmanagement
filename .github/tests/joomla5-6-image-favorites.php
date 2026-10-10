<?php
/**
 * Regression tests for Joomla 5/6 SportsManagement image imports and favorite-only calendars.
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
        public static string $body = '';

        public function getHttp(): FakeHttpClient
        {
            return new FakeHttpClient();
        }
    }

    final class FakeHttpClient
    {
        public function get(string $url, array $headers = [], int $timeout = 30): FakeHttpResponse
        {
            return new FakeHttpResponse(HttpFactory::$body);
        }
    }

    final class FakeHttpResponse
    {
        public function __construct(private string $body)
        {
        }

        public function getStatusCode(): int
        {
            return 200;
        }

        public function getBody(): string
        {
            return $this->body;
        }
    }
}

namespace Joomla\Database {
    interface DatabaseInterface
    {
    }

    final class ParameterType
    {
        public const INTEGER = 'integer';
    }

    final class FakeQuery
    {
        public array $conditions = [];

        public function __call(string $method, array $arguments): self
        {
            if ($method === 'where') {
                $this->conditions[] = (string) ($arguments[0] ?? '');
            }

            return $this;
        }
    }

    final class FakeDatabase implements DatabaseInterface
    {
        public int $executions = 0;
        public FakeQuery $query;

        public function createQuery(): FakeQuery
        {
            return new FakeQuery();
        }

        public function quoteName(string $name, ?string $alias = null): string
        {
            return $name;
        }

        public function setQuery(FakeQuery $query): self
        {
            $this->executions++;
            $this->query = $query;

            return $this;
        }

        public function loadObjectList(): array
        {
            return [(object) ['id' => 100]];
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Service\GoogleCalendarMatchSynchronizer;
    use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyApiClient;
    use Joomla\Database\FakeDatabase;
    use Joomla\Http\HttpFactory;

    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/site/src/Service/InlineHockeyApiClient.php';
    require dirname(__DIR__, 2) . '/admin/src/Service/GoogleCalendarMatchSynchronizer.php';

    function check(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    function mustReject(callable $callback, string $description): void
    {
        try {
            $callback();
        } catch (\RuntimeException) {
            return;
        }

        throw new \LogicException('Expected rejection: ' . $description);
    }

    $client = new InlineHockeyApiClient();
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScL/nwAAAABJRU5ErkJggg==', true);
    check(is_string($png), 'Invalid test PNG fixture.');
    HttpFactory::$body = $png;
    check($client->fetchIshdImage('https://www.ishd.de/logo.png') === $png, 'Valid ISHD PNG was rejected.');
    mustReject(static fn() => $client->fetchIshdImage('https://www.ishd.de/logo.jpg'), 'PNG bytes with JPG extension');
    HttpFactory::$body = '<html>not an image</html>';
    mustReject(static fn() => $client->fetchIshdImage('https://www.ishd.de/logo.png'), 'HTML with PNG extension');
    HttpFactory::$body = str_repeat('x', 5242881);
    mustReject(static fn() => $client->fetchIshdImage('https://www.ishd.de/logo.png'), 'oversized logo');

    $db = new FakeDatabase();
    $synchronizer = new GoogleCalendarMatchSynchronizer($db);
    $loadMatches = new \ReflectionMethod($synchronizer, 'loadMatches');
    $noFavorites = $loadMatches->invoke($synchronizer, [100], 7, (object) [
        'gcalendar_use_fav_teams' => 1,
        'fav_team' => '',
    ]);
    check($noFavorites === [], 'Favorites-only synchronization selected matches without any favorites.');
    check($db->executions === 0, 'Favorites-only synchronization queried without favorites.');

    $withFavorites = $loadMatches->invoke($synchronizer, [100], 7, (object) [
        'gcalendar_use_fav_teams' => 1,
        'fav_team' => '17, 42',
    ]);
    check(count($withFavorites) === 1 && $db->executions === 1, 'Favorite team query did not execute.');
    check(
        in_array('(t1.id IN (17,42) OR t2.id IN (17,42))', $db->query->conditions, true),
        'Favorites-only query does not restrict both teams.'
    );

    echo "Joomla 5/6 image and favorite-team regressions: OK\n";
}
