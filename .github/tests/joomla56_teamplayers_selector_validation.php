<?php
/**
 * Joomla 5/6 TeamPlayers project/team selector regression checks.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}

    final class ParameterType
    {
        public const INTEGER = 'integer';
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        public function __construct(private array $data = []) {}

        public function get(string $key, mixed $default = null): mixed
        {
            return $this->data[$key] ?? $default;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        public static function resolve(object $database, int $selector): object
        {
            return $database;
        }
    }
}

namespace {
    use Diddipoeler\Module\SportsManagementTeamPlayers\Site\Helper\TeamPlayersHelper;
    use Joomla\Database\DatabaseInterface;
    use Joomla\Registry\Registry;

    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_teamplayers/src/Helper/TeamPlayersHelper.php';

    function expectSame(mixed $expected, mixed $actual, string $reason): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException(
                $reason . ': expected ' . var_export($expected, true)
                . ', got ' . var_export($actual, true)
            );
        }
    }

    final class QueryStub
    {
        public array $binds = [];

        public function select(array|string $columns): self { return $this; }
        public function from(string $table): self { return $this; }
        public function join(string $type, string $condition): self { return $this; }
        public function where(string $condition): self { return $this; }

        public function bind(string $placeholder, mixed $value, string $type): self
        {
            $this->binds[$placeholder] = $value;
            return $this;
        }
    }

    final class DatabaseStub implements DatabaseInterface
    {
        public int $queries = 0;
        public ?QueryStub $lastQuery = null;

        public function createQuery(): QueryStub { return new QueryStub(); }
        public function quoteName(string $name, ?string $alias = null): string
        {
            return $alias === null ? $name : $name . ' AS ' . $alias;
        }

        public function setQuery(QueryStub $query, int $offset = 0, int $limit = 0): void
        {
            $this->lastQuery = $query;
            ++$this->queries;
        }

        public function loadObject(): ?object { return null; }
    }

    $helper = new TeamPlayersHelper();
    $normalise = new \ReflectionMethod(TeamPlayersHelper::class, 'positiveId');

    foreach ([
        [12, 12],
        ['12', 12],
        ['0012:project-name', 12],
        ['12:team_name', 12],
        [' 12:team-name ', 12],
        ['12:fußball', 12],
        [0, 0],
        ['0', 0],
        [null, 0],
        [true, 0],
        [12.5, 0],
        [['12'], 0],
        ['12suffix', 0],
        ['12:bad:extra', 0],
        ['12:bad alias', 0],
        ['12,13', 0],
        ['-12', 0],
        ['9999999999999999999999999999999', 0],
    ] as [$value, $expected]) {
        expectSame($expected, $normalise->invoke($helper, $value), 'Incorrect numeric ID parsing');
    }

    $db = new DatabaseStub();
    $empty = ['project' => null, 'players' => [], 'roster' => []];

    foreach ([
        ['p' => '12garbage', 'team' => '20'],
        ['p' => '12', 'team' => '20:bad:extra'],
        ['p' => '999999999999999999999999', 'team' => '20'],
        ['p' => ['12'], 'team' => '20'],
        ['p' => '12', 'team' => true],
        ['p' => 0, 'team' => '20'],
    ] as $data) {
        expectSame($empty, $helper->getData(new Registry($data), $db),
            'Malformed module selector must not read the database');
    }
    expectSame(0, $db->queries, 'Invalid project/team selectors issued SQL queries');

    expectSame($empty, $helper->getData(new Registry([
        'p' => '12:my-project',
        'team' => '020:my-team',
    ]), $db), 'A missing project must return an empty team roster');
    expectSame(1, $db->queries, 'Valid selection must query the database');
    expectSame(12, $db->lastQuery?->binds[':projectId'] ?? null,
        'A project ID:alias must bind the canonical integer');
    expectSame(20, $db->lastQuery?->binds[':teamId'] ?? null,
        'A team ID:alias must bind the canonical integer');

    echo "Joomla 5/6 TeamPlayers selector validation OK.\n";
}
