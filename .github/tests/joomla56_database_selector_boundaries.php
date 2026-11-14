<?php
/**
 * Regression checks for Joomla 5/6 site, admin and module database selectors.
 *
 * Tests strict external database configuration and raw input validation with
 * isolated Joomla service doubles; no live database or Joomla installation is needed.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}

    final class ParameterType
    {
        public const INTEGER = 'integer';
        public const STRING = 'string';
    }

    final class DatabaseFactory
    {
        public static int $attempts = 0;

        /**
         * Count attempted external connections and simulate a private driver failure.
         *
         * @param string $driver Requested Joomla driver.
         * @param array $options Connection parameters.
         * @return DatabaseInterface Never returned because connection fails.
         * @throws \RuntimeException For the simulated driver failure.
         */
        public function getDriver(string $driver, array $options = []): DatabaseInterface
        {
            ++self::$attempts;
            throw new \RuntimeException('private-password-should-not-leak');
        }
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Store the module or component settings used during this isolated test.
         *
         * @param array<string,mixed> $values Registry settings.
         */
        public function __construct(private array $values = []) {}

        /**
         * Read a configuration value without changing its original PHP type.
         *
         * @param string $key Registry setting name.
         * @param mixed $default Fallback for missing keys.
         * @return mixed Saved setting or caller fallback.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        public static \Joomla\Registry\Registry $params;

        /**
         * Expose the configured component registry to both native resolvers.
         *
         * @param string $component Joomla extension identifier.
         * @return \Joomla\Registry\Registry Current test settings.
         */
        public static function getParams(string $component): \Joomla\Registry\Registry
        {
            return self::$params;
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 3;
        public const INFO = 1;
        public static array $messages = [];

        /**
         * Capture diagnostic messages so private database details can be checked.
         *
         * @param string $message Diagnostic message from a component or module.
         * @param mixed $level Joomla severity, which is not relevant to this test.
         * @param string $category Joomla logging category.
         * @return void
         */
        public static function add(string $message, mixed $level = null, string $category = ''): void
        {
            self::$messages[] = $message;
        }
    }
}

namespace Joomla\CMS {
    final class Factory
    {
        public static \Joomla\Database\DatabaseInterface $database;

