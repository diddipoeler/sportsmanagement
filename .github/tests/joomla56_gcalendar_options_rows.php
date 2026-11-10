<?php
/**
 * Joomla 5/6 GCalendar options, calendar-ID and database-row regressions.
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

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $entries = [];

        /**
         * Capture details of calendar database failures in the Joomla log.
         *
         * @param string $message Internal diagnostic text.
         * @param int $level Logging severity.
         * @param string $category Joomla component log namespace.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Store a fixed set of module configuration values for regression checks.
         *
         * @param array<string,mixed> $settings Simulated Joomla parameters.
         */
        public function __construct(private array $settings = [])
        {
        }

        /**
         * Read a configured module value or its supplied default.
         *
         * @param string $key Configuration key to retrieve.
         * @param mixed $default Fallback for an absent key.
         * @return mixed Configured value or the fallback.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->settings[$key] ?? $default;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_gcalendar/src/Helper/GcalendarHelper.php';

    $class = \Diddipoeler\Module\SportsManagementGcalendar\Site\Helper\GcalendarHelper::class;
    $helper = new $class();
    $bounded = new \ReflectionMethod($class, 'boundedOption');
    $ids = new \ReflectionMethod($class, 'normaliseCalendarIds');

    foreach ([
        ['0002', 1, 2, 2],
        [0, 1, 2, 0],
        ['0000', 1, 2, 0],
        [20, 0, 6, 6],
        ['003500', 0, 4000, 3500],
        [7000, 0, 4000, 4000],
        ['2junk', 1, 2, 1],
        ['-1', 1, 2, 1],
        ['1.0', 1, 2, 1],
        [true, 1, 2, 1],
        [['1'], 1, 2, 1],
        [PHP_INT_MAX . '0', 1, 2, 1],
    ] as [$raw, $default, $maximum, $expected]) {
        if ($bounded->invoke(null, $raw, $default, $maximum) !== $expected) {
            throw new \RuntimeException('GCalendar accepted a malformed display option.');
        }
    }

    $timezone = new \ReflectionMethod($class, 'safeTimezone');
    set_error_handler(static function (int $level, string $message): never {
        throw new \ErrorException($message, 0, $level);
    });

    try {
        foreach ([
            ['Europe/Berlin', 'Europe/Berlin'],
            ['UTC', 'UTC'],
            ['+02:00', '+02:00'],
            [[], 'UTC'],
            [new \stdClass(), 'UTC'],
            [null, 'UTC'],
            [true, 'UTC'],
            ['Not/A_Timezone', 'UTC'],
            ["Europe/Berlin\0evil", 'UTC'],
            [str_repeat('X', 129), 'UTC'],
        ] as [$value, $expected]) {
            if ($timezone->invoke(null, $value) !== $expected) {
                throw new \RuntimeException('GCalendar accepted malformed timezone configuration.');
            }
        }
    } finally {
        restore_error_handler();
    }

    if ($ids->invoke($helper, ['0002', 2, '003', true, '4junk', '000', 5]) !== [2, 3, 5]
        || $ids->invoke($helper, range(1, 200)) !== range(1, 50)
        || $ids->invoke($helper, [PHP_INT_MAX . '0']) !== []) {
        throw new \RuntimeException('GCalendar calendar ID selection or bound regression.');
    }

    final class GCalendarQueryStub
    {
        /**
         * Accept a calendar column selection in the query double.
         *
         * @param mixed $fields SQL field selector.
         * @return self Query builder fixture.
         */
        public function select(mixed $fields): self { return $this; }

        /**
         * Accept the calendar table name.
         *
         * @param string $table Source database table.
         * @return self Query builder fixture.
         */
        public function from(string $table): self { return $this; }

        /**
         * Accept bound calendar or access-level identifiers.
         *
         * @param string $field SQL column name.
         * @param array<int,int> $values Bound integer identifiers.
         * @param string $type Joomla parameter type.
         * @return self Query builder fixture.
         */
        public function whereIn(string $field, array $values, string $type): self { return $this; }

        /**
         * Accept calendar sorting.
         *
         * @param string $expression SQL order expression.
         * @return self Query builder fixture.
         */
        public function order(string $expression): self { return $this; }
    }

    final class GCalendarDbStub implements \Joomla\Database\DatabaseInterface
    {
        public mixed $rows = [];
        public int $queryCount = 0;
        public int $limit = 0;
        public bool $failCreate = false;
        public bool $failLoad = false;

        /**
         * Construct a fluent query builder fixture.
         *
         * @return GCalendarQueryStub Fake Joomla query.
         */
        public function createQuery(): GCalendarQueryStub
        {
            if ($this->failCreate) {
                throw new \RuntimeException('DB create query: password=private');
            }

            return new GCalendarQueryStub();
        }

        /**
         * Simulate Joomla SQL identifier quoting.
         *
         * @param string $name Requested identifier.
         * @return string Unchanged fixture identifier.
         */
        public function quoteName(string $name): string { return $name; }

        /**
         * Record the query and ensure calendar result limits are respected.
         *
         * @param GCalendarQueryStub $query Query being prepared.
         * @param int $offset Offset for result retrieval.
         * @param int $limit Maximum database result count.
         * @return void
         */
        public function setQuery(GCalendarQueryStub $query, int $offset = 0, int $limit = 0): void
        {
            ++$this->queryCount;
            $this->limit = $limit;
        }

        /**
         * Return malformed database data to exercise row filtering.
         *
         * @return mixed Simulated calendar rows or malformed result.
         */
        public function loadObjectList(): mixed
        {
            if ($this->failLoad) {
                throw new \RuntimeException('DB execute: password=private');
            }

            return $this->rows;
        }
    }

    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        /**
         * Return an administrator identity for isolated row validation.
         *
         * @return object Identity fixture with calendar administration access.
         */
        public function getIdentity(): object
        {
            return new class {
                /**
                 * Grant the calendar administrator permission in the test.
                 *
                 * @param string $permission Permission being checked.
                 * @param string $asset Component asset name.
                 * @return bool Whether access is authorised.
                 */
                public function authorise(string $permission, string $asset): bool
                {
                    return $permission === 'core.admin' && $asset === 'com_sportsmanagement';
                }
            };
        }
    };

    $db = new GCalendarDbStub();
    $good = (object) ['id' => '0002', 'name' => 'League events'];
    $db->rows = [$good, null, ['id' => 3], (object) ['id' => true],
        (object) ['id' => '5junk'], (object) ['id' => PHP_INT_MAX . '0']];
    $result = $helper->getCalendars(new \Joomla\Registry\Registry(), $app, $db);

    if ($result !== [$good] || $db->queryCount !== 1 || $db->limit !== 50) {
        throw new \RuntimeException('GCalendar returned malformed SQL records or dropped query bound.');
    }

    $db->rows = (object) ['id' => 2];
    if ($helper->getCalendars(new \Joomla\Registry\Registry(), $app, $db) !== []) {
        throw new \RuntimeException('GCalendar accepted a malformed database result.');
    }

    // Temporary database failures must not abort frontend module rendering
    // or disclose driver credentials in the returned calendar list.
    $db->failCreate = true;
    if ($helper->getCalendars(new \Joomla\Registry\Registry(), $app, $db) !== []) {
        throw new \RuntimeException('GCalendar query setup failure escaped the module helper.');
    }

    $db->failCreate = false;
    $db->failLoad = true;
    if ($helper->getCalendars(new \Joomla\Registry\Registry(), $app, $db) !== []) {
        throw new \RuntimeException('GCalendar query execution failure escaped the module helper.');
    }

    $logs = \Joomla\CMS\Log\Log::$entries;
    if (count($logs) !== 2) {
        throw new \RuntimeException('GCalendar database failures were not logged.');
    }

    foreach ($logs as [$message, $level, $category]) {
        if ($level !== \Joomla\CMS\Log\Log::ERROR
            || $category !== 'com_sportsmanagement'
            || !str_contains($message, 'password=private')) {
            throw new \RuntimeException('GCalendar diagnostic details were not logged correctly.');
        }
    }

    echo "GCalendar option, row and private database failure validation passed.\n";
}
