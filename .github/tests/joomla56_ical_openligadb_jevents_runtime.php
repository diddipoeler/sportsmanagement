<?php
/**
 * Joomla 5/6 ICS parser, OpenLigaDB preview and JEvents connector regressions.
 *
 * Run: php .github/tests/joomla56_ical_openligadb_jevents_runtime.php
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
         * Save simulated module settings for a connector runtime test.
         *
         * @param array<string,mixed> $values Joomla module options.
         */
        public function __construct(private array $values = [])
        {
        }

        /**
         * Read the configured value exactly as a Joomla registry would.
         *
         * @param string $key Module option name.
         * @param mixed $default Value used when the option is absent.
         * @return mixed Current option or fallback.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace Joomla\CMS\Router {
    final class Route
    {
        /**
         * Return the supplied test route unchanged to inspect query values.
         *
         * @param string $route Raw Joomla internal route.
         * @return string Deterministic route string.
         */
        public static function _(string $route): string
        {
            return $route;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    /**
     * Prevent a standalone JEvents fixture from booting Joomla services.
     */
    final class SportsManagementSiteApplicationResolver {}
}

namespace {
    \define('_JEXEC', 1);
    \define('JPATH_SITE', __DIR__);

    /**
     * Provide the native calendar base class expected by the JEvents adapter.
     */
    class JSMCalendar {}

    /**
     * Assert a Joomla 5/6 migration behaviour with an actionable failure.
     *
     * @param bool $valid Whether the expected assertion holds.
     * @param string $message Failure description.
     * @return void
     */
    function assertCompatRuntime(bool $valid, string $message): void
    {
        if (!$valid) {
            throw new \RuntimeException($message);
        }
    }

    /**
     * Escalate notices, deprecations and PHP 8 type warnings into failures.
     *
     * @param int $severity PHP error severity.
     * @param string $message Original PHP runtime message.
     * @return never Always throws an error exception.
     */
    function rejectPhpWarning(int $severity, string $message): never
    {
        throw new \ErrorException($message, 0, $severity);
    }

    set_error_handler('rejectPhpWarning');

    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/helpers/ical.php';
    require_once $root . '/site/src/Service/OpenLigaDbPreviewService.php';
    require_once $root . '/modules/mod_sportsmanagement_calendar/connectors/jevents_j5.php';

    // RFC 5545 text folding must happen before splitting the property at ':',
    // including a folded line with its own colon and tab indentation.
    $calendarText = "BEGIN:VCALENDAR\r\n"
        . "VERSION:2.0\r\n"
        . "BEGIN:VEVENT\r\n"
        . "DTSTART:20261117T110000Z\r\n"
        . "SUMMARY:Football:\r\n"
        . " league final\r\n"
        . "DESCRIPTION:First line\r\n"
        . "\twith: second part\r\n"
        . "LOCATION:\r\n"
        . "END:VEVENT\r\n"
        . "BEGIN:VEVENT\r\n"
        . "DTSTART:20261117T090000Z\r\n"
        . "SUMMARY:Morning game\r\n"
        . "END:VEVENT\r\n"
        . "END:VCALENDAR\r\n";

    $fixture = tempnam(sys_get_temp_dir(), 'jsm-ical-');

    if ($fixture === false) {
        throw new \RuntimeException('Could not create temporary iCalendar fixture.');
    }

    try {
        file_put_contents($fixture, $calendarText);
        $calendar = new \ical();
        $parsed = $calendar->parse($fixture);

        assertCompatRuntime(is_array($parsed), 'Valid calendar input was not parsed.');
        $events = $calendar->get_event_list();
        assertCompatRuntime(
            count($events) === 2
            && ($events[0]['SUMMARY'] ?? null) === 'Football:league final'
            && ($events[0]['DESCRIPTION'] ?? null) === 'First linewith: second part'
            && ($events[0]['LOCATION'] ?? null) === '',
            'iCalendar folded fields, colons or empty values were changed.'
        );

        $ordered = $calendar->get_sort_event_list();
        assertCompatRuntime(
            is_array($ordered)
            && ($ordered[0]['SUMMARY'] ?? null) === 'Morning game',
            'iCalendar event sorting no longer honours DTSTART.'
        );

        assertCompatRuntime(
            date('Y-m-d', $calendar->ical_date_to_unix('20261117')) === '2026-11-17'
            && $calendar->ical_date_to_unix('20250230T120000Z') === false
            && $calendar->ical_date_to_unix('20261117T250000Z') === false
            && $calendar->ical_date_to_unix('20261117junk') === false,
            'iCalendar invalid or date-only timestamp handling regressed.'
        );
    } finally {
        unlink($fixture);
    }

