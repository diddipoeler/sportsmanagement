<?php
/**
 * Standalone regression checks for Joomla 5/6 LiveScore date-boundary parsing.
 *
 * This intentionally avoids a full Joomla bootstrap: it supplies only the
 * legacy parent class and namespaced resolver required when loading the file.
 *
 * Usage: php .github/tests/joomla56-livescore-date-boundaries.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    /** Stub prevents the connector from bootstrapping Joomla services. */
    final class SportsManagementSiteApplicationResolver
    {
    }
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);

    /** Minimal compatibility parent for the isolated connector test. */
    class JSMCalendar
    {
    }

    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_calendar/connectors/livescore_j5.php';

    /**
     * Assert the LiveScore date parser accepts only real ISO calendar boundaries.
     *
     * Reflection accesses the private method without executing the connector's
     * Joomla database lookups or optional LiveScore provider integration.
     *
     * @param mixed $input Raw boundary to check.
     * @param string|null $expected Expected validated date, or null if invalid.
     * @return void
     * @throws \RuntimeException When date normalization violates the contract.
     */
    function smAssertLiveScoreBoundary(mixed $input, ?string $expected): void
    {
        $method = new \ReflectionMethod(\LivescoreConnector::class, 'normaliseBoundary');
        $actual = $method->invoke(null, $input);

        if ($actual !== $expected) {
            throw new \RuntimeException(
                'LiveScore boundary mismatch for ' . var_export($input, true)
                . ': expected ' . var_export($expected, true)
                . ', received ' . var_export($actual, true)
            );
        }
    }

    smAssertLiveScoreBoundary('2026-11-08', '2026-11-08');
    smAssertLiveScoreBoundary('2026-11-08 00:00:00', '2026-11-08 00:00:00');
    smAssertLiveScoreBoundary('2024-02-29 23:59:59', '2024-02-29 23:59:59');
    smAssertLiveScoreBoundary('2025-02-29', null);
    smAssertLiveScoreBoundary('2026-11-08 25:00:00', null);
    smAssertLiveScoreBoundary('2026-11-08 12:61:00', null);
    smAssertLiveScoreBoundary('2026-11-08 12:00:61', null);
    smAssertLiveScoreBoundary('2026-11-08 OR 1=1', null);
    smAssertLiveScoreBoundary(['2026-11-08'], null);
    smAssertLiveScoreBoundary('', null);

    echo "Joomla 5/6 LiveScore boundary regressions passed.\n";
}
