<?php
/**
 * Guard Joomla 5/6 navigation module against invalid database selection.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
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
    class Registry
    {
        /**
         * Create a fake Joomla module configuration registry.
         *
         * @param array<string, mixed> $data Stored navigation settings.
         */
        public function __construct(private array $data = []) {}

        /**
         * Read configured values without coercing malformed selector types.
         *
         * @param string $name Parameter being selected.
         * @param mixed $default Fallback for an unset parameter.
         * @return mixed Configured raw value.
         */
        public function get(string $name, mixed $default = null): mixed
        {
            return $this->data[$name] ?? $default;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementDatabaseResolver
    {
        public static array $selections = [];

        /**
         * Record database selections that were allowed to reach Joomla.
         *
         * @param \Joomla\Database\DatabaseInterface $db Primary database.
         * @param int $selector Selected SportsManagement connection.
         * @return \Joomla\Database\DatabaseInterface Original database fixture.
         */
        public static function resolve(
            \Joomla\Database\DatabaseInterface $db,
            int $selector
        ): \Joomla\Database\DatabaseInterface {
            self::$selections[] = $selector;

            return $db;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/modules/mod_sportsmanagement_navigation_menu/src/Helper/NavigationMenuHelper.php';

    $class = \Diddipoeler\Module\SportsManagementNavigationMenu\Site\Helper\NavigationMenuHelper::class;
    $resolver = \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::class;
    $selector = new \ReflectionMethod($class, 'databaseSelector');

    foreach ([
        [0, 0], [1, 1], ['0', 0], ['1', 1],
        ['0000', 0], ['00001', 1],
    ] as [$value, $expected]) {
        if ($selector->invoke(null, $value) !== $expected) {
            throw new \RuntimeException('Valid Joomla navigation database selector changed.');
        }
    }

    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {};
    $db = new class implements \Joomla\Database\DatabaseInterface {};
    $helper = new $class(null, $app, $db);

    foreach ([
        false, true, [], ['1'], new \stdClass(), -1, 2, 0.0,
        '2', '-1', '1.0', '1junk', ' 1', '1 ', '', '99999999999999',
    ] as $raw) {
        if ($selector->invoke(null, $raw) !== null) {
            throw new \RuntimeException('Invalid navigation database selector was accepted.');
        }

        $params = new \Joomla\Registry\Registry(['cfg_which_database' => $raw]);

        if ($helper->getData($params, $app, $db) !== [] || $resolver::$selections !== []) {
            throw new \RuntimeException('Malformed navigation config resolved a database connection.');
        }
    }

    $dispatcher = (string) file_get_contents(
        $root . '/modules/mod_sportsmanagement_navigation_menu/src/Dispatcher/Dispatcher.php'
    );

    if (!str_contains($dispatcher, 'if ($payload === [])')
        || !preg_match('/if \\(\\$payload === \\[\\]\\) \\{[^}]*return false;/s', $dispatcher)) {
        throw new \RuntimeException('Navigation dispatcher did not skip invalid module data.');
    }

    echo "Joomla 5/6 navigation database selection guard passed.\n";
}
