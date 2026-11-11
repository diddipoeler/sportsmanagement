<?php
/**
 * Joomla 5/6 Matches module database selector and ID regression coverage.
 *
 * Exercises query trait selection without loading Joomla or opening a database.
 *
 * Usage: php .github/tests/joomla56_matches_database_selector.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Store module configuration values in memory for the test.
         *
         * @param array<string,mixed> $values Configured selectors.
         * @return void
         */
        public function __construct(private array $values = [])
        {
        }

        /**
         * Retrieve a configured selector or its fallback value.
         *
         * @param string $key Registry key.
         * @param mixed $default Fallback value.
         * @return mixed Stored configuration or fallback.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        public static array $selectors = [];

        /**
         * Record which external database selector the module requests.
         *
         * @param \Joomla\Database\DatabaseInterface $connection Default Joomla database.
         * @param int $selector Validated selector.
         * @return \Joomla\Database\DatabaseInterface Same fake connection.
         */
        public static function resolve(
            \Joomla\Database\DatabaseInterface $connection,
            int $selector
        ): \Joomla\Database\DatabaseInterface {
            self::$selectors[] = $selector;

            return $connection;
        }
    }
}

namespace {
    \define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_matches/connectors/native/QueryTrait.php';

    final class MatchesSelectorHarness
    {
        use \Diddipoeler\Module\SportsManagementMatches\Site\Helper\NativeQueryTrait;

        /**
         * Exercise the private database selection boundary.
         *
         * @param \Joomla\Registry\Registry $params Raw module configuration.
         * @param \Joomla\Database\DatabaseInterface $db Joomla fake connection.
         * @return \Joomla\Database\DatabaseInterface Selected test connection.
         */
        public function selectDatabase(
            \Joomla\Registry\Registry $params,
            \Joomla\Database\DatabaseInterface $db
        ): \Joomla\Database\DatabaseInterface {
            return $this->database($params, $db);
        }

        /**
         * Exercise the private ID parser used for project and team filters.
         *
         * @param mixed $raw Raw module selector.
         * @return array<int,int> Valid unique positive IDs.
         */
        public function parseIds(mixed $raw): array
        {
            return $this->ids($raw);
        }
    }

    $harness = new MatchesSelectorHarness();
    $db = new class implements \Joomla\Database\DatabaseInterface {};
    $resolver = \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::class;

    foreach ([0, 1, '0001', ' 0 ', PHP_INT_MAX] as $value) {
        $returned = $harness->selectDatabase(
            new \Joomla\Registry\Registry(['cfg_which_database' => $value]),
            $db
        );

        if ($returned !== $db) {
            throw new \RuntimeException('Valid Matches selector changed the injected connection.');
        }
    }

    if ($resolver::$selectors !== [0, 1, 1, 0, PHP_INT_MAX]) {
        throw new \RuntimeException('Valid Matches database selectors were not normalized.');
    }

    foreach ([true, false, ['1'], new \stdClass(), -1, '1junk', '1.5', '1e2', '', PHP_INT_MAX . '0'] as $invalid) {
        try {
            $harness->selectDatabase(
                new \Joomla\Registry\Registry(['cfg_which_database' => $invalid]),
                $db
            );
        } catch (\InvalidArgumentException $exception) {
            if ($exception->getCode() === 400) {
                continue;
            }
        }

        throw new \RuntimeException('Unsafe Matches database selector was accepted: ' . get_debug_type($invalid));
    }

    if (count($resolver::$selectors) !== 5) {
        throw new \RuntimeException('Invalid Matches database selectors reached the resolver.');
    }

    if ($harness->parseIds(['0012', 12, '8;009', false, true, 3.5, ['7'], '0', PHP_INT_MAX . '0']) !== [12, 8, 9]) {
        throw new \RuntimeException('Matches project/team ID filtering accepted invalid values.');
    }

    echo "Joomla 5/6 Matches database selector and IDs regressions passed.\n";
}
