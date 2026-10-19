<?php
/**
 * Joomla 5/6 club birthday safe empty state for external database failures.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace Joomla\Registry {
    final class Registry
    {
        public function __construct(private array $values = []) {}

        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $errors = [];

        public static function add(string $message, int $level, string $category): void
        {
            self::$errors[] = [$message, $level, $category];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        public static int $selector = -1;
        public static bool $failResolve = true;

        public static function resolve(
            \Joomla\Database\DatabaseInterface $fallback,
            int $selector
        ): \Joomla\Database\DatabaseInterface {
            self::$selector = $selector;

            if (self::$failResolve) {
                throw new \RuntimeException('External SQLSTATE private user=secret password=secret');
            }

            return $fallback;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_club_birthday/src/Helper/ClubBirthdayHelper.php';

    $helper = new \Diddipoeler\Module\SportsManagementClubBirthday\Site\Helper\ClubBirthdayHelper();
    $selector = new \ReflectionMethod($helper, 'databaseSelector');

    foreach ([
        [0, 0], [1, 1], ['0007', 7], ['000', 0],
        [-1, 0], ['-1', 0], [true, 0], [false, 0],
        [['7'], 0], [[], 0], ['7garbage', 0], ['1.4', 0],
        ['99999999999999999999999999', 0],
    ] as [$value, $expected]) {
        if ($selector->invoke(null, $value) !== $expected) {
            throw new \RuntimeException('Invalid club birthday database selector: ' . var_export($value, true));
        }
    }

    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        public function get(string $key, string $default): string
        {
            return $default;
        }
    };

    $db = new class implements \Joomla\Database\DatabaseInterface {
        public int $queries = 0;

        public function createQuery(): object
        {
            ++$this->queries;
            throw new \RuntimeException('Missing clubs table password=hidden');
        }
    };

    $params = new \Joomla\Registry\Registry([
        'cfg_which_database' => '0007',
        'mode' => 'BC',
    ]);

    $result = $helper->getData($params, $app, $db);

    if ($result !== ['clubs' => [], 'mode' => 'BC']
        || \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::$selector !== 7
        || $db->queries !== 0) {
        throw new \RuntimeException('Club birthday database resolution failure reached a public page.');
    }

    \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::$failResolve = false;
    $result = $helper->getData(
        new \Joomla\Registry\Registry(['cfg_which_database' => ['invalid'], 'mode' => 'bad']),
        $app,
        $db
    );

    if ($result !== ['clubs' => [], 'mode' => 'L']
        || $db->queries !== 1
        || \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::$selector !== 0
        || count(\Joomla\CMS\Log\Log::$errors) !== 2
        || !str_contains(\Joomla\CMS\Log\Log::$errors[0][0], 'password=secret')
        || !str_contains(\Joomla\CMS\Log\Log::$errors[1][0], 'password=hidden')) {
        throw new \RuntimeException('Club birthday SQL errors were not isolated in server logs.');
    }

    echo "Club birthday database resolution and query failure paths passed.\n";
}
