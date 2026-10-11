<?php
/**
 * Joomla 5/6 regression tests for project map SQL and admin quickicon selection.
 *
 * Usage: php .github/tests/joomla56_projectmap_quickicon_contract.php
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType { public const INTEGER = 'integer'; }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    final class ProjectQuery
    {
        public array $selected = [];
        public array $filters = [];
        public array $in = [];
        public array $orders = [];
        public int $groups = 0;
        public function select(mixed $columns): self { $this->selected = (array) $columns; return $this; }
        public function from(string $table): self { return $this; }
        public function join(string $type, string $condition): self { return $this; }
        public function where(string $expression): self { $this->filters[] = $expression; return $this; }
        public function whereIn(string $column, array $ids, string $type): self
        {
            $this->in[$column] = $ids;
            return $this;
        }
        public function group(string $column): self { ++$this->groups; return $this; }
        public function order(string $column): self { $this->orders[] = $column; return $this; }
    }

    final class ProjectDatabase implements \Joomla\Database\DatabaseInterface
    {
        public int $calls = 0;
        public ?ProjectQuery $lastQuery = null;
        public array $rows = [];
        public function createQuery(): ProjectQuery { return new ProjectQuery(); }
        public function quoteName(string $column, string $alias = ''): string
        {
            return $alias !== '' ? $column . ' AS ' . $alias : $column;
        }
        public function quote(string $text): string { return "'" . $text . "'"; }
        public function setQuery(ProjectQuery $query, int $offset = 0, int $limit = 0): void
        {
            $this->lastQuery = $query;
            ++$this->calls;
        }
        public function loadObjectList(): array { return $this->rows; }
        public function loadResult(): string { return 'cpanel'; }
    }

    final class FactoryStub
    {
        public static ?ProjectDatabase $database = null;
        public static function getContainer(): object
        {
            return new class {
                public function get(string $id): \Joomla\Database\DatabaseInterface
                {
                    return FactoryStub::$database;
                }
            };
        }
    }

    class_alias(FactoryStub::class, 'Joomla\CMS\Factory');
    require_once $root . '/modules/mod_sportsmanagement_projectmap/src/Helper/ProjectMapHelper.php';
    require_once $root . '/admin/modules/mod_sportsmanagement_quickicon/src/Helper/QuickIconHelper.php';

    $helper = new \Diddipoeler\Module\SportsManagementProjectMap\Site\Helper\ProjectMapHelper();
    $db = new ProjectDatabase();

    foreach ([[], null, ['bogus', [], '1:bad']] as $invalid) {
        if ($helper->getData($invalid, $db) !== [] || $db->calls !== 0) {
            throw new \RuntimeException('Malformed map season IDs must not cause a query.');
        }
    }

    $db->rows = [
        (object) ['country' => 'DEU', 'id' => 2, 'name' => 'Old German project'],
        (object) ['country' => 'FRA', 'id' => 11, 'name' => 'French project'],
        (object) ['country' => 'DEU', 'id' => 10, 'name' => 'Latest German project'],
        (object) ['country' => 'FRA', 'id' => 4, 'name' => 'Old French project'],
    ];

    $selected = $helper->getData([1, '2;3', 0, -1, 'junk', '99oops', [], new \stdClass()], $db);

    if (array_map(static fn(object $row): string => $row->name, $selected)
        !== ['Latest German project', 'French project']) {
        throw new \RuntimeException('Map must choose complete highest-ID project rows per country.');
    }

    if (($db->lastQuery?->in['pro.season_id'] ?? []) !== [1, 2, 3]
        || $db->lastQuery?->groups !== 0
        || str_contains(implode(', ', $db->lastQuery?->selected ?? []), 'MAX(')
        || !str_contains(implode(', ', $db->lastQuery?->orders ?? []), 'pro.id')
    ) {
        throw new \RuntimeException('Map query is not deterministic or strict-SQL compatible.');
    }

    FactoryStub::$database = $db;
    if (\Diddipoeler\Module\SportsManagementQuickIcon\Administrator\Helper\QuickIconHelper::getModPosition()
        !== 'cpanel') {
        throw new \RuntimeException('Administrator quickicon module position lookup failed.');
    }

    foreach (['client_id = 1', 'published = 1', "module = 'mod_sportsmanagement_quickicon'"] as $filter) {
        if (!in_array($filter, $db->lastQuery?->filters ?? [], true)) {
            throw new \RuntimeException('Quickicon module position was not restricted by ' . $filter);
        }
    }

    echo "Joomla 5/6 project map SQL and administrator quickicon selection OK\n";
}
