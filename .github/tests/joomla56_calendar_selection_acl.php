<?php
/**
 * Joomla 5/6 regression tests for Google Calendar selection, ACL and match IDs.
 *
 * Usage: php .github/tests/joomla56_calendar_selection_acl.php
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
    class Registry
    {
        public function __construct(private array $values = []) {}
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementModel {}
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    require_once $root . '/site/src/Model/JsonfeedModel.php';
    require_once $root . '/site/src/Service/GoogleCalendarReadService.php';
    require_once $root . '/admin/src/Service/GoogleCalendarMatchSynchronizer.php';
    require_once $root . '/modules/mod_sportsmanagement_gcalendar/src/Helper/GcalendarHelper.php';

    final class CalendarQueryStub
    {
        public array $whereIn = [];
        public array $where = [];
        public array $binds = [];

        public function select(mixed $columns): self { return $this; }
        public function from(string $table): self { return $this; }
        public function join(string $type, string $condition): self { return $this; }
        public function order(string $expression): self { return $this; }
        public function where(string $expression): self
        {
            $this->where[] = $expression;
            return $this;
        }
        public function whereIn(string $column, array $values, string $type): self
        {
            $this->whereIn[$column] = $values;
            return $this;
        }
        public function bind(string $name, mixed $value, string $type): self
        {
            $this->binds[$name] = $value;
            return $this;
        }
    }

    final class CalendarDatabaseStub implements \Joomla\Database\DatabaseInterface
    {
        public ?CalendarQueryStub $lastQuery = null;
        public int $executions = 0;

        public function createQuery(): CalendarQueryStub { return new CalendarQueryStub(); }
        public function quoteName(string $name): string { return $name; }
        public function setQuery(CalendarQueryStub $query, int $offset = 0, int $limit = 0): void
        {
            $this->lastQuery = $query;
            ++$this->executions;
        }
        public function loadObjectList(): array { return []; }
    }

    final class CalendarApplicationStub implements \Joomla\CMS\Application\CMSApplicationInterface
    {
        public function __construct(private ?object $user) {}
        public function getIdentity(): ?object { return $this->user; }
    }

    $member = new class {
        public function authorise(string $action, string $asset): bool { return false; }
        public function getAuthorisedViewLevels(): array { return [1, 3]; }
    };
    $admin = new class {
        public function authorise(string $action, string $asset): bool { return true; }
        public function getAuthorisedViewLevels(): array { return []; }
    };
    $withoutLevels = new class {
        public function authorise(string $action, string $asset): bool { return false; }
        public function getAuthorisedViewLevels(): array { return []; }
    };

    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\JsonfeedModel();
    $normaliseFeedIds = new \ReflectionMethod($model, 'normalizeCalendarIds');
    $feedCases = [
        ['2, 3;2', [2, 3]],
        [[1, '4', '4', '4abc', -2, null, [], '6:slug'], [1, 4]],
        [['214748364899999999999', '5'], [5]],
        [null, []],
        [['evil', ['1'], true], []],
    ];
    foreach ($feedCases as [$value, $expected]) {
        if ($normaliseFeedIds->invoke($model, $value) !== $expected) {
            throw new \RuntimeException('Frontend JSON feed accepted invalid calendar IDs.');
        }
    }

    $module = new \Diddipoeler\Module\SportsManagementGcalendar\Site\Helper\GcalendarHelper();
    $db = new CalendarDatabaseStub();

    if ($module->getCalendars(new \Joomla\Registry\Registry(['calendarids' => '3evil']), new CalendarApplicationStub($member), $db) !== []
        || $db->executions !== 0) {
        throw new \RuntimeException('Invalid module calendar selection listed all calendars.');
    }

    $module->getCalendars(
        new \Joomla\Registry\Registry(['calendarids' => '2, 9;2, nope']),
        new CalendarApplicationStub($member),
        $db
    );
    if ($db->lastQuery?->whereIn['id'] !== [2, 9]
        || $db->lastQuery?->whereIn['access'] !== [1, 3]
        || $db->lastQuery?->whereIn['access_content'] !== [1, 3]) {
        throw new \RuntimeException('Module calendar selection or view-level restrictions regressed.');
    }

    $missingUserDb = new CalendarDatabaseStub();
    if ($module->getCalendars(new \Joomla\Registry\Registry(), new CalendarApplicationStub(null), $missingUserDb) !== []
        || $missingUserDb->executions !== 0) {
        throw new \RuntimeException('Module calendar access was allowed without user identity.');
    }

    $missingLevelsDb = new CalendarDatabaseStub();
    if ($module->getCalendars(new \Joomla\Registry\Registry(), new CalendarApplicationStub($withoutLevels), $missingLevelsDb) !== []
        || $missingLevelsDb->executions !== 0) {
        throw new \RuntimeException('Module calendar access was allowed without view levels.');
    }

    $superUserDb = new CalendarDatabaseStub();
    $module->getCalendars(new \Joomla\Registry\Registry(), new CalendarApplicationStub($admin), $superUserDb);
    if ($superUserDb->executions !== 1 || isset($superUserDb->lastQuery->whereIn['access'])) {
        throw new \RuntimeException('Administrator calendar listing behavior changed.');
    }

    $readerDb = new CalendarDatabaseStub();
    $reader = new \Diddipoeler\Component\SportsManagement\Site\Service\GoogleCalendarReadService(
        $readerDb,
        new CalendarApplicationStub($member)
    );
    if ($reader->getCalendars(['4abc', -2, null]) !== [] || $readerDb->executions !== 0) {
        throw new \RuntimeException('Direct calendar read accepted malformed IDs.');
    }
    $reader->getCalendars([4, 4]);
    if ($readerDb->lastQuery?->whereIn['id'] !== [4]
        || $readerDb->lastQuery?->whereIn['access'] !== [1, 3]
        || $readerDb->lastQuery?->whereIn['access_content'] !== [1, 3]) {
        throw new \RuntimeException('Direct calendar read ACL filters changed.');
    }

    $syncDb = new CalendarDatabaseStub();
    $sync = new \Diddipoeler\Component\SportsManagement\Administrator\Service\GoogleCalendarMatchSynchronizer($syncDb);
    $normaliseMatches = new \ReflectionMethod($sync, 'normaliseIds');
    if ($normaliseMatches->invoke($sync, [5, '6', '6abc', -1, null, [], '99999999999999999999']) !== [5, 6]) {
        throw new \RuntimeException('Administrator calendar sync accepted malformed match IDs.');
    }
    $loadMatches = new \ReflectionMethod($sync, 'loadMatches');
    $loadMatches->invoke($sync, [5, 6], 17, (object) ['gcalendar_use_fav_teams' => false]);
    if ($syncDb->lastQuery?->whereIn['m.id'] !== [5, 6]
        || $syncDb->lastQuery?->binds[':projectId'] !== 17) {
        throw new \RuntimeException('Administrator calendar sync did not bind selected matches by project.');
    }

    echo "Joomla 5/6 calendar selection, ACL and match ID checks OK\n";
}
