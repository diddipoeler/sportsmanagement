<?php
/**
 * Joomla 5/6 site extra-field template validation and query regressions.
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
        public const STRING = 'string';
        public const INTEGER = 'integer';
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Helper/ExtraFieldsReadHelper.php';

    final class SiteExtraFieldQuery
    {
        public array $bindings = [];
        public function select(mixed $v): self { return $this; }
        public function from(mixed $v): self { return $this; }
        public function join(mixed $a, mixed $b): self { return $this; }
        public function where(mixed $v): self { return $this; }
        public function order(mixed $v): self { return $this; }
        public function bind(string $key, mixed $value, mixed $type): self
        {
            $this->bindings[$key] = [$value, $type];

            return $this;
        }
    }

    final class SiteExtraFieldDatabase implements \Joomla\Database\DatabaseInterface
    {
        public int $queries = 0;
        public ?SiteExtraFieldQuery $last = null;
        public bool $throw = false;

        public function createQuery(): SiteExtraFieldQuery
        {
            if ($this->throw) {
                throw new \RuntimeException('Controlled database failure');
            }

            return new SiteExtraFieldQuery();
        }

        public function quoteName(string $name, ?string $alias = null): string
        {
            return $alias === null ? $name : $name . ' AS ' . $alias;
        }

        public function setQuery(SiteExtraFieldQuery $query): void
        {
            ++$this->queries;
            $this->last = $query;
        }

        public function loadResult(): int { return 1; }
        public function loadObjectList(): array { return [(object) ['fieldvalue' => 'value']]; }
    }

    $helper = \Diddipoeler\Component\SportsManagement\Site\Helper\ExtraFieldsReadHelper::class;
    $db = new SiteExtraFieldDatabase();

    foreach ([
        '', '/', 'club/person', 'club%2Finfo', 'club.info',
        '<script>', "club\ninfo", str_repeat('a', 129),
    ] as $invalid) {
        if ($helper::hasFields($db, $invalid)
            || $helper::load($db, 7, $invalid) !== []
            || $db->queries !== 0) {
            throw new \RuntimeException('Malformed site extra-field template triggered a query.');
        }
    }

    if ($helper::load($db, 0, 'club') !== [] || $db->queries !== 0) {
        throw new \RuntimeException('Invalid site extra-field record ID triggered a query.');
    }

    if (!$helper::hasFields($db, 'club-info')
        || ($db->last?->bindings[':templateName'] ?? null) !== ['club-info', 'string']) {
        throw new \RuntimeException('Valid site extra-field template was rejected.');
    }

    $rows = $helper::load($db, 7, 'club_2026');

    if (count($rows) !== 1
        || ($db->last?->bindings[':templateName'] ?? null) !== ['club_2026', 'string']
        || ($db->last?->bindings[':itemId'] ?? null) !== [7, 'integer']) {
        throw new \RuntimeException('Site extra-field query bindings have regressed.');
    }

    $db->throw = true;

    if ($helper::hasFields($db, 'club') || $helper::load($db, 7, 'club') !== []) {
        throw new \RuntimeException('Site extra-field query exception was not handled.');
    }

    echo "Site extra-field template validation passed.\n";
}
