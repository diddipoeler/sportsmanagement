<?php
/**
 * Isolated regressions for Joomla 5/6 JEvents calendar request validation.
 *
 * No Joomla bootstrap or JEvents installation is necessary to check that bad
 * date boundaries are rejected before loading optional provider classes.
 *
 * Usage: php .github/tests/joomla56-jevents-date-validation.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    /** Resolver declaration prevents the connector from loading component files. */
    final class SportsManagementSiteApplicationResolver
    {
    }
}

namespace Joomla\Registry {
    /** Lightweight settings stub for date-only validation tests. */
    class Registry
    {
    }
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);

    /** Legacy runtime stub required by the optional JEvents connector. */
    class JSMCalendar
    {
    }

    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_calendar/connectors/jevents_j5.php';

    /**
     * Assert that an invalid provider date returns no events without loading JEvents.
     *
     * The shared match array must remain unchanged, and invalid input must
     * never progress to the optional external calendar provider.
     *
     * @param mixed $date Invalid calendar start date to test.
     * @return void
     * @throws \RuntimeException If invalid input unexpectedly produces events.
     */
    function smAssertInvalidJEventsStart(mixed $date): void
    {
        $matches = [];
        $params = new \Joomla\Registry\Registry();
        $events = \JEventsConnector::getEntries(['start' => $date], $params, $matches);

        if ($events !== [] || $matches !== []) {
            throw new \RuntimeException('Invalid JEvents date was not rejected: ' . var_export($date, true));
        }
    }

    smAssertInvalidJEventsStart(null);
    smAssertInvalidJEventsStart([]);
    smAssertInvalidJEventsStart('');
    smAssertInvalidJEventsStart('2025-02-29');
    smAssertInvalidJEventsStart('2026-11-08 25:00:00');
    smAssertInvalidJEventsStart('2026-11-08 12:61:00');
    smAssertInvalidJEventsStart('2026-11-08 12:00:61');
    smAssertInvalidJEventsStart('2026-11-08 bogus');
    smAssertInvalidJEventsStart('2026-11-08 00:00:00 AND 1=1');

    echo "Joomla 5/6 JEvents invalid-date regressions passed.\n";
}
