<?php
/**
 * Standalone regression checks for Joomla 5/6 Team Statistics Counter slugs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface
    {
    }

    final class ParameterType
    {
        public const INTEGER = 'integer';
    }
}

namespace {
    \defined('_JEXEC') || define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_teamstatistics_counter/src/Helper/TeamStatisticsCounterHelper.php';

    final class SlugTestQuery
    {
        public array $columns = [];
        public string $table = '';
        public array $conditions = [];
        public array $parameters = [];

        public function select(string $column): self
        {
            $this->columns[] = $column;
            return $this;
        }

        public function from(string $table): self
        {
            $this->table = $table;
            return $this;
        }

        public function where(string $condition): self
        {
            $this->conditions[] = $condition;
            return $this;
        }

        public function bind(string $key, mixed $value, mixed $type): self
        {
            $this->parameters[$key] = [$value, $type];
            return $this;
        }
    }

    final class SlugTestDatabase implements \Joomla\Database\DatabaseInterface
    {
        public ?SlugTestQuery $query = null;
        public int $queries = 0;

        public function __construct(public ?object $record)
        {
        }

        public function createQuery(): SlugTestQuery
        {
            return new SlugTestQuery();
        }

        public function quoteName(string $name, ?string $alias = null): string
        {
            return $name;
        }

        public function setQuery(SlugTestQuery $query, int $offset = 0, int $limit = 0): void
        {
            $this->query = $query;
            $this->queries++;
        }

        public function loadObject(): ?object
        {
            return $this->record === null ? null : clone $this->record;
        }
    }

    $helper = new \Diddipoeler\Module\SportsManagementTeamStatisticsCounter\Site\Helper\TeamStatisticsCounterHelper();

    foreach ([
        ['getTeam', '#__sportsmanagement_team', 't.*', 'teamId', (object) ['id' => 42, 'alias' => 'blue-club'], '42:blue-club'],
        ['getProject', '#__sportsmanagement_project', 'p.*', 'projectId', (object) ['id' => 11, 'alias' => 'spring'], '11:spring'],
        ['getTeam', '#__sportsmanagement_team', 't.*', 'teamId', (object) ['id' => 42, 'alias' => ''], '42:'],
        ['getProject', '#__sportsmanagement_project', 'p.*', 'projectId', (object) ['id' => 11], '11:'],
    ] as [$methodName, $table, $column, $parameter, $record, $slug]) {
        $db = new SlugTestDatabase($record);
        $method = new \ReflectionMethod($helper, $methodName);
        $result = $method->invoke($helper, $db, (int) $record->id);

        if (!$result || $result->slug !== $slug) {
            throw new \RuntimeException('Incorrect Joomla ID:alias slug from ' . $methodName);
        }

        if ($db->query === null
            || $db->query->table !== $table
            || $db->query->columns !== [$column]
            || $db->query->parameters[':' . $parameter][0] !== (int) $record->id
            || str_contains(implode(' ', $db->query->columns), 'CONCAT_WS')
        ) {
            throw new \RuntimeException('Non-portable database query in ' . $methodName);
        }
    }

    foreach (['getTeam', 'getProject'] as $methodName) {
        $method = new \ReflectionMethod($helper, $methodName);
        $db = new SlugTestDatabase(null);

        if ($method->invoke($helper, $db, 12) !== null || $db->queries !== 1) {
            throw new \RuntimeException('Missing record must return null from ' . $methodName);
        }

        $db = new SlugTestDatabase((object) ['id' => 12, 'alias' => 'valid']);
        if ($method->invoke($helper, $db, 0) !== null || $db->queries !== 0) {
            throw new \RuntimeException('Non-positive IDs must not query ' . $methodName);
        }
    }

    echo "Team Statistics Counter portable slug regressions OK\n";
}
