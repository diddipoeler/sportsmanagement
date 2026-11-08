<?php
/**
 * Verify native Joomla 5/6 Google event-ID checks and database-error privacy.
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
    final class ParameterType { public const INTEGER = 'integer'; }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $entries = [];

        /**
         * Collect private Joomla SQL errors without emitting browser output.
         *
         * @param string $message Internal exception details.
         * @param int $level Joomla log severity.
         * @param string $category Component's log category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Service/GoogleCalendarReadService.php';

    final class EventGuardQuery
    {
        /**
         * Accept the selected calendar columns in the mock query builder.
         *
         * @param array $fields Calendar column identifiers.
         * @return self Query builder stand-in.
         */
        public function select(array $fields): self { return $this; }

        /**
         * Accept the configured calendar table in this SQL fixture.
         *
         * @param string $table Quoted Joomla table name.
         * @return self Query builder stand-in.
         */
        public function from(string $table): self { return $this; }

        /**
         * Accept authorised calendar IDs in the scoped SQL fixture.
         *
         * @param string $column Calendar or ACL column.
         * @param array $values Bounded list of integer IDs.
         * @param string $type Joomla parameter binding type.
         * @return self Query builder stand-in.
         */
        public function whereIn(string $column, array $values, string $type): self { return $this; }

        /**
         * Accept sorting without a live database connection.
         *
         * @param string $expression SQL ordering expression.
         * @return self Query builder stand-in.
         */
        public function order(string $expression): self { return $this; }
    }

    final class EventGuardDatabase implements \Joomla\Database\DatabaseInterface
    {
        public int $reads = 0;
        public bool $failSetup = false;
        public bool $failRead = false;

        /**
         * Build an SQL query or simulate an unavailable database connection.
         *
         * @return EventGuardQuery SQL query test double.
         */
        public function createQuery(): EventGuardQuery
        {
            if ($this->failSetup) {
                throw new \RuntimeException('SQLSTATE setup private=/srv/db.conf');
            }

            return new EventGuardQuery();
        }

        /**
         * Return SQL identifiers without installing the Joomla database.
         *
         * @param string $identifier Calendar table or field name.
         * @return string Unchanged SQL identifier.
         */
        public function quoteName(string $identifier): string { return $identifier; }

        /**
         * Track scoped reads and optionally raise a private SQL exception.
         *
         * @param EventGuardQuery $query Calendar query being executed.
         * @return void
         */
        public function setQuery(EventGuardQuery $query): void
        {
            ++$this->reads;
            if ($this->failRead) {
                throw new \RuntimeException('SQLSTATE execute password=private');
            }
        }

        /**
         * Return no calendars so the optional Google client is not loaded.
         *
         * @return array Empty calendar list for this guarded test.
         */
        public function loadObjectList(): array { return []; }
    }

    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        /**
         * Return an identity permitted to use the selected test calendar.
         *
         * @return object Authorised Joomla calendar user.
         */
        public function getIdentity(): object
        {
            return new class {
                /**
                 * Authorise the component's calendar read for this fixture.
                 *
                 * @param string $permission Joomla ACL action.
                 * @param string $asset Component ACL asset.
                 * @return bool Whether the user may access the calendar.
                 */
                public function authorise(string $permission, string $asset): bool
                {
                    return $permission === 'core.admin' && $asset === 'com_sportsmanagement';
                }
            };
        }
    };

    $db = new EventGuardDatabase();
    $reader = new \Diddipoeler\Component\SportsManagement\Site\Service\GoogleCalendarReadService($db, $app);

    if ($reader->getEvent(3, 'event-x-123') !== null || $db->reads !== 1) {
        throw new \RuntimeException('An ordinary Google event ID containing x was rejected.');
    }

    foreach (["bad\nline", "bad\rline", "zero\0byte", "tab\tbyte", "delete\x7F", ''] as $invalid) {
        if ($reader->getEvent(3, $invalid) !== null || $db->reads !== 1) {
            throw new \RuntimeException('Invalid event identifier reached the database.');
        }
    }

    if ($reader->getEvent(0, 'event-x') !== null
        || $reader->getEvent(3, str_repeat('a', 513)) !== null
        || $db->reads !== 1) {
        throw new \RuntimeException('Invalid calendar ID or oversized event ID was accepted.');
    }

    $db->failSetup = true;
    if ($reader->getCalendars([3]) !== []) {
        throw new \RuntimeException('A database setup exception reached the public JSON feed.');
    }

    $db->failSetup = false;
    $db->failRead = true;
    if ($reader->getEvent(3, 'event-x') !== null || $db->reads !== 2) {
        throw new \RuntimeException('A database read exception reached the public event view.');
    }

    $logs = \Joomla\CMS\Log\Log::$entries;
    if (count($logs) !== 2) {
        throw new \RuntimeException('Frontend calendar SQL errors were not privately logged.');
    }

    foreach ($logs as [$message, $severity, $category]) {
        if ($severity !== \Joomla\CMS\Log\Log::ERROR
            || $category !== 'com_sportsmanagement'
            || !str_contains($message, 'SQLSTATE')) {
            throw new \RuntimeException('Missing or malformed Joomla calendar error log.');
        }
    }

    echo "Joomla 5/6 Google Calendar event-ID and SQL privacy checks passed.\n";
}
