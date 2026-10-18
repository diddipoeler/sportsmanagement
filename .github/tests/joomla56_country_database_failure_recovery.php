<?php
/**
 * Joomla 5/6 country presentation must survive optional database failures.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace Joomla\CMS {
    final class Factory
    {
        public static \Joomla\Database\DatabaseInterface $database;

        public static function getContainer(): object
        {
            return new class {
                public function get(string $service): \Joomla\Database\DatabaseInterface
                {
                    return Factory::$database;
                }
            };
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $entries = [];

        public static function add(string $message, int $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        public static function _(string $value): string
        {
            return $value;
        }
    }
}

namespace Joomla\CMS\HTML {
    final class HTMLHelper
    {
        public static function _(string $action, string $value, string $label): object
        {
            return (object) ['value' => $value, 'text' => $label];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementSiteApplicationResolver
    {
        public static function resolve(): object
        {
            return new class {
                public function getInput(): object
                {
                    return new class {
                        public function getInt(string $key, int $default = 0): int
                        {
                            return $default;
                        }
                    };
                }
            };
        }
    }

    final class SportsManagementDatabaseResolver
    {
        public static function resolve(
            \Joomla\Database\DatabaseInterface $fallbackDatabase,
            int $selector
        ): \Joomla\Database\DatabaseInterface {
            return $fallbackDatabase;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Helper/CountryPresentationHelper.php';

    final class CountryQuery
    {
        public function select(mixed $value): self { return $this; }
        public function from(mixed $value): self { return $this; }
        public function where(mixed $value): self { return $this; }
    }

    final class CountryDatabase implements \Joomla\Database\DatabaseInterface
    {
        public int $attempts = 0;
        public bool $throw = true;

        public function createQuery(): CountryQuery
        {
            ++$this->attempts;

            if ($this->throw) {
                throw new \RuntimeException('SQLSTATE private password=secret');
            }

            return new CountryQuery();
        }

        public function quoteName(string $name): string { return $name; }
        public function quote(string $value): string { return "'" . $value . "'"; }
        public function setQuery(object $query, int $offset = 0, int $limit = 0): void {}
        public function loadObject(): object { return (object) ['name' => 'France']; }
        public function loadObjectList(): array
        {
            return [
                (object) ['alpha3' => 'FRA', 'name' => 'France'],
                (object) ['alpha3' => 'DEU', 'name' => 'Germany'],
            ];
        }
    }

    $db = new CountryDatabase();
    \Joomla\CMS\Factory::$database = $db;
    $helper = \Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper::class;

    if ($helper::name('DEU') !== '' || $db->attempts !== 1) {
        throw new \RuntimeException('Country lookup did not recover from a database failure.');
    }

    if ($helper::name('DEU') !== '' || $db->attempts !== 1) {
        throw new \RuntimeException('Failed country lookup was not cached for this request.');
    }

    if ($helper::options($db) !== [] || $db->attempts !== 2) {
        throw new \RuntimeException('Country select options did not recover from database failure.');
    }

    if (count(\Joomla\CMS\Log\Log::$entries) !== 2
        || !str_contains(\Joomla\CMS\Log\Log::$entries[0][0], 'password=secret')
        || \Joomla\CMS\Log\Log::$entries[0][2] !== 'com_sportsmanagement') {
        throw new \RuntimeException('Country query failures were not logged server-side.');
    }

    $db->throw = false;

    if ($helper::name('FRA') !== 'France' || $db->attempts !== 3) {
        throw new \RuntimeException('Healthy country lookups no longer work.');
    }

    $options = $helper::options($db);

    if (count($options) !== 2
        || $options[0]->value !== 'FRA'
        || $options[1]->value !== 'DEU') {
        throw new \RuntimeException('Healthy country options have regressed.');
    }

    echo "Country database recovery and options passed.\n";
}
