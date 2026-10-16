<?php
/**
 * Joomla 5/6 calendar migration regressions across site, admin and modules.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel
    {
        public mixed $saved = null;

        public function save($data)
        {
            $this->saved = $data;
            return true;
        }
    }
}

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}

namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType { public const INTEGER = 'integer'; }
}

namespace {
    define('_JEXEC', 1);
    // A new calendar must not require a writable site/tmp/createcal.xml.
    define('JPATH_SITE', sys_get_temp_dir() . '/jsm-calendar-migration-' . bin2hex(random_bytes(8)));

    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Model/JsmgcalendarModel.php';
    require_once $root . '/site/src/Service/GoogleCalendarReadService.php';
    require_once $root . '/modules/mod_sportsmanagement_calendar/src/Helper/CalendarHelper.php';

    $admin = new \Diddipoeler\Component\SportsManagement\Administrator\Model\JsmgcalendarModel();
    $calendar = ['name' => 'New calendar', 'color' => '#135CAE'];
    if ($admin->save($calendar) !== true || $admin->saved !== $calendar) {
        throw new \RuntimeException('Native Joomla administrator model save was not used.');
    }
    if (file_exists(JPATH_SITE . '/tmp/createcal.xml') || is_dir(JPATH_SITE)) {
        throw new \RuntimeException('Google GData Atom template was unexpectedly written to the site directory.');
    }

    class BoundedCalendarQuery
    {
        public array $ids = [];
        public function select(mixed $columns): self { return $this; }
        public function from(string $table): self { return $this; }
        public function whereIn(string $name, array $values, string $type): self
        {
            if ($name === 'id') { $this->ids = $values; }
            return $this;
        }
        public function order(string $order): self { return $this; }
    }

    class BoundedCalendarDatabase implements \Joomla\Database\DatabaseInterface
    {
        public int $requests = 0;
        public ?BoundedCalendarQuery $query = null;
        public function createQuery(): BoundedCalendarQuery { return new BoundedCalendarQuery(); }
        public function quoteName(string $name): string { return $name; }
        public function setQuery(BoundedCalendarQuery $query): void
        {
            ++$this->requests;
            $this->query = $query;
        }
        public function loadObjectList(): array { return []; }
    }

    class CalendarSiteApp implements \Joomla\CMS\Application\CMSApplicationInterface
    {
        public function getIdentity(): object
        {
            return new class {
                public function authorise(string $action, string $asset): bool { return true; }
            };
        }
    }

    $db = new BoundedCalendarDatabase();
    $site = new \Diddipoeler\Component\SportsManagement\Site\Service\GoogleCalendarReadService(
        $db, new CalendarSiteApp()
    );
    $site->getCalendars(array_merge(range(1, 75), [1, -9, '80']));
    if ($db->requests !== 1 || $db->query?->ids !== range(1, 50)) {
        throw new \RuntimeException('Frontend Google Calendar selection was not safely bounded.');
    }

    foreach (["malformed\nidentifier", str_repeat('x', 513), "\0bad"] as $eventId) {
        if ($site->getEvent(10, $eventId) !== null || $db->requests !== 1) {
            throw new \RuntimeException('Invalid Google Calendar event ID reached the database.');
        }
    }
    if ($site->getCalendars([]) !== [] || $db->requests !== 1) {
        throw new \RuntimeException('Empty calendar selection accessed the database.');
    }

    $module = \Diddipoeler\Module\SportsManagementCalendar\Site\Helper\CalendarHelper::class;
    foreach ([[-100, 1970], [1970, 1970], [2026, 2026], [9999, 9999], [10000, 9999], [PHP_INT_MAX, 9999]] as [$input, $expected]) {
        if ($module::normaliseYear($input) !== $expected) {
            throw new \RuntimeException('Calendar module did not keep the year within Joomla date bounds.');
        }
    }

    echo "Joomla 5/6 site/admin/module calendar migration contracts OK\n";
}