    // Constructor only needs a typed database dependency: summarise() is
    // read-only and does not execute a query.
    $database = new class implements \Joomla\Database\DatabaseInterface {};
    $preview = new \Diddipoeler\Component\SportsManagement\Site\Service\OpenLigaDbPreviewService($database);

    $summary = $preview->summarize([
        [
            'Team1' => ['TeamId' => 42],
            'Team2' => ['TeamId' => '0042'],
            'Location' => ['LocationID' => '0007'],
            'Goals' => [['GoalID' => 1]],
        ],
        [
            'Team1' => ['TeamId' => '43'],
            'Team2' => ['TeamId' => '9999999999999999999999999'],
            'Location' => ['LocationID' => 7],
            'Goals' => [['GoalID' => 2], ['GoalID' => 3]],
        ],
        [
            'Team1' => ['TeamId' => '-1'],
            'Team2' => ['TeamId' => '42bad'],
            'Location' => ['LocationID' => ['invalid']],
            'Goals' => [],
        ],
    ]);

    assertCompatRuntime(
        $summary === ['matches' => 3, 'teams' => 2, 'playgrounds' => 1, 'goals' => 3],
        'OpenLigaDB preview included malformed or duplicate team/location IDs.'
    );

    // A standalone JEvents integration can format fixture data without
    // installing the third-party calendar provider or Joomla application.
    \JEventsConnector::$xparams = new \Joomla\Registry\Registry(['jevents_text' => ['invalid heading']]);
    $rows = [
        [
            'year' => 2026,
            'month' => 11,
            'cellDate' => strtotime('2026-11-17 00:00:00 UTC'),
            'events' => [
                (object) [
                    '_eventdetail_id' => '100',
                    '_dup' => '17',
                    '_uid' => ['invalid uid'],
                    '_dtstart' => strtotime('2026-11-17 11:30:00 UTC'),
                    '_dtend' => strtotime('2026-11-17 12:00:00 UTC'),
                    '_title' => ['invalid title'],
                    '_location' => "Arena",
                    '_color_bar' => '#aabbcc',
                ],
                (object) [
                    '_eventdetail_id' => ['invalid id'],
                    '_dup' => 17,
                    '_dtstart' => ['invalid timestamp'],
                    '_title' => 'No working link',
                    '_location' => ['invalid location'],
                ],
            ],
        ],
        [
            'year' => 2026,
            'month' => 11,
            'events' => [(object) ['_title' => 'Missing day timestamp']],
        ],
    ];
    $matches = [];
    $method = new \ReflectionMethod(\JEventsConnector::class, 'formatEntries');
    $formatted = $method->invokeArgs(null, [$rows, &$matches]);

    assertCompatRuntime(
        count($formatted) === 2
        && count($matches) === 2
        && ($formatted[0]['title'] ?? null) === ''
        && ($formatted[0]['headingtitle'] ?? null) === ''
        && ($formatted[0]['location'] ?? null) === 'Arena'
        && str_contains($formatted[0]['link'] ?? '', 'evid=100')
        && ($formatted[1]['link'] ?? null) === ''
        && ($formatted[1]['location'] ?? null) === ''
        && ($formatted[1]['date'] ?? '') !== '1970-01-01 00:00',
        'JEvents accepted malformed IDs, fields or missing calendar day.'
    );

    echo "Joomla 5/6 iCalendar, OpenLigaDB and JEvents regressions passed.\n";
}
