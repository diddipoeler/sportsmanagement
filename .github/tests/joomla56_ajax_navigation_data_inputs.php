<?php
/**
 * Joomla 5/6 AJAX navigation data helper input and SQL-bound regressions.
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
    final class ParameterType
    {
        public const INTEGER = 'integer';
    }
}
namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Hold selected module settings for the tests.
         *
         * @param array<string,mixed> $data Module parameters.
         */
        public function __construct(private array $data = []) {}

        /**
         * Return a configured value or the supplied default.
         *
         * @param string $key Registry key.
         * @param mixed $default Fallback for missing values.
         * @return mixed Test configuration value.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->data[$key] ?? $default;
        }
    }
}
namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        /**
         * Provide default component configuration.
         *
         * @param string $component Joomla component name.
         * @return \Joomla\Registry\Registry Default configuration.
         */
        public static function getParams(string $component): \Joomla\Registry\Registry
        {
            return new \Joomla\Registry\Registry(['cfg_which_database' => 0]);
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        public static int $calls = 0;
        public static int $selector = -1;

        /**
         * Record which database connection was requested.
         *
         * @param \Joomla\Database\DatabaseInterface $db Injected database.
         * @param int $selector Validated connection selector.
         * @return \Joomla\Database\DatabaseInterface Same test database.
         */
        public static function resolve(
            \Joomla\Database\DatabaseInterface $db, int $selector
        ): \Joomla\Database\DatabaseInterface {
            ++self::$calls;
            self::$selector = $selector;
            return $db;
        }
    }
}
namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_ajax_top_navigation_menu/src/Helper/NavigationDataHelper.php';

    $class = \Diddipoeler\Module\SportsManagementAjaxTopNavigationMenu\Site\Helper\NavigationDataHelper::class;
    $parse = new \ReflectionMethod($class, 'nonNegativeSelector');
    $favorites = new \ReflectionMethod($class, 'favoriteTeamIds');

    foreach ([[0, 0], ['0004', 4], [4, 4], ['000', 0], [true, null],
        [['4'], null], ['4bad', null], [-1, null],
        [PHP_INT_MAX . '0', null]] as [$input, $expected]) {
        if ($parse->invoke(null, $input) !== $expected) {
            throw new \RuntimeException('Invalid navigation database selector parsed.');
        }
    }

    if ($favorites->invoke(null, '0002, 2, 4junk, 0003, -1, 0, 4') !== [2, 3, 4]
        || $favorites->invoke(null, array_fill(0, 20, 1)) !== []
        || $favorites->invoke(null, implode(',', range(1, 300))) !== range(1, 100)) {
        throw new \RuntimeException('Invalid or excessive favorite-team IDs passed to SQL.');
    }

    final class NavigationQueryStub
    {
        public array $whereIn = [];

        /**
         * Accept the query column list.
         *
         * @param mixed $columns Selected SQL columns.
         * @return self Fluent query double.
         */
        public function select(mixed $columns): self { return $this; }

        /**
         * Accept the query source table.
         *
         * @param string $name Table name.
         * @return self Fluent query double.
         */
        public function from(string $name): self { return $this; }

        /**
         * Accept a bound predicate.
         *
         * @param string $condition SQL condition.
         * @return self Fluent query double.
         */
        public function where(string $condition): self { return $this; }

        /**
         * Accept a parameter binding.
         *
         * @param string $name Parameter placeholder.
         * @param mixed $value Parameter value.
         * @param string $type Joomla parameter type.
         * @return self Fluent query double.
         */
        public function bind(string $name, mixed $value, string $type): self { return $this; }

        /**
         * Record the selected favourite team IDs.
         *
         * @param string $name SQL column.
         * @param array<int,int> $ids Selected team IDs.
         * @param string $type Joomla parameter type.
         * @return self Fluent query double.
         */
        public function whereIn(string $name, array $ids, string $type): self
        {
            $this->whereIn = $ids;
            return $this;
        }
    }

    final class NavigationDatabaseStub implements \Joomla\Database\DatabaseInterface
    {
        public int $queries = 0;
        public mixed $favorite = null;
        public array $lastTeamIds = [];

        /**
         * Construct the fake query.
         *
         * @return NavigationQueryStub Query fixture.
         */
        public function createQuery(): NavigationQueryStub { return new NavigationQueryStub(); }

        /**
         * Remember the requested SQL query and favourite team IDs.
         *
         * @param NavigationQueryStub $query Executed query.
         * @param int $offset Result offset.
         * @param int $limit Result count cap.
         * @return void
         */
        public function setQuery(NavigationQueryStub $query, int $offset = 0, int $limit = 0): void
        {
            ++$this->queries;
            $this->lastTeamIds = $query->whereIn;
        }

        /**
         * Return a fixture favourite-team string or malformed value.
         *
         * @return mixed Stored project favorite-team identifiers.
         */
        public function loadResult(): mixed { return $this->favorite; }

        /**
         * Simulate an empty list of team metadata.
         *
         * @return array<int,object> No team records.
         */
        public function loadObjectList(): array { return []; }
    }

    final class NavigationInputStub
    {
        public array $settings = [];

        /**
         * Supply a raw Joomla request database selector.
         *
         * @param mixed $selector Request value.
         */
        public function __construct(private mixed $selector = null) {}

        /**
         * Return a request parameter without coercing malformed values.
         *
         * @param string $key Requested parameter.
         * @param mixed $default Missing-key fallback.
         * @param string $filter Joomla input filter.
         * @return mixed Raw request setting.
         */
        public function get(string $key, mixed $default = null, string $filter = 'CMD'): mixed
        {
            return $key === 'cfg_which_database' ? $this->selector : $default;
        }

        /**
         * Record the validated selector used by downstream routes.
         *
         * @param string $key Input field.
         * @param mixed $value Validated value.
         * @return void
         */
        public function set(string $key, mixed $value): void { $this->settings[$key] = $value; }
    }

    final class NavigationAppStub implements \Joomla\CMS\Application\CMSApplicationInterface
    {
        /**
         * Set up a synthetic frontend application.
         *
         * @param NavigationInputStub $input Request input double.
         */
        public function __construct(private NavigationInputStub $input) {}

        /**
         * Identify the application as the Joomla site client.
         *
         * @param string $client Client name.
         * @return bool True for the frontend.
         */
        public function isClient(string $client): bool { return $client === 'site'; }

        /**
         * Provide the request input object.
         *
         * @return NavigationInputStub Fake Joomla input.
         */
        public function getInput(): NavigationInputStub { return $this->input; }
    }

    $db = new NavigationDatabaseStub();
    $resolver = \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::class;

    foreach ([true, ['2'], '2junk', PHP_INT_MAX . '0'] as $invalid) {
        try {
            new $class(new \Joomla\Registry\Registry(['cfg_which_database' => 2]),
                new NavigationAppStub(new NavigationInputStub($invalid)), $db);
            throw new \RuntimeException('Malformed AJAX database request was accepted.');
        } catch (\InvalidArgumentException) {
        }
    }

    if ($resolver::$calls !== 0) {
        throw new \RuntimeException('Malformed database selectors reached resolver.');
    }

    $input = new NavigationInputStub('0007');
    $helper = new $class(new \Joomla\Registry\Registry(),
        new NavigationAppStub($input), $db);
    if ($resolver::$calls !== 1 || $resolver::$selector !== 7
        || ($input->settings['cfg_which_database'] ?? null) !== 7) {
        throw new \RuntimeException('Valid database selector was not reused consistently.');
    }

    $db->favorite = '0002, 2, 4junk, 0003, 0, -1';
    $helper->getFavTeams('11');
    if ($db->lastTeamIds !== [2, 3]) {
        throw new \RuntimeException('Malformed favourite team IDs reached SQL IN clause.');
    }

    $before = $db->queries;
    if ($helper->getFavTeams(['11']) !== false || $db->queries !== $before) {
        throw new \RuntimeException('Malformed favourite team project ID reached SQL.');
    }

    echo "AJAX navigation data selectors and favourite teams passed.\n";
}
