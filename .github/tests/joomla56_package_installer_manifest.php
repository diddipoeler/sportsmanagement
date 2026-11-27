<?php
/**
 * Regression tests for the native package installer's manifest cache reader.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Installer {
    interface InstallerScriptInterface {}
}
namespace Joomla\Filesystem {
    final class Folder
    {
        public static bool $fail = false;
        public static int $calls = 0;

        /**
         * Simulate Joomla directory creation or a failed operation.
         * @param string $path Directory to create.
         * @return bool Whether creation succeeded.
         */
        public static function create(string $path): bool
        {
            ++self::$calls;
            return !self::$fail && mkdir($path, 0700, true);
        }
    }
}
namespace Joomla\Database {
    interface DatabaseInterface {}
}
namespace Joomla\CMS {
    final class Factory
    {
        public static object $container;

        /**
         * Return the isolated installer test container.
         * @return object Container fixture.
         */
        public static function getContainer(): object
        {
            return self::$container;
        }
    }
}
namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/script.joomla56.php';

    $db = new class implements \Joomla\Database\DatabaseInterface {
        public mixed $cache = null;
        public int $queries = 0;

        /**
         * Create a native query fixture without exposing the old getQuery API.
         * @return object Fluent query fixture.
         */
        public function createQuery(): object
        {
            ++$this->queries;
            return new class {
                /**
                 * Accept selected columns.
                 * @param mixed $columns SQL selection.
                 * @return self Query fixture.
                 */
                public function select(mixed $columns): self { return $this; }

                /**
                 * Accept the source table.
                 * @param string $table SQL table.
                 * @return self Query fixture.
                 */
                public function from(string $table): self { return $this; }

                /**
                 * Accept a query predicate.
                 * @param string $condition SQL predicate.
                 * @return self Query fixture.
                 */
                public function where(string $condition): self { return $this; }
            };
        }

        /**
         * Return the test identifier unchanged.
         * @param string $name SQL identifier.
         * @return string Identifier fixture.
         */
        public function quoteName(string $name): string { return $name; }

        /**
         * Quote a simple test value.
         * @param string $value SQL value.
         * @return string Quoted fixture.
         */
        public function quote(string $value): string { return "'" . $value . "'"; }

        /**
         * Accept the prepared query for execution.
         * @param object $query Native query fixture.
         * @return void
         */
        public function setQuery(object $query): void {}

        /**
         * Return the configured manifest cache fixture.
         * @return mixed Manifest cache or malformed data.
         */
        public function loadResult(): mixed { return $this->cache; }
    };

    \Joomla\CMS\Factory::$container = new class($db) {
        /**
         * Store the test database service.
         * @param object $service Database fixture.
         */
        public function __construct(public object $service) {}

        /**
         * Return the configured service for installer dependency injection.
         * @param string $id Requested service identifier.
         * @return object Configured fixture.
         */
        public function get(string $id): object { return $this->service; }
    };

    $script = new \com_sportsmanagementInstallerScript();
    $version = new \ReflectionMethod($script, 'getInstalledVersion');

    foreach ([
        ['{"version":"4.24.03"}', '4.24.03'],
        ['{"version":" 4.24.03 "}', '4.24.03'],
        ['{"version":[]}', ''],
        ['{"version":42}', ''],
        ['{"version":null}', ''],
        ['{}', ''],
        ['invalid JSON', ''],
        ['', ''],
        [null, ''],
        [[], ''],
    ] as [$cache, $expected]) {
        $db->cache = $cache;
        if ($version->invoke($script) !== $expected) {
            throw new \RuntimeException('Unexpected installed package version.');
        }
    }

    if ($db->queries !== 10) {
        throw new \RuntimeException('Installer did not use the native query factory.');
    }

    \Joomla\CMS\Factory::$container->service = new \stdClass();
    try {
        $version->invoke($script);
        throw new \LogicException('Invalid database service was accepted.');
    } catch (\RuntimeException $exception) {
        if ($exception->getMessage() !== 'SportsManagement installer database service is unavailable.') {
            throw $exception;
        }
    }

    $directory = sys_get_temp_dir() . '/jsm-installer-' . bin2hex(random_bytes(12));
    $ensure = new \ReflectionMethod($script, 'ensureDirectory');
    try {
        $ensure->invoke($script, $directory);
        $ensure->invoke($script, $directory);
        if (!is_dir($directory) || \Joomla\Filesystem\Folder::$calls !== 1) {
            throw new \RuntimeException('Required directory creation is not idempotent.');
        }

        \Joomla\Filesystem\Folder::$fail = true;
        try {
            $ensure->invoke($script, $directory . '/missing');
            throw new \LogicException('Failed directory creation was accepted.');
        } catch (\RuntimeException $exception) {
            if ($exception->getMessage() !== 'Could not create required SportsManagement image directory.') {
                throw $exception;
            }
        }
    } finally {
        if (is_dir($directory . '/missing')) {
            rmdir($directory . '/missing');
        }
        if (is_dir($directory)) {
            rmdir($directory);
        }
    }

    echo "Package installer manifest and directory regressions passed.\n";
}
