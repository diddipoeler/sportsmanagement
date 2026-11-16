<?php
/**
 * Regression checks for Joomla 5/6 site/admin Ajax bridges and calendar input.
 *
 * Execute from the repository root:
 * php .github/tests/joomla56-ajax-calendar-bridges.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

use Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarFunctions;

define('_JEXEC', 1);

$root = dirname(__DIR__, 2);
$site = file_get_contents($root . '/site/controllers/ajax.php');
$admin = file_get_contents($root . '/admin/controllers/ajax.php');

if (!is_string($site) || !str_contains($site, "is_readable(\$legacyAjaxController)")
    || !str_contains($site, "class_exists('sportsmanagementControllerAjax', false)")) {
    throw new RuntimeException('Site legacy Ajax bridge lost its file and alias guards.');
}

if (!is_string($admin) || !str_contains($admin, "ajax.json.php")
    || !str_contains($admin, "is_readable(\$legacyAjaxController)")
    || !str_contains($admin, "class_exists('sportsmanagementControllerAjax', false)")) {
    throw new RuntimeException('Administrator Ajax bridge must share the JSON controller and guard its alias.');
}

require_once $root . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarFunctions.php';

$oldTimezone = date_default_timezone_get();
date_default_timezone_set('UTC');

try {
    $result = CalendarFunctions::events(1700000000);
    $expected = '<h2 class="sidebar__heading">Tuesday<br>November 14</h2>';

    if ($result !== $expected) {
        throw new RuntimeException('Calendar sidebar did not interpret Unix seconds correctly: ' . $result);
    }

    if (!str_contains(CalendarFunctions::events(str_repeat('9', 200)), 'sidebar__heading')) {
        throw new RuntimeException('Calendar sidebar could not safely handle an oversized date.');
    }
} finally {
    date_default_timezone_set($oldTimezone);
}

echo "Joomla 5/6 Ajax bridge and calendar regressions passed.\n";
