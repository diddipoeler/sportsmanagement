<?php
/**
 * Joomla 5/6 administrator extra-field save error and ID validation contracts.
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

namespace Joomla\CMS\Language {
    final class Text
    {
        public static function _(string $key): string { return $key; }
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

namespace Diddipoeler\Component\SportsManagement\Administrator\Helper {
    final class SportsManagementAdministratorApplicationResolver
    {
        public static object $app;

        public static function resolve(): \Joomla\CMS\Application\CMSApplicationInterface
        {
            return self::$app;
        }
    }

    final class SportsManagementDatabaseResolver
    {
        public static bool $unavailable = false;

        public function resolve(mixed $config, ?\Joomla\Database\DatabaseInterface $database): \Joomla\Database\DatabaseInterface
        {
            if (self::$unavailable) {
                throw new \RuntimeException('SQLSTATE connection username=secret');
            }

            return $database;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Helper/ExtraFieldsSaveHelper.php';

    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        public array $messages = [];

        public function getInput(): object
        {
            return new class {
                public function getCmd(string $name): string { return 'save'; }
            };
        }

        public function enqueueMessage(string $message, string $type): void
        {
            $this->messages[] = [$message, $type];
        }
    };
    \Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementAdministratorApplicationResolver::$app = $app;

    $db = new class implements \Joomla\Database\DatabaseInterface {
        public array $operations = [];
        public bool $failDelete = false;
        public bool $failInsert = false;
        private string $operation = '';

        public function createQuery(): self { return $this; }
        public function delete(string $table): self
        {
            $this->operation = 'delete';
            return $this;
        }
        public function insert(string $table): self
        {
            $this->operation = 'insert';
            return $this;
        }
        public function where(array $conditions): self { return $this; }
        public function columns(array|string $columns): self { return $this; }
        public function values(string $values): self { return $this; }
        public function quoteName(string|array $name): string|array { return $name; }
        public function quote(string $value): string { return "'" . $value . "'"; }
        public function setQuery(object $query): self { return $this; }

        public function execute(): void
        {
            $this->operations[] = $this->operation;

            if (($this->operation === 'delete' && $this->failDelete)
                || ($this->operation === 'insert' && $this->failInsert)) {
                throw new \RuntimeException('SQLSTATE private connection password=secret');
            }
        }
    };

    $helper = new \Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraFieldsSaveHelper();

    $normalise = new \ReflectionMethod($helper, 'positiveFieldId');
    foreach ([
        [7, 7], ['0007', 7], ['0', 0], [0, 0], ['7bad', 0],
        ['-1', 0], [-1, 0], [true, 0], [false, 0],
        [[7], 0], [[], 0], ['999999999999999999999999', 0],
    ] as [$input, $expected]) {
        if ($normalise->invoke(null, $input) !== $expected) {
            throw new \RuntimeException('Invalid extra field identifier: ' . var_export($input, true));
        }
    }

    // Invalid IDs and nested values must not initiate a database write.
    $helper->save([
        'extraf' => ['invalid', ['nested'], 'zero', 'partial'],
        'extra_id' => ['7junk', 7, '0', '5bad'],
    ], 12, $db);
    if ($db->operations !== []) {
        throw new \RuntimeException('Malformed extra fields initiated a write.');
    }

    $helper->save(['extraf' => ['valid'], 'extra_id' => ['0007']], 12, $db);
    if ($db->operations !== ['delete', 'insert']) {
        throw new \RuntimeException('Legacy zero-padded extra field ID was not saved.');
    }

    $db->operations = [];
    $db->failDelete = true;
    $helper->save(['extraf' => ['test'], 'extra_id' => [7]], 12, $db);
    if ($db->operations !== ['delete']) {
        throw new \RuntimeException('Delete error caused a duplicate extra field insertion.');
    }

    $db->failDelete = false;
    $db->operations = [];
    $db->failInsert = true;
    $helper->save(['extraf' => ['test'], 'extra_id' => [7]], 12, $db);
    if ($db->operations !== ['delete', 'insert']) {
        throw new \RuntimeException('Insert error was not handled.');
    }

    $db->failInsert = false;
    $db->operations = [];
    \Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver::$unavailable = true;
    $helper->save(['extraf' => ['test'], 'extra_id' => [7]], 12, $db);

    if ($db->operations !== []
        || count($app->messages) !== 3
        || count(\Joomla\CMS\Log\Log::$entries) !== 3) {
        throw new \RuntimeException('Extra field database errors were not isolated.');
    }

    foreach ($app->messages as [$message, $type]) {
        if ($message !== 'JERROR_AN_ERROR_HAS_OCCURRED' || $type !== 'error'
            || str_contains($message, 'password')) {
            throw new \RuntimeException('Administrator error message leaked SQL details.');
        }
    }

    if (!str_contains(\Joomla\CMS\Log\Log::$entries[0][0], 'password=secret')) {
        throw new \RuntimeException('Database failure details were not retained in server logs.');
    }

    echo "Administrator extra-field safe save and database error regression passed.\n";
}
