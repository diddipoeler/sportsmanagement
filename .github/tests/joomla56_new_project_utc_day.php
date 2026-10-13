<?php
/**
 * Regression tests for local-day to UTC conversion in New Project module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface
    {
    }
}

namespace Joomla\Database {
    interface DatabaseInterface
    {
    }

    final class ParameterType
    {
        public const STRING = 'string';
        public const INTEGER = 'integer';
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        public function __construct(private array $values = [])
        {
        }

        public function get(string $name, mixed $default = null): mixed
        {
            return $this->values[$name] ?? $default;
        }
    }
}

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        public static function getParams(string $component): \Joomla\Registry\Registry
        {
            return new \Joomla\Registry\Registry();
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        public static function resolve(
            \Joomla\Database\DatabaseInterface $fallback,
            int $selector
        ): \Joomla\Database\DatabaseInterface {
            return $fallback;
        }
    }
}

namespace {
    \defined('_JEXEC') || define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_new_project/src/Helper/NewProjectHelper.php';

    final class ClockApp implements \Joomla\CMS\Application\CMSApplicationInterface
    {
        public function __construct(private string $timezone)
        {
        }

        public function get(string $key, mixed $default = null): mixed
        {
            return $key === 'offset' ? $this->timezone : $default;
        }
    }

    final class ProjectQuery
    {
        public array $where = [];
        public array $bindings = [];

        public function select(array $columns): self
        {
            return $this;
        }

        public function from(string $table): self
        {
            return $this;
        }

        public function join(string $type, string $condition): self
        {
            return $this;
        }

        public function where(string $condition): self
        {
            $this->where[] = $condition;
            return $this;
        }

        public function bind(string $key, mixed $value, mixed $type): self
        {
            $this->bindings[$key] = $value;
            return $this;
        }

        public function order(string $order): self
        {
            return $this;
        }
    }

    final class ProjectDatabase implements \Joomla\Database\DatabaseInterface
    {
        public ?ProjectQuery $query = null;

        public function createQuery(): ProjectQuery
        {
            return new ProjectQuery();
        }

        public function quoteName(string $name, ?string $alias = null): string
        {
            return $name;
        }

        public function setQuery(ProjectQuery $query): void
        {
            $this->query = $query;
        }

        public function loadObjectList(): array
        {
            return [];
        }
    }

    $helper = new \Diddipoeler\Module\SportsManagementNewProject\Site\Helper\NewProjectHelper();
    $range = new \ReflectionMethod($helper, 'todayRange');

    foreach ([
        ['UTC', '2026-02-12T12:00:00+00:00', '2026-02-12 00:00:00', '2026-02-13 00:00:00'],
        ['Europe/Berlin', '2026-03-29T12:00:00+02:00', '2026-03-28 23:00:00', '2026-03-29 22:00:00'],
        ['Europe/Berlin', '2026-10-25T12:00:00+01:00', '2026-10-24 22:00:00', '2026-10-25 23:00:00'],
        ['America/New_York', '2026-03-08T12:00:00-04:00', '2026-03-08 05:00:00', '2026-03-09 04:00:00'],
        ['Asia/Tokyo', '2026-10-13T13:00:00+09:00', '2026-10-12 15:00:00', '2026-10-13 15:00:00'],
        ['Not/A-Timezone', '2026-10-13T13:00:00+00:00', '2026-10-13 00:00:00', '2026-10-14 00:00:00'],
    ] as [$timezone, $input, $expectedStart, $expectedEnd]) {
        $now = new \DateTimeImmutable($input);
        $actual = $range->invoke($helper, new ClockApp($timezone), $now);

        if ($actual !== [$expectedStart, $expectedEnd]) {
            throw new \RuntimeException(
                sprintf('%s %s: expected %s / %s, got %s',
                    $timezone, $input, $expectedStart, $expectedEnd, implode(' / ', $actual))
            );
        }
    }

    $db = new ProjectDatabase();
    $params = new \Joomla\Registry\Registry(['cfg_which_database' => 0]);
    $result = $helper->getData($params, new ClockApp('Europe/Berlin'), $db);

    if ($result !== [] || $db->query === null) {
        throw new \RuntimeException('New Project query was not constructed');
    }

    if (!in_array('p.modified >= :modifiedStart', $db->query->where, true)
        || !in_array('p.modified < :modifiedEnd', $db->query->where, true)
    ) {
        throw new \RuntimeException('New Project UTC day range must be half-open');
    }

    $bounds = $db->query->bindings;
    if (!isset($bounds[':modifiedStart'], $bounds[':modifiedEnd'])
        || $bounds[':modifiedStart'] >= $bounds[':modifiedEnd']
    ) {
        throw new \RuntimeException('New Project query did not bind valid UTC limits');
    }

    echo "New Project DST-safe UTC modified-date queries OK\n";
}
