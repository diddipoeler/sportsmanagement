<?php
/**
 * Standalone Joomla 5/6 regression tests for safe site routes and calendar dates.
 *
 * Run from repository root with:
 * php .github/tests/joomla56-site-route-calendar-date.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Router {
    /** Minimal route facade retaining the generated query for assertions. */
    final class Route
    {
        /**
         * Return the supplied internal route unchanged for isolated tests.
         *
         * @param string $url Joomla internal URL.
         * @param bool $xhtml Whether HTML entity encoding is requested.
         * @return string Original route for assertions.
         */
        public static function _(string $url, bool $xhtml = true): string
        {
            return $url;
        }
    }
}

namespace Joomla\CMS\Uri {
    /** Minimal URI helper used by the native route helper. */
    final class Uri
    {
        /**
         * Encode route parameters using PHP's RFC3986 query formatter.
         *
         * @param array<string,mixed> $parameters Route query values.
         * @return string Percent-encoded query component.
         */
        public static function buildQuery(array $parameters): string
        {
            return http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
        }
    }
}

namespace Joomla\CMS\Date {
    /** Joomla-like date stub accepting timezone names, as Joomla Date does. */
    class Date extends \DateTime
    {
        /**
         * Convert Joomla's timezone name argument to a PHP timezone object.
         *
         * @param string $time Source date or UNIX timestamp expression.
         * @param string $tz Joomla timezone identifier.
         * @return void
         */
        public function __construct(string $time = 'now', string $tz = 'UTC')
        {
            parent::__construct($time, new \DateTimeZone($tz));
        }
    }
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/site/src/Helper/SiteRouteHelper.php';
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarDate.php';

    use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
    use Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarDate;

    /**
     * Verify the site routing helper rejects invalid view identifiers.
     *
     * @param string $view Deliberately invalid Joomla view identifier.
     * @return void
     * @throws \RuntimeException If the router accepts malformed input.
     */
    function smCheckInvalidView(string $view): void
    {
        try {
            SiteRouteHelper::view($view);
        } catch (\InvalidArgumentException) {
            return;
        }

        throw new \RuntimeException('Expected invalid site view to be rejected: ' . var_export($view, true));
    }

    /**
     * Verify the native calendar date parser rejects unsupported input values.
     *
     * @param mixed $value Malformed date or timestamp input.
     * @return void
     * @throws \RuntimeException If the parser accepts an unsupported value.
     */
    function smCheckInvalidCalendarValue(mixed $value): void
    {
        if (CalendarDate::fromValue($value, 'UTC') !== null) {
            throw new \RuntimeException('Unexpected calendar date accepted: ' . var_export($value, true));
        }
    }

    $route = SiteRouteHelper::view('nextmatch', ['view' => 'other', 'option' => 'com_other', 'mid' => 7]);

    if (!str_contains($route, 'option=com_sportsmanagement')
        || !str_contains($route, 'view=nextmatch')
        || !str_contains($route, 'mid=7')) {
        throw new \RuntimeException('Authoritative component/view route contract broken: ' . $route);
    }

    foreach (['', '../admin', 'nextmatch?option=com_users', 'a/b', 'a-b', str_repeat('x', 101)] as $invalidView) {
        smCheckInvalidView($invalidView);
    }

    foreach ([null, false, true, [], new \stdClass(), '', '  ', str_repeat('a', 129)] as $invalidDate) {
        smCheckInvalidCalendarValue($invalidDate);
    }

    $valid = CalendarDate::fromValue(1700000000, 'UTC');

    if (!$valid instanceof \Joomla\CMS\Date\Date || $valid->getTimestamp() !== 1700000000) {
        throw new \RuntimeException('Valid calendar timestamp was not retained.');
    }

    echo "Joomla 5/6 route and calendar-date regressions passed.\n";
}