        /**
         * Provide the native Joomla database service to administrator helpers.
         *
         * @return object Test service container with a database lookup method.
         */
        public static function getContainer(): object
        {
            return new class {
                /**
                 * Return the configured test database instead of a real Joomla service.
                 *
                 * @param string $name Requested service identifier.
                 * @return \Joomla\Database\DatabaseInterface Fake active connection.
                 */
                public function get(string $name): \Joomla\Database\DatabaseInterface
                {
                    return Factory::$database;
                }
            };
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    final class SportsManagementAdministratorApplicationResolver
    {
        public static object $application;

        /**
         * Return a fake administrator application to the legacy model bridge.
         *
         * @return object Configured application double containing request/state.
         */
        public static function resolve(): object
        {
            return self::$application;
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Helper\LegacyModelDatabaseHelper;
    use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver as AdminResolver;
    use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
    use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver as SiteResolver;
    use Diddipoeler\Module\SportsManagementTeamStatisticsCounter\Site\Helper\TeamStatisticsCounterHelper;
    use Joomla\CMS\Component\ComponentHelper;
    use Joomla\CMS\Factory;
    use Joomla\CMS\Log\Log;
    use Joomla\Database\DatabaseFactory;
    use Joomla\Database\DatabaseInterface;
    use Joomla\Registry\Registry;

    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Service/SportsManagementDatabaseResolver.php';
    require_once $root . '/admin/src/Helper/SportsManagementDatabaseResolver.php';
    require_once $root . '/admin/src/Helper/LegacyModelDatabaseHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_teamstatistics_counter/src/Helper/TeamStatisticsCounterHelper.php';

    final class SelectorTestInput
    {
        /**
         * Preserve raw request types when constructing the Joomla input double.
         *
         * @param array<string,mixed> $values Simulated request variables.
         */
        public function __construct(public array $values = []) {}

        /**
         * Return an unfiltered request selector as Joomla Input::get does.
         *
         * @param string $name Input variable name.
         * @param mixed $default Default when absent.
         * @param string $filter Joomla input filter identifier.
         * @return mixed Stored request value, without coercion.
         */
        public function get(string $name, mixed $default = null, string $filter = 'cmd'): mixed
        {
            return array_key_exists($name, $this->values) ? $this->values[$name] : $default;
        }
    }

    final class SelectorTestApp
    {
        /**
         * Bind request and stored user settings to a fake admin application.
         *
         * @param SelectorTestInput $input Simulated request input.
         * @param mixed $state Persisted administrator database selection.
         */
        public function __construct(public SelectorTestInput $input, public mixed $state = 0) {}

        /**
         * Expose unfiltered administrator input to the legacy model bridge.
         *
         * @return SelectorTestInput Request double.
         */
        public function getInput(): SelectorTestInput
        {
            return $this->input;
        }

        /**
         * Supply an existing administrator database preference when no request is set.
         *
         * @param string $key Joomla state key.
         * @param mixed $default Default state.
         * @return mixed Stored setting or fallback.
         */
        public function getUserState(string $key, mixed $default = null): mixed
        {
            return $this->state ?? $default;
        }
    }

    final class SelectorTestQuery
    {
        /**
         * Preserve fluent query construction without constructing real SQL.
         *
         * @param mixed $columns Requested SELECT expressions.
         * @return self Query stub for chaining.
         */
        public function select(mixed $columns): self { return $this; }

        /**
         * Accept a FROM table for the isolated association query.
         *
         * @param string $table Quoted table identifier.
         * @return self Query stub for chaining.
         */
        public function from(string $table): self { return $this; }

        /**
         * Accept a project/season-team join without executing a real database.
         *
         * @param string $kind Join type.
         * @param string $condition Join expression.
         * @return self Query stub for chaining.
         */
        public function join(string $kind, string $condition): self { return $this; }

        /**
         * Accept a validated SQL where-condition in the query stub.
         *
         * @param string $condition Bound-filter expression.
         * @return self Query stub for chaining.
         */
        public function where(string $condition): self { return $this; }

        /**
         * Preserve the Joomla parameter binding call for this regression check.
         *
         * @param string $name Bound placeholder.
         * @param mixed $value Value to bind.
         * @param mixed $type Joomla database parameter type.
         * @return self Query stub for chaining.
         */
        public function bind(string $name, mixed $value, mixed $type): self { return $this; }
    }

    final class SelectorTestDatabase implements DatabaseInterface
    {
        public int $queries = 0;
        public mixed $result = 0;
        public bool $failQuery = false;

        /**
         * Create a test query or simulate a failed private database operation.
         *
         * @return SelectorTestQuery Fluent SQL query double.
         * @throws \RuntimeException When the requested query is simulated as failed.
         */
        public function createQuery(): SelectorTestQuery
        {
            if ($this->failQuery) {
                throw new \RuntimeException('sensitive-sql-password-must-not-leak');
            }

            return new SelectorTestQuery();
        }

        /**
         * Return a stable placeholder for a quoted table or column identifier.
         *
         * @param string $name Identifier name.
         * @param string|null $alias Optional SQL alias.
         * @return string Identifier expression.
         */
        public function quoteName(string $name, ?string $alias = null): string
        {
            return $alias === null ? $name : $name . ' AS ' . $alias;
        }

        /**
         * Track attempted SQL execution without requiring a database server.
         *
         * @param SelectorTestQuery $query Prepared query.
         * @param int $start First result offset.
         * @param int $limit Maximum result count.
         * @return void
         */
        public function setQuery(SelectorTestQuery $query, int $start = 0, int $limit = 0): void
        {
            ++$this->queries;
        }

        /**
         * Return the intentionally malformed or valid project-team row ID.
         *
         * @return mixed Simulated scalar database result.
         */
        public function loadResult(): mixed
        {
            return $this->result;
        }
    }

    /**
     * Compare expected and actual values without relying on PHP assertion settings.
     *
     * @param mixed $expected Expected runtime value.
     * @param mixed $actual Value returned by the tested helper.
     * @param string $description Failure context.
     * @return void
     * @throws \RuntimeException When the comparison fails.
     */
    function selectorAssertSame(mixed $expected, mixed $actual, string $description): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException($description . ': expected '
                . var_export($expected, true) . ', got ' . var_export($actual, true));
        }
    }

    $db = new SelectorTestDatabase();
    Factory::$database = $db;

    // Nonempty strings, objects and arrays must not enable external storage.
    foreach ([0, '0', false, 'false', 'off', ' 1', [], ['1'], new \stdClass()] as $setting) {
        ComponentHelper::$params = new Registry(['cfg_which_database' => $setting, 'jsm_server_user' => 5]);
        DatabaseFactory::$attempts = 0;
        selectorAssertSame($db, SiteResolver::resolve($db), 'Site invalid configuration fallback');
        selectorAssertSame(0, DatabaseFactory::$attempts, 'Site invalid configuration attempted external connection');
        selectorAssertSame($db, (new AdminResolver())->resolve(0, $db), 'Admin invalid configuration fallback');
        selectorAssertSame(0, DatabaseFactory::$attempts, 'Admin invalid configuration attempted external connection');
    }

    foreach ([1, '1', true, 'true'] as $setting) {
        ComponentHelper::$params = new Registry(['cfg_which_database' => $setting, 'jsm_server_user' => 5]);
        DatabaseFactory::$attempts = 0;
        selectorAssertSame($db, SiteResolver::resolve($db), 'Site failed external connection fallback');
        selectorAssertSame(1, DatabaseFactory::$attempts, 'Site enabled configuration did not attempt connection');
        DatabaseFactory::$attempts = 0;
        selectorAssertSame($db, (new AdminResolver())->resolve(0, $db), 'Admin failed external connection fallback');
        selectorAssertSame(1, DatabaseFactory::$attempts, 'Admin enabled configuration did not attempt connection');
    }

    ComponentHelper::$params = new Registry(['cfg_which_database' => 0, 'jsm_server_user' => 5]);
    $request = new SelectorTestInput();
    $app = new SelectorTestApp($request);
    SportsManagementAdministratorApplicationResolver::$application = $app;

    foreach ([false, true, -1, 2, '1junk', '1.0', ' 1', '', ['1'], new \stdClass(), PHP_INT_MAX . '9'] as $bad) {
        $request->values = ['cfg_which_database' => $bad];
        $app->state = 0;

        try {
            LegacyModelDatabaseHelper::resolve();
            throw new \RuntimeException('Malformed admin selector was accepted');
        } catch (\InvalidArgumentException $exception) {
            selectorAssertSame(400, $exception->getCode(), 'Admin malformed selector error code');
        }
    }

    foreach ([0, '0', '0000', 1, '1', '0001'] as $valid) {
        $request->values = ['cfg_which_database' => $valid];
        $app->state = 0;
        selectorAssertSame($db, LegacyModelDatabaseHelper::resolve(), 'Valid admin selector was rejected');
    }

    $request->values = [];
    $app->state = '0001';
    selectorAssertSame($db, LegacyModelDatabaseHelper::resolve(), 'Stored administrator selection was lost');

    $app->state = ['invalid'];
    try {
        LegacyModelDatabaseHelper::resolve();
        throw new \RuntimeException('Malformed saved admin selector was accepted');
    } catch (\InvalidArgumentException $exception) {
        selectorAssertSame(400, $exception->getCode(), 'Saved selector error code');
    }

    $helper = new TeamStatisticsCounterHelper();
    $empty = ['project' => null, 'team' => null, 'stats' => []];

    foreach ([false, true, -1, 2, '1junk', ' 1', '1.0', ['1'], PHP_INT_MAX . '9'] as $bad) {
        $db->queries = 0;
        $params = new Registry(['p' => 12, 'teams' => 24, 'cfg_which_database' => $bad]);
        selectorAssertSame($empty, $helper->getData($params, $db), 'Invalid module selector was accepted');
        selectorAssertSame(0, $db->queries, 'Invalid module selector triggered SQL');
    }

    $association = new \ReflectionMethod(TeamStatisticsCounterHelper::class, 'getProjectTeamId');
    foreach (['37junk' => 0, '0' => 0, '00037' => 37, '37' => 37, '37:slug' => 0] as $raw => $expected) {
        $db->result = (string) $raw;
        selectorAssertSame($expected, $association->invoke($helper, $db, 12, 24),
            'Module project-team association accepted malformed SQL ID');
    }
    foreach ([false, true, null, -37, 37] as $raw) {
        $db->result = $raw;
        selectorAssertSame($raw === 37 ? 37 : 0, $association->invoke($helper, $db, 12, 24),
            'Module project-team association incorrectly cast a driver result');
    }

    Log::$messages = [];
    $db->failQuery = true;
    selectorAssertSame($empty, $helper->getData(
        new Registry(['p' => 12, 'teams' => 24, 'cfg_which_database' => 0]), $db
    ), 'Failed module database query must return empty data');
    if (Log::$messages === []
        || str_contains(implode("\n", Log::$messages), 'sensitive-sql-password-must-not-leak')) {
        throw new \RuntimeException('Module leaked private database diagnostics');
    }

    echo "Joomla 5/6 site/admin/module database selector boundaries passed.\n";
}
