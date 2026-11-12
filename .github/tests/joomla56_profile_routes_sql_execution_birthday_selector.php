<?php
/**
 * Regression checks for profile routes, SQL execute failures, and birthday database selector guards.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    /** Capture external component routes without the Joomla router. */
    final class SiteRouteHelper
    {
        public static array $calls = [];

        /**
         * Record route arguments for positive and invalid profile identifiers.
         *
         * @param array<string, mixed> $parameters Component query parameters.
         * @return string Generated test route URL.
         */
        public static function query(array $parameters): string
        {
            self::$calls[] = $parameters;
            return 'index.php?' . http_build_query($parameters);
        }
    }
}
namespace Joomla\CMS\Installer {
    /** Deterministic SQL parser fixture. */
    final class Installer
    {
        public static array $statements = [];

        /**
         * Return the configured installer SQL statement sequence.
         *
         * @param string $sql Source SQL content.
         * @return array Parsed statement fixture.
         */
        public static function splitSql(string $sql): array
        {
            return self::$statements;
        }
    }
}
namespace Joomla\Database {
    /** Minimal Joomla database contract for importer and module tests. */
    interface DatabaseInterface
    {
        /**
         * Store the SQL statement to run.
         *
         * @param string $statement SQL query.
         * @return self Fluent query driver.
         */
        public function setQuery(string $statement): self;

        /**
         * Execute a configured SQL statement.
         *
         * @return bool Success or a simulated false error.
         */
        public function execute(): bool;
    }
}
namespace Joomla\Registry {
    /** Standalone Joomla module Registry fixture. */
    final class Registry
    {
        /**
         * Construct the module parameter fixture.
         *
         * @param array<string, mixed> $values Raw module values.
         */
        public function __construct(private array $values = [])
        {
        }

        /**
         * Preserve explicitly configured nulls and malformed selectors.
         *
         * @param string $key Configuration key.
         * @param mixed $default Default if unset.
         * @return mixed Configured parameter.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return array_key_exists($key, $this->values) ? $this->values[$key] : $default;
        }
    }
}
namespace Joomla\CMS\Application {
    /** Test-only Joomla application type. */
    interface CMSApplicationInterface
    {
    }
}
namespace Joomla\CMS\Uri {
    /** Joomla site-root URL fixture for empty birthday layouts. */
    final class Uri
    {
        /**
         * Return the fixed site root.
         *
         * @return string Site root URL.
         */
        public static function root(): string
        {
            return 'https://example.test/';
        }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Helper/PersonProfileRouteHelper.php';
    require_once $root . '/admin/src/Helper/SqlImportHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_birthday/src/Helper/BirthdayHelper.php';

    $routes = \Diddipoeler\Component\SportsManagement\Site\Helper\PersonProfileRouteHelper::class;
    $router = \Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper::class;

    foreach ([0, -1] as $invalid) {
        if ($routes::contact($invalid) !== ''
            || $routes::cbe(8, 4, $invalid) !== ''
            || $routes::cbe($invalid, 4, 5) !== ''
            || $routes::cbe(8, $invalid, 5) !== '') {
            throw new \RuntimeException('Invalid external person profile ID generated a route.');
        }
    }
    if ($routes::contact(8, 2) !== '' || $routes::cbe(8, 4, 5, -1) !== ''
        || $router::$calls !== []) {
        throw new \RuntimeException('Unsupported external database selector reached the router.');
    }
    if (!str_contains($routes::contact(8, 0), 'option=com_contact')
        || !str_contains($routes::cbe(8, 4, 5, 1), 'option=com_cbe')
        || count($router::$calls) !== 2
        || $router::$calls[1]['cfg_which_database'] !== 1) {
        throw new \RuntimeException('Valid external profile routes have regressed.');
    }

    $db = new class implements \Joomla\Database\DatabaseInterface {
        public bool $fail = false;
        public array $executed = [];

        /**
         * Record the SQL statement being prepared.
         *
         * @param string $statement Query source.
         * @return self Current fake database.
         */
        public function setQuery(string $statement): self
        {
            $this->executed[] = $statement;
            return $this;
        }

        /**
         * Return an explicit execution failure when requested.
         *
         * @return bool Simulated SQL driver result.
         */
        public function execute(): bool
        {
            return !$this->fail;
        }
    };
    $sqlFile = tempnam(sys_get_temp_dir(), 'sm-sql-failure-');
    if ($sqlFile === false || file_put_contents($sqlFile, 'SELECT 1;') === false) {
        throw new \RuntimeException('Could not prepare SQL file fixture.');
    }

    try {
        $importer = \Diddipoeler\Component\SportsManagement\Administrator\Helper\SqlImportHelper::class;
        \Joomla\CMS\Installer\Installer::$statements = ['SELECT 1'];
        $importer::importFile($db, $sqlFile);
        if ($db->executed !== ['SELECT 1']) {
            throw new \RuntimeException('Valid SQL execution did not occur.');
        }
        $db->fail = true;
        try {
            $importer::importFile($db, $sqlFile);
            throw new \RuntimeException('The SQL importer ignored a false execution result.');
        } catch (\RuntimeException $exception) {
            if ($exception->getMessage() !== 'SQL import statement execution failed.') {
                throw $exception;
            }
        }
    } finally {
        unlink($sqlFile);
    }

    $birthday = new \Diddipoeler\Module\SportsManagementBirthday\Site\Helper\BirthdayHelper();
    $selector = new \ReflectionMethod($birthday, 'normaliseDatabaseSelector');

    foreach ([null, false, true, [], '1foo', ' 1', -1, 2, '2', 1.0, str_repeat('0', 65)] as $invalid) {
        if ($selector->invoke($birthday, $invalid) !== null) {
            throw new \RuntimeException('Invalid birthday database selector was accepted.');
        }
    }
    foreach ([[0, 0], [1, 1], ['0000', 0], ['0001', 1]] as [$raw, $expected]) {
        if ($selector->invoke($birthday, $raw) !== $expected) {
            throw new \RuntimeException('Valid birthday selector was rejected.');
        }
    }

    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {};
    foreach ([['cfg_which_database' => ['invalid'], 'mode' => ['broken']],
        ['cfg_which_database' => '2', 'mode' => false]] as $config) {
        $data = $birthday->getData(new \Joomla\Registry\Registry($config), new \Joomla\Registry\Registry(), $app, $db);
        if ($data['persons'] !== [] || $data['mode'] !== 'L'
            || $data['pictureServer'] !== 'https://example.test/') {
            throw new \RuntimeException('Invalid birthday module configuration did not fail closed.');
        }
    }

    echo "Joomla 5/6 person profile routes, SQL execution and birthday selectors: OK\n";
}
