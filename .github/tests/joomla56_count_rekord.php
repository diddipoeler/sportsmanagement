<?php
/**
 * Standalone Joomla 5/6 Count Record module regression tests.
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
        public function createQuery(): object;
        public function quoteName(string $name): string;
        public function setQuery(object $query): void;
        public function loadResult(): mixed;
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        public function __construct(private array $values = [])
        {
        }

        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        public static function sprintf(string $key, mixed ...$values): string
        {
            return $key . '|' . implode('|', $values);
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $messages = [];

        public static function add(string $message, int $priority, string $category): void
        {
            self::$messages[] = [$message, $priority, $category];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        public static function resolve(
            \Joomla\Database\DatabaseInterface $database,
            int $selector = 0
        ): \Joomla\Database\DatabaseInterface {
            return $database;
        }
    }
}

namespace {
    \defined('_JEXEC') || define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_count_rekord/src/Helper/CountRekordHelper.php';

    final class CountQuery
    {
        public function select(string $column): self
        {
            return $this;
        }

        public function from(string $table): self
        {
            return $this;
        }
    }

    final class CountDatabase implements \Joomla\Database\DatabaseInterface
    {
        public bool $queried = false;

        public function __construct(
            private int $count,
            private bool $fail = false
        ) {
        }

        public function createQuery(): object
        {
            return new CountQuery();
        }

        public function quoteName(string $name): string
        {
            return $name;
        }

        public function setQuery(object $query): void
        {
            $this->queried = true;
        }

        public function loadResult(): mixed
        {
            if ($this->fail) {
                throw new \RuntimeException('Database is unavailable');
            }

            return $this->count;
        }
    }

    $helper = new \Diddipoeler\Module\SportsManagementCountRekord\Site\Helper\CountRekordHelper();
    $module = (object) ['module' => 'mod_sportsmanagement_count_rekord'];

    foreach ([
        [60, 100, 'SHOW_MATCHES_DIFF', 40],
        [100, 100, 'SHOW_MATCHES_TARGET_REACHED', 0],
        [120, 100, 'SHOW_MATCHES_TARGET_REACHED', 0],
        [0, 0, 'SHOW_MATCHES_TARGET_REACHED', 0],
    ] as [$count, $target, $expectedKey, $expectedDifference]) {
        $db = new CountDatabase($count);
        $params = new \Joomla\Registry\Registry([
            'jsm_stat_spielpaarungen' => 1,
            'jsm_stat_paarungen' => $target,
        ]);
        $rows = $helper->getData($params, $module, $db);

        if (count($rows) !== 1
            || $rows[0]->anzahldiff !== $expectedDifference
            || !str_starts_with($rows[0]->text, $expectedKey . '|')
            || !$db->queried
        ) {
            throw new \RuntimeException('Invalid Count Record result for count=' . $count);
        }
    }

    $db = new CountDatabase(10);
    $disabled = new \Joomla\Registry\Registry(['jsm_stat_spielpaarungen' => 0]);
    if ($helper->getData($disabled, $module, $db) !== [] || $db->queried) {
        throw new \RuntimeException('Disabled Count Record module queried the database');
    }

    $db = new CountDatabase(0, true);
    $enabled = new \Joomla\Registry\Registry(['jsm_stat_spielpaarungen' => 1]);
    if ($helper->getData($enabled, $module, $db) !== []
        || count(\Joomla\CMS\Log\Log::$messages) !== 1
        || \Joomla\CMS\Log\Log::$messages[0][2] !== 'com_sportsmanagement'
    ) {
        throw new \RuntimeException('Count Record database failure was not handled privately');
    }

    echo "Count Record target and database error cases OK\n";
}
