<?php
/**
 * Joomla 5/6 regressions for untrusted Inline Hockey external entity IDs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
    class ParameterType {
        public const INTEGER = 'integer';
        public const STRING = 'string';
    }
}

namespace Joomla\CMS\Filter {
    class OutputFilter {
        public static function stringURLSafe(string $name): string {
            return strtolower(str_replace(' ', '-', $name));
        }
    }
}

namespace Joomla\CMS\Log {
    class Log {
        public const WARNING = 300;
        public static function add(string $message, int $priority, string $category): void {}
    }
}

namespace Joomla\Http {
    class HttpFactory {
        public static string $body = '';
        public function getHttp(array $options = [], array $transports = []): object {
            return new class {
                public function get(string $url, array $headers = [], int $timeout = 30): object {
                    if (!str_starts_with($url, 'https://www.ishd.de/')) {
                        throw new \RuntimeException('Untrusted ISHD URL was requested.');
                    }
                    return new class {
                        public function getStatusCode(): int { return 200; }
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
    require_once $root . '/site/src/Service/InlineHockeyApiClient.php';
    require_once $root . '/site/src/Service/InlineHockeyProjectService.php';
    require_once $root . '/site/src/Service/InlineHockeyClubTeamImportService.php';
    require_once $root . '/site/src/Service/InlineHockeyMatchImportService.php';
    require_once $root . '/site/src/Service/InlineHockeyClubLogoService.php';

    use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyApiClient;
    use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyProjectService;
    use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyClubTeamImportService;
    use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyMatchImportService;
    use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyClubLogoService;

    class InlineHockeyQuery {
        public function select(mixed $value): self { return $this; }
        public function from(string $value): self { return $this; }
        public function where(string $value): self { return $this; }
        public function bind(string $key, mixed $value, string $type): self { return $this; }
    }

    class InlineHockeyDb implements \Joomla\Database\DatabaseInterface {
        public array $inserted = [];
        public int $reads = 0;
        public function createQuery(): InlineHockeyQuery { ++$this->reads; return new InlineHockeyQuery(); }
        public function quoteName(string $column): string { return $column; }
        public function setQuery(InlineHockeyQuery $query, int $offset = 0, int $limit = 0): void {}
        public function loadResult(): int { return 0; }
        public function insertObject(string $table, object $record): void {
            $this->inserted[] = ['table' => $table, 'record' => $record];
        }
    }

    $client = new InlineHockeyApiClient();
    $db = new InlineHockeyDb();
    $projects = new InlineHockeyProjectService($db);

    foreach ([
        [12, 12], ['12', 12], ['1', 1],
        [0, 0], [-3, 0], ['0', 0], ['012', 0],
        ['12junk', 0], ['1e3', 0], [' 12', 0],
        [12.7, 0], [true, 0], [false, 0], [[12], 0],
        [(object) ['id' => 12], 0], ['99999999999999999999999999', 0],
    ] as [$input, $expected]) {
        if ($client->positiveId($input) !== $expected) {
            throw new \RuntimeException('Invalid external identifier was accepted: ' . json_encode($input));
        }
    }

    // Malformed records must not become club 1, 12 or a clamped integer.
    \Joomla\Http\HttpFactory::$body = json_encode([
        'pages' => 1,
        '_embedded' => ['clubs' => [
            ['id' => [1], 'name' => 'Array'],
            ['id' => '12junk', 'name' => 'Partial'],
            ['id' => '99999999999999999999999999', 'name' => 'Overflow'],
            ['id' => '7', 'name' => 'Valid Club'],
        ]],
    ], JSON_THROW_ON_ERROR);

    $clubs = new InlineHockeyClubTeamImportService($db, $client);
    if ($clubs->importClubs() !== 1 || count($db->inserted) !== 1
        || $db->inserted[0]['record']->id !== 7
        || $db->inserted[0]['table'] !== '#__sportsmanagement_club') {
        throw new \RuntimeException('Malformed ISHD club ID created or replaced a database row.');
    }

    $db->inserted = [];
    $matches = new InlineHockeyMatchImportService($db, $client, $projects);
    $upsert = new \ReflectionMethod($matches, 'upsertMatch');
    $home = ['project_team_id' => 11];
    $away = ['project_team_id' => 22];

    foreach ([[11], '11junk', '99999999999999999999999999', true] as $bad) {
        if ($upsert->invoke($matches, (object) ['id' => $bad], 2, 3, $home, $away) !== false) {
            throw new \RuntimeException('Malformed external match ID was upserted.');
        }
    }

    $prepare = new \ReflectionMethod($matches, 'prepareSide');
    $side = (object) [
        'club' => (object) ['id' => ['bad'], 'name' => 'Not a Club'],
        'team_id' => '15junk',
        'full_name' => 'Not a Team',
    ];
    $prepared = $prepare->invoke($matches, $side, 2, 3, 4);
    if ($prepared['club_id'] !== 0 || $prepared['team_id'] !== 0
        || $prepared['project_team_id'] !== 0 || $db->inserted !== []) {
        throw new \RuntimeException('Invalid external team/club IDs reached the database.');
    }

    // A bad club ID embedded in a schedule must not trigger logo downloads or writes.
    \Joomla\Http\HttpFactory::$body = json_encode([
        'pages' => 1,
        '_embedded' => ['schedule' => [[
            'home_team' => [
                'club' => ['id' => ['bad']],
                '_links' => ['logo' => ['href' => 'https://www.ishd.de/club/logo.png']],
            ],
        ]]],
    ], JSON_THROW_ON_ERROR);
    $logos = new InlineHockeyClubLogoService($db, $client, $projects);
    if ($logos->syncProjectLogos(2, 'https://www.ishd.de/schedule.json') !== 0
        || $db->inserted !== []) {
        throw new \RuntimeException('Malformed external club ID reached the logo updater.');
    }

    echo "Joomla 5/6 Inline Hockey external entity ID write safety OK\n";
}
